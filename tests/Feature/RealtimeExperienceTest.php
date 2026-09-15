<?php

namespace Tests\Feature;

use App\Events\UserNotificationCreated;
use App\Events\WebinarAttendanceUpdated;
use App\Events\WebinarQuestionUpdated;
use App\Events\WebinarRoomUpdated;
use App\Models\Permission;
use App\Models\CertificateTemplate;
use App\Models\Poll;
use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RealtimeExperienceTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['slug' => $role], ['name' => $role]));

        return $user;
    }

    private function webinar(User $admin): Webinar
    {
        return Webinar::create(['created_by' => $admin->id, 'title' => 'Realtime event', 'slug' => 'realtime-'.uniqid(), 'status' => 'live', 'chat_enabled' => true, 'polls_enabled' => true, 'starts_at' => now()->subHour(), 'ends_at' => now()->addHour()]);
    }

    public function test_attendance_join_heartbeat_and_leave_record_real_watch_time(): void
    {
        Event::fake([WebinarAttendanceUpdated::class]);
        Carbon::setTestNow('2026-09-08 10:00:00');
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);
        $this->actingAs($learner)->postJson(route('webinars.attendance.join', $webinar))->assertOk()->assertJsonPath('state', 'join')->assertJsonPath('participants.0.id', $learner->id)->assertJsonPath('participants.0.name', $learner->name);
        Carbon::setTestNow('2026-09-08 10:00:30');
        $this->postJson(route('webinars.attendance.heartbeat', $webinar))->assertOk()->assertJsonPath('watch_seconds', 30);
        Carbon::setTestNow('2026-09-08 10:00:50');
        $this->postJson(route('webinars.attendance.leave', $webinar))->assertOk()->assertJsonPath('watch_seconds', 50);
        $this->assertDatabaseHas('webinar_attendees', ['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'watch_seconds' => 50]);
        $this->assertSame(3, DB::table('webinar_attendance_events')->where(['webinar_id' => $webinar->id, 'user_id' => $learner->id])->count());
        Event::assertDispatched(WebinarAttendanceUpdated::class, 3);
        Carbon::setTestNow();
    }

    public function test_unregistered_learner_cannot_write_attendance(): void
    {
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        $this->actingAs($learner)->postJson(route('webinars.attendance.join', $webinar))->assertForbidden();
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'pending']);
        $this->postJson(route('webinars.attendance.join', $webinar))->assertOk()->assertJsonPath('state', 'join');
    }

    public function test_registered_learner_can_raise_and_lower_hand(): void
    {
        Event::fake([WebinarAttendanceUpdated::class]);
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);
        $this->actingAs($learner)->postJson(route('webinars.attendance.hand', $webinar))->assertOk()->assertJsonPath('raised_hand', true);
        $this->assertDatabaseHas('webinar_attendees', ['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'raised_hand' => true]);
        $this->postJson(route('webinars.attendance.hand', $webinar))->assertOk()->assertJsonPath('raised_hand', false);
        Event::assertDispatched(WebinarAttendanceUpdated::class, 2);
        Event::assertDispatched(WebinarAttendanceUpdated::class, fn ($event) => $event->state === 'hand-raised' && $event->broadcastWith()['user_name'] === $learner->name);
    }

    public function test_poll_lifecycle_broadcasts_room_refresh(): void
    {
        Event::fake([WebinarRoomUpdated::class]);
        $admin = $this->user('super-admin');
        $webinar = $this->webinar($admin);
        $poll = Poll::create(['webinar_id' => $webinar->id, 'created_by' => $admin->id, 'question' => 'Ready?', 'status' => 'draft']);
        $this->actingAs($admin)->patch(route('admin.polls.status', $poll), ['status' => 'active'])->assertRedirect();
        Event::assertDispatched(WebinarRoomUpdated::class, fn ($event) => $event->webinarId === $webinar->id && $event->change === 'poll');
    }

    public function test_quiz_start_and_stop_broadcast_type_and_status(): void
    {
        Event::fake([WebinarRoomUpdated::class]);
        $admin = $this->user('super-admin');
        $webinar = $this->webinar($admin);
        $poll = Poll::create(['webinar_id' => $webinar->id, 'created_by' => $admin->id, 'question' => 'Quiz?', 'status' => 'draft']);
        $poll->options()->create(['label' => 'Yes', 'is_correct' => true, 'display_order' => 0]);
        foreach (['active' => 'started', 'ended' => 'stopped'] as $status => $verb) {
            $this->actingAs($admin)->patch(route('admin.polls.status', $poll), ['status' => $status])->assertSessionHas('status', 'Quiz '.$verb.'.');
            Event::assertDispatched(WebinarRoomUpdated::class, fn ($event) => $event->state['status'] === $status && $event->state['type'] === 'Quiz');
        }
    }

    public function test_poll_results_are_hidden_until_learner_answers_and_selection_survives_reload(): void
    {
        Event::fake([\App\Events\WebinarPollUpdated::class]);
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);
        $poll = Poll::create(['webinar_id' => $webinar->id, 'created_by' => $admin->id, 'question' => 'Choose a topic', 'status' => 'active']);
        $one = $poll->options()->create(['label' => 'First topic', 'display_order' => 0]);
        $poll->options()->create(['label' => 'Second topic', 'display_order' => 1]);
        $before = $this->actingAs($learner)->get(route('webinars.dashboard', $webinar))->assertOk();
        $before->assertSee('data-answered="false"', false)->assertSee('type="radio"', false);
        $this->assertMatchesRegularExpression('/data-poll-result\s+hidden/', $before->getContent());
        $this->postJson(route('webinars.polls.vote', [$webinar, $poll]), ['option_id' => $one->id])->assertOk()->assertJsonPath('selected_option_id', $one->id)->assertJsonPath('options.0.count', 1);
        $after = $this->get(route('webinars.dashboard', $webinar))->assertOk();
        $after->assertSee('data-answered="true"', false)->assertDontSee('Your Answer')->assertDontSee('Correct Answer')->assertSee('100%')->assertSee('1 total votes');
        $this->assertMatchesRegularExpression('/poll-choice selectable selected locked/', $after->getContent());
        $this->assertDoesNotMatchRegularExpression('/data-poll-result\s+hidden/', $after->getContent());
    }

    public function test_poll_survives_broadcast_failure_and_retry_returns_saved_answer(): void
    {
        Event::listen(\App\Events\WebinarPollUpdated::class, fn () => throw new \RuntimeException('Reverb unavailable'));
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);
        $poll = Poll::create(['webinar_id' => $webinar->id, 'created_by' => $admin->id, 'question' => 'Offline poll', 'status' => 'active']);
        $one = $poll->options()->create(['label' => 'First', 'display_order' => 0]);
        $two = $poll->options()->create(['label' => 'Second', 'display_order' => 1]);
        $this->actingAs($learner)->getJson(route('webinars.polls.results', [$webinar, $poll]))->assertForbidden();
        $this->postJson(route('webinars.polls.vote', [$webinar, $poll]), ['option_id' => $one->id])->assertOk()->assertJsonPath('selected_option_id', $one->id);
        $this->postJson(route('webinars.polls.vote', [$webinar, $poll]), ['option_id' => $two->id])->assertOk()->assertJsonPath('selected_option_id', $one->id);
        $this->getJson(route('webinars.polls.results', [$webinar, $poll]))->assertOk()->assertJsonPath('options.0.count', 1)->assertJsonPath('options.1.count', 0);
        $this->assertSame(1, DB::table('poll_responses')->where('poll_id', $poll->id)->count());
        Event::forget(\App\Events\WebinarPollUpdated::class);
    }

    public function test_certificate_download_requires_configured_watch_percentage(): void
    {
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        $webinar->update(['certificate_enabled' => 'yes', 'settings' => ['experience' => ['certificate_min_attendance' => 80, 'certificate_require_poll' => true]]]);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);
        $url = route('webinars.certificate.download', $webinar);
        $this->actingAs($learner)->get(route('webinars.dashboard', $webinar))->assertOk()
            ->assertSee('Certificate unlocks at 80% watch time')
            ->assertDontSee('@endif');
        $this->get($url)->assertForbidden();
        DB::table('webinar_attendees')->where(['webinar_id' => $webinar->id, 'user_id' => $learner->id])->update(['watch_seconds' => 7200, 'last_seen_at' => now(), 'updated_at' => now()]);
        $poll = Poll::create(['webinar_id' => $webinar->id, 'created_by' => $admin->id, 'question' => 'Required response', 'status' => 'active']);
        $option = $poll->options()->create(['label' => 'Done', 'display_order' => 1]);
        DB::table('poll_responses')->insert(['poll_id' => $poll->id, 'poll_option_id' => $option->id, 'user_id' => $learner->id, 'created_at' => now(), 'updated_at' => now()]);
        $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertSame(1, DB::table('certificates')->where('webinar_id', $webinar->id)->count());
        $webinar->update(['certificate_enabled' => 'no']);
        $this->get($url)->assertNotFound();
        $webinar->update(['certificate_enabled' => 'yes']);
        $this->actingAs($this->user('learner'))->get($url)->assertForbidden();
    }

    public function test_comments_listing_filters_by_webinar_and_search(): void
    {
        $admin = $this->user('super-admin');
        $one = $this->webinar($admin);
        $two = $this->webinar($admin);
        foreach ([$one, $two] as $webinar) {
            $webinar->update(['comments_enabled' => true]);
            DB::table('comments')->insert(['webinar_id' => $webinar->id, 'user_id' => $admin->id, 'comment' => 'Unique note '.$webinar->id, 'created_at' => now(), 'updated_at' => now()]);
        }
        $this->actingAs($admin)->get(route('admin.comments.index', ['webinar_id' => $one->id, 'search' => 'Unique']))->assertOk()->assertSee('Unique note '.$one->id)->assertDontSee('Unique note '.$two->id);
        $this->get(route('admin.comments.index', ['webinar_id' => $one->id, 'search' => 'absent-text']))->assertOk()->assertSee('No comments found.');
        $one->update(['feedback_enabled' => true]);
        $feedbackId = DB::table('feedback')->insertGetId(['webinar_id' => $one->id, 'user_id' => $admin->id, 'rating' => 5, 'message' => 'Actionable feedback', 'status' => 'new', 'created_at' => now(), 'updated_at' => now()]);
        $this->get(route('admin.feedback.index'))->assertOk()
            ->assertSee('All webinars')
            ->assertSee('Actions')
            ->assertSee('Actionable feedback')
            ->assertSee(route('admin.feedback.show', $one).'#feedback-'.$feedbackId, false)
            ->assertDontSee('<th>Status</th>', false);
    }

    public function test_admin_notification_is_saved_and_broadcast_to_each_learner(): void
    {
        Event::fake([UserNotificationCreated::class]);
        $admin = $this->user('super-admin');
        $one = $this->user('learner');
        $two = $this->user('learner');
        $webinar = $this->webinar($admin);
        foreach ([$one, $two] as $learner) {
            Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);
        }
        $before = DB::table('user_notifications')->count();
        $this->actingAs($admin)->post(route('admin.notifications.store'), ['subject' => 'Starting now', 'message' => 'Please join the room.', 'audience' => 'webinar', 'webinar_id' => $webinar->id])->assertRedirect(route('admin.notifications.index'));
        $this->assertSame($before + 2, DB::table('user_notifications')->count());
        Event::assertDispatched(UserNotificationCreated::class, 2);
        $this->actingAs($one)->get(route('notifications.index'))->assertOk()->assertSee('Starting now');
    }

    public function test_sub_admin_can_open_only_assigned_webinar_chat(): void
    {
        $admin = $this->user('super-admin');
        $sub = $this->user('sub-admin');
        $assigned = $this->webinar($admin);
        $other = $this->webinar($admin);
        $sub->assignedWebinars()->attach($assigned->id, ['assigned_by' => $admin->id]);
        foreach (['dashboard.view', 'chat.view'] as $slug) {
            $permission = Permission::firstOrCreate(['slug' => $slug], ['name' => $slug, 'module' => strtok($slug, '.')]);
            $sub->webinarPermissions()->attach($permission->id, ['webinar_id' => $assigned->id, 'assigned_by' => $admin->id]);
        }
        $this->actingAs($sub)->get(route('admin.dashboard'))->assertOk()
            ->assertSee('ASSIGNED EVENTS')
            ->assertSee('Profile')
            ->assertDontSee('<div class="sidebar-section-title">Administration</div>', false);
        $this->actingAs($sub)->get(route('admin.chats.show', $assigned))->assertOk()
            ->assertSee('data-chat-history="'.route('admin.chats.show', $assigned).'"', false);
        $this->get(route('admin.chats.show',$other))->assertForbidden();
    }

    public function test_learner_can_fetch_active_poll_partial_dynamically(): void
    {
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);
        $poll = Poll::create(['webinar_id' => $webinar->id, 'created_by' => $admin->id, 'question' => 'Realtime Poll Question', 'status' => 'active']);
        $poll->options()->create(['label' => 'Dynamic Option 1', 'display_order' => 0]);
        $poll->options()->create(['label' => 'Dynamic Option 2', 'display_order' => 1]);

        $response = $this->actingAs($learner)->get(route('webinars.polls.active', $webinar))->assertOk();
        $response->assertSee('Realtime Poll Question')->assertSee('Dynamic Option 1')->assertSee('Dynamic Option 2');
    }

    public function test_live_controls_and_certificate_visibility_broadcast_full_state(): void
    {
        Event::fake([WebinarRoomUpdated::class]);
        $admin = $this->user('super-admin');
        $webinar = $this->webinar($admin);

        $this->actingAs($admin)->put(route('admin.webinars.controls', $webinar), [
            'status' => 'live',
            'chat_enabled' => 1,
            'polls_enabled' => 1,
            'comments_enabled' => 1,
            'feedback_enabled' => 1,
            'show_poll_correct_answer' => 1,
        ])->assertRedirect();

        Event::assertDispatched(WebinarRoomUpdated::class, fn ($event) =>
            $event->webinarId === $webinar->id &&
            $event->change === 'controls' &&
            $event->state['chat_enabled'] === true &&
            $event->state['polls_enabled'] === true &&
            $event->state['comments_enabled'] === true &&
            $event->state['feedback_enabled'] === true &&
            $event->state['show_poll_correct_answer'] === true &&
            $event->state['status'] === 'live'
        );

        $this->patch(route('admin.certificates.visibility', $webinar), ['enabled' => 1])->assertRedirect();
        Event::assertDispatched(WebinarRoomUpdated::class, fn ($event) =>
            $event->change === 'controls' &&
            $event->state['certificate_enabled'] === true
        );
    }

    public function test_host_can_update_pinned_announcement_and_learner_sees_it(): void
    {
        Event::fake([WebinarRoomUpdated::class]);
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);

        $res = $this->actingAs($admin)->putJson(route('admin.webinars.announcement', $webinar), [
            'message' => 'Exclusive 50% discount on next course!',
            'button_text' => 'Get Discount',
            'button_url' => 'https://example.com/discount',
            'enabled' => 1,
        ])->assertOk();

        $res->assertJsonPath('pinned_announcement.enabled', true);
        $res->assertJsonPath('pinned_announcement.message', 'Exclusive 50% discount on next course!');

        Event::assertDispatched(WebinarRoomUpdated::class, fn ($e) =>
            $e->webinarId === $webinar->id &&
            $e->change === 'announcement' &&
            $e->state['pinned_announcement']['enabled'] === true
        );

        $this->actingAs($learner)->get(route('webinars.dashboard', $webinar))->assertOk()
            ->assertSee('Exclusive 50% discount on next course!')
            ->assertSee('Get Discount')
            ->assertSee('https://example.com/discount');
    }

    public function test_learner_can_upvote_live_chat_messages_in_realtime(): void
    {
        Event::fake([\App\Events\WebinarChatMessageVoted::class]);
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);

        $messageId = DB::table('chat_messages')->insertGetId([
            'webinar_id' => $webinar->id,
            'user_id' => $admin->id,
            'message' => 'Welcome to the live webinar session!',
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Learner upvotes chat message
        $voteRes = $this->actingAs($learner)->postJson(route('webinars.chat.vote', [$webinar, $messageId]))->assertOk();
        $this->assertTrue($voteRes->json('voted'));
        $this->assertSame(1, $voteRes->json('votes_count'));

        Event::assertDispatched(\App\Events\WebinarChatMessageVoted::class, fn ($e) =>
            $e->webinarId === $webinar->id && $e->messageId === $messageId && $e->votesCount === 1
        );

        // Toggle upvote off
        $unvoteRes = $this->postJson(route('webinars.chat.vote', [$webinar, $messageId]))->assertOk();
        $this->assertFalse($unvoteRes->json('voted'));
        $this->assertSame(0, $unvoteRes->json('votes_count'));

        // Upvote again
        $this->postJson(route('webinars.chat.vote', [$webinar, $messageId]))->assertOk();

        // Dashboard shows chat message with upvote button and no Q&A tab
        $this->get(route('webinars.dashboard', $webinar))->assertOk()
            ->assertSee('Welcome to the live webinar session!')
            ->assertSee('data-chat-vote-btn', false)
            ->assertDontSee('data-module-tab="qa"', false);
    }

    public function test_certificate_sharing_links_are_rendered_on_dashboard_and_library(): void
    {
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        $webinar->update(['certificate_enabled' => 'yes']);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);

        DB::table('certificates')->insert([
            'webinar_id' => $webinar->id,
            'user_id' => $learner->id,
            'credential_id' => (string) \Illuminate\Support\Str::uuid(),
            'status' => 'approved',
            'issued_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Live dashboard has WhatsApp and LinkedIn share buttons
        $this->actingAs($learner)->get(route('webinars.dashboard', $webinar))->assertOk()
            ->assertSee('WhatsApp')
            ->assertSee('LinkedIn')
            ->assertSee('https://wa.me/?text=', false)
            ->assertSee('https://www.linkedin.com/sharing/share-offsite/', false);

        // User library has certificate share buttons
        $this->get(route('certificates.index'))->assertOk()
            ->assertSee('WhatsApp')
            ->assertSee('LinkedIn')
            ->assertSee('https://wa.me/?text=', false)
            ->assertSee('https://www.linkedin.com/sharing/share-offsite/', false);
    }

    public function test_background_presence_keeps_user_online_without_adding_watch_time(): void
    {
        Carbon::setTestNow('2026-09-13 10:00:00');
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);

        $this->actingAs($learner)->postJson(route('webinars.attendance.join', $webinar))->assertOk();
        Carbon::setTestNow('2026-09-13 10:00:30');
        $this->postJson(route('webinars.attendance.presence', $webinar))
            ->assertOk()->assertJsonPath('live_viewers', 1)->assertJsonPath('watch_seconds', 0);
        $this->assertSame(1, DB::table('webinar_attendance_events')->where(['webinar_id' => $webinar->id, 'user_id' => $learner->id])->count());
        Carbon::setTestNow();
    }

    public function test_correct_poll_answer_is_only_highlighted_when_live_controller_enables_it(): void
    {
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);
        $poll = Poll::create(['webinar_id' => $webinar->id, 'created_by' => $admin->id, 'question' => 'Reveal test?', 'status' => 'active']);
        $wrong = $poll->options()->create(['label' => 'Wrong choice', 'is_correct' => false, 'display_order' => 0]);
        $correct = $poll->options()->create(['label' => 'Right choice', 'is_correct' => true, 'display_order' => 1]);

        $this->actingAs($learner)->postJson(route('webinars.polls.vote', [$webinar, $poll]), ['option_id' => $wrong->id])
            ->assertOk()->assertJsonPath('show_correct_answer', false)->assertJsonPath('correct_option_id', null);
        $this->get(route('webinars.polls.active', $webinar))->assertOk()
            ->assertDontSee('quiz-correct', false)->assertDontSee('Your Answer')->assertDontSee('Correct Answer');

        $this->actingAs($admin)->put(route('admin.webinars.controls', $webinar), [
            'status' => 'live', 'chat_enabled' => 1, 'polls_enabled' => 1,
            'comments_enabled' => 1, 'feedback_enabled' => 1,
            'show_poll_correct_answer' => 1,
        ])->assertRedirect();

        $this->actingAs($learner)->get(route('webinars.polls.active', $webinar))->assertOk()
            ->assertSee('data-option-id="'.$correct->id.'"', false)
            ->assertSee('quiz-correct', false)
            ->assertDontSee('Your Answer')->assertDontSee('Correct Answer');
    }

    public function test_live_controller_viewer_fallback_endpoint_counts_recent_attendees(): void
    {
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        DB::table('webinar_attendees')->insert([
            'webinar_id' => $webinar->id, 'user_id' => $learner->id,
            'joined_at' => now(), 'last_seen_at' => now(), 'left_at' => null,
            'watch_seconds' => 0, 'raised_hand' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($admin)->getJson(route('admin.webinars.live-viewers', $webinar))
            ->assertOk()->assertJsonPath('live_viewers', 1);
    }

    public function test_poll_results_show_voter_log_to_authorized_admins(): void
    {
        $admin = $this->user('super-admin');
        $sub = $this->user('sub-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        $sub->assignedWebinars()->attach($webinar->id, ['assigned_by' => $admin->id]);
        $permission = Permission::firstOrCreate(['slug' => 'polls.view'], ['name' => 'polls.view', 'module' => 'polls']);
        $sub->webinarPermissions()->attach($permission->id, ['webinar_id' => $webinar->id, 'assigned_by' => $admin->id]);

        $poll = Poll::create(['webinar_id' => $webinar->id, 'created_by' => $admin->id, 'question' => 'Which answer did you select?', 'status' => 'ended']);
        $option = $poll->options()->create(['label' => 'VirtualPortal answer', 'is_correct' => true, 'display_order' => 0]);
        DB::table('poll_responses')->insert(['poll_id' => $poll->id, 'poll_option_id' => $option->id, 'user_id' => $learner->id, 'is_correct' => true, 'voted_at' => now(), 'created_at' => now(), 'updated_at' => now()]);

        foreach ([$admin, $sub] as $staff) {
            $this->actingAs($staff)->get(route('admin.polls.show', $poll))->assertOk()
                ->assertSee('Poll results')
                ->assertSee('VirtualPortal answer')
                ->assertDontSee('Who answered this poll');
        }

        $this->actingAs($admin)->get(route('admin.polls.logs'))->assertOk()
            ->assertSee('Poll voter logs')
            ->assertSee($learner->email)
            ->assertSee('VirtualPortal answer')
            ->assertSee('View results')
            ->assertSee(route('admin.polls.show', $poll), false)
            ->assertSee('sidebar-uploaded-brand', false)
            ->assertSee('site-brand-logo', false)
            ->assertSee('/storage/site/', false);

        $this->actingAs($sub)->get(route('admin.polls.logs'))->assertForbidden();
        $this->actingAs($sub)->get(route('admin.certificates.logs'))->assertForbidden();
    }

    public function test_poll_and_dashboard_access_never_cross_webinar_registration_scope(): void
    {
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $registeredWebinar = $this->webinar($admin);
        $otherWebinar = $this->webinar($admin);
        Registration::create(['webinar_id' => $registeredWebinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);
        $poll = Poll::create(['webinar_id' => $registeredWebinar->id, 'created_by' => $admin->id, 'question' => 'Only registered webinar poll', 'status' => 'active']);
        $poll->options()->create(['label' => 'Scoped option A', 'display_order' => 0]);
        $poll->options()->create(['label' => 'Scoped option B', 'display_order' => 1]);

        $this->actingAs($learner)->get(route('webinars.dashboard', $registeredWebinar))->assertOk()->assertSee('Only registered webinar poll');
        $this->get(route('webinars.dashboard', $otherWebinar))->assertForbidden();
        $this->get(route('webinars.polls.active', $otherWebinar))->assertForbidden();

        Registration::create(['webinar_id' => $otherWebinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'pending']);
        $this->get(route('webinars.dashboard', $otherWebinar))->assertOk();
        $this->get(route('webinars.polls.active', $otherWebinar))->assertOk();
    }

    public function test_certificate_preview_is_available_to_super_admin_and_authorized_sub_admin(): void
    {
        $admin = $this->user('super-admin');
        $sub = $this->user('sub-admin');
        $webinar = $this->webinar($admin);
        $sub->assignedWebinars()->attach($webinar->id, ['assigned_by' => $admin->id]);
        $permission = Permission::firstOrCreate(['slug' => 'certificates.view'], ['name' => 'certificates.view', 'module' => 'certificates']);
        $sub->webinarPermissions()->attach($permission->id, ['webinar_id' => $webinar->id, 'assigned_by' => $admin->id]);
        $template = CertificateTemplate::create(['name' => 'VirtualPortal Certificate', 'orientation' => 'landscape', 'design' => ['headline' => 'Certificate of Completion', 'signatory' => 'VirtualPortal Team'], 'created_by' => $admin->id]);
        $webinar->update(['settings' => ['certificate_template_id' => $template->id]]);

        foreach ([$admin, $sub] as $staff) {
            $this->actingAs($staff)->get(route('admin.certificates.preview', $webinar))->assertOk()
                ->assertSee('Certificate Preview')
                ->assertSee('Sample Attendee')
                ->assertSee('VirtualPortal Team');
        }
    }

    public function test_role_permissions_are_module_based_and_apply_to_all_assigned_webinars(): void
    {
        $admin = $this->user('super-admin');
        $sub = $this->user('sub-admin');
        $firstWebinar = $this->webinar($admin);
        $secondWebinar = $this->webinar($admin);
        $sub->assignedWebinars()->attach([$firstWebinar->id, $secondWebinar->id], ['assigned_by' => $admin->id]);

        $this->actingAs($admin)->get(route('admin.permissions.create', ['user_id' => $sub->id]))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Dynamic Fields')
            ->assertSee('Speakers')
            ->assertSee('Users')
            ->assertSee('Feedback')
            ->assertSee('Q And A')
            ->assertSee('Poll Logs')
            ->assertSee('Certificate Logs')
            ->assertDontSee('name="webinar_id"', false);

        $permissionIds = Permission::whereIn('slug', ['dashboard.view', 'speakers.view', 'feedback.view', 'poll-logs.view', 'certificate-logs.view'])->pluck('id')->all();
        $this->put(route('admin.permissions.update'), ['user_id' => $sub->id, 'permissions' => $permissionIds])
            ->assertRedirect(route('admin.permissions.index'));

        foreach ([$firstWebinar, $secondWebinar] as $webinar) {
            foreach ($permissionIds as $permissionId) {
                $this->assertDatabaseHas('user_webinar_permissions', [
                    'user_id' => $sub->id,
                    'webinar_id' => $webinar->id,
                    'permission_id' => $permissionId,
                ]);
            }
        }

        $this->actingAs($sub)->get(route('admin.polls.logs'))
            ->assertOk()
            ->assertDontSee('name="webinar_id"', false);
        $this->get(route('admin.certificates.logs'))
            ->assertOk()
            ->assertDontSee('name="webinar_id"', false);
        $this->get(route('admin.feedback.index'))
            ->assertOk()
            ->assertSee('Feedback from your assigned webinars.')
            ->assertSee('Sub Administrator')
            ->assertDontSee('name="webinar_id"', false);

        $thirdWebinar = $this->webinar($admin);
        $this->actingAs($admin)->put(route('admin.subadmins.update', $sub), [
            'name' => $sub->name,
            'email' => $sub->email,
            'mobile' => $sub->mobile,
            'job_title' => $sub->job_title,
            'status' => 'active',
            'password' => '',
            'webinars' => [$firstWebinar->id, $secondWebinar->id, $thirdWebinar->id],
        ])->assertRedirect(route('admin.subadmins.index'));

        foreach ($permissionIds as $permissionId) {
            $this->assertDatabaseHas('user_webinar_permissions', [
                'user_id' => $sub->id,
                'webinar_id' => $thirdWebinar->id,
                'permission_id' => $permissionId,
            ]);
        }
    }

    public function test_view_only_sub_admin_does_not_see_mutation_buttons_and_reports_stay_assigned(): void
    {
        $admin = $this->user('super-admin');
        $sub = $this->user('sub-admin');
        $assigned = $this->webinar($admin);
        $assigned->update(['title' => 'Assigned report event']);
        $other = $this->webinar($admin);
        $other->update(['title' => 'Private platform event']);
        $sub->assignedWebinars()->attach($assigned->id, ['assigned_by' => $admin->id]);

        foreach (['webinars.view', 'polls.view', 'certificates.view', 'speakers.view', 'notifications.view', 'reports.view'] as $slug) {
            $permission = Permission::where('slug', $slug)->firstOrFail();
            $sub->webinarPermissions()->attach($permission->id, ['webinar_id' => $assigned->id, 'assigned_by' => $admin->id]);
        }

        Poll::create(['webinar_id' => $assigned->id, 'created_by' => $admin->id, 'question' => 'View-only poll', 'status' => 'draft']);
        Registration::create(['webinar_id' => $assigned->id, 'email' => 'assigned-report@example.com', 'status' => 'approved']);
        Registration::create(['webinar_id' => $other->id, 'email' => 'private-report@example.com', 'status' => 'approved']);

        $this->actingAs($sub)->get(route('admin.webinars.index'))->assertOk()
            ->assertDontSee('Create webinar')->assertDontSee('Edit webinar')->assertDontSee('Clone as draft')->assertDontSee('>Delete<', false);
        $this->get(route('admin.webinars.create'))->assertForbidden();
        $this->get(route('admin.webinars.edit', $assigned))->assertForbidden();
        $this->get(route('admin.polls.index'))->assertOk()
            ->assertDontSee('Add poll')->assertDontSee('>Edit<', false)->assertDontSee('Duplicate')->assertDontSee('>Delete<', false);
        $this->get(route('admin.polls.edit', Poll::where('webinar_id', $assigned->id)->firstOrFail()))->assertForbidden();
        $this->get(route('admin.certificates.index'))->assertOk()
            ->assertDontSee('Add certificate')->assertDontSee('Add template')->assertDontSee('Edit template');
        $this->get(route('admin.speakers.index'))->assertOk()->assertDontSee('Add speaker');
        $this->get(route('admin.notifications.index'))->assertOk()->assertDontSee('Create notification');

        $this->get(route('admin.reports.index'))->assertOk()
            ->assertSee('Assigned Events report')
            ->assertSee('Assigned report event')
            ->assertDontSee('Private platform event')
            ->assertDontSee('private-report@example.com');
        $this->get(route('admin.reports.index', ['webinar_id' => $other->id]))->assertForbidden();
    }
}
