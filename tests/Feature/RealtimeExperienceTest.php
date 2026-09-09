<?php

namespace Tests\Feature;

use App\Events\UserNotificationCreated;
use App\Events\WebinarAttendanceUpdated;
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
        $this->actingAs($learner)->postJson(route('webinars.attendance.join', $webinar))->assertOk()->assertJsonPath('state', 'join');
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
}
