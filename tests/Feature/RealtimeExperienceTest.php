<?php

namespace Tests\Feature;

use App\Events\UserNotificationCreated;
use App\Events\WebinarAttendanceUpdated;
use App\Events\WebinarQuestionUpdated;
use App\Events\WebinarRoomUpdated;
use App\Models\Permission;
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
        $webinar = $this->webinar($admin);
        $this->actingAs($this->user('learner'))->postJson(route('webinars.attendance.join', $webinar))->assertForbidden();
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
        $after->assertSee('data-answered="true"', false)->assertSee('Your Answer')->assertSee('100%')->assertSee('1 total votes');
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

    public function test_backend_certificate_toggle_allows_zero_attendance_download_any_time(): void
    {
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        $webinar->update(['certificate_enabled' => 'yes', 'settings' => ['experience' => ['certificate_min_attendance' => 80, 'certificate_require_poll' => true]]]);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);
        $url = route('webinars.certificate.download', $webinar);
        $this->actingAs($learner)->get(route('webinars.dashboard', $webinar))->assertOk()->assertSee('Download certificate')->assertDontSee('80% attendance');
        foreach (['scheduled', 'live', 'completed'] as $status) {
            $webinar->update(['status' => $status]);
            $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
        }
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
        $this->get(route('admin.feedback.index'))->assertOk()->assertSee('All webinars');
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
        $this->actingAs($sub)->get(route('admin.dashboard'))->assertOk()->assertSee('ASSIGNED EVENTS');
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
        ])->assertRedirect();

        Event::assertDispatched(WebinarRoomUpdated::class, fn ($event) =>
            $event->webinarId === $webinar->id &&
            $event->change === 'controls' &&
            $event->state['chat_enabled'] === true &&
            $event->state['polls_enabled'] === true &&
            $event->state['comments_enabled'] === true &&
            $event->state['feedback_enabled'] === true &&
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
}
