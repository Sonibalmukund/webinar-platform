<?php

namespace Tests\Feature;

use App\Events\WebinarChatMessageDeleted;
use App\Events\WebinarChatMessageSent;
use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RealtimeChatTest extends TestCase
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
        return Webinar::create(['created_by' => $admin->id, 'title' => 'Chat test', 'slug' => 'chat-test-'.uniqid(), 'status' => 'live', 'chat_enabled' => true]);
    }

    public function test_admin_messages_broadcast_without_email_and_return_json(): void
    {
        Event::fake([WebinarChatMessageSent::class]);
        $admin = $this->user('super-admin');
        $webinar = $this->webinar($admin);
        $response = $this->actingAs($admin)->postJson(route('admin.chats.store', $webinar), ['message' => 'Hello participants']);
        $response->assertCreated()->assertJsonPath('message.user_name', $admin->name)->assertJsonPath('message.message', 'Hello participants');
        Event::assertDispatched(WebinarChatMessageSent::class, fn ($event) => $event->webinarId === $webinar->id && ! array_key_exists('user_email', $event->broadcastWith()) && (string) $event->broadcastOn()[0] === 'private-webinar.chat.'.$webinar->id);
    }

    public function test_admin_moderation_broadcasts_deletion_and_history_excludes_it(): void
    {
        Event::fake([WebinarChatMessageSent::class, WebinarChatMessageDeleted::class]);
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);
        $id = $this->actingAs($admin)->postJson(route('admin.chats.store', $webinar), ['message' => 'Remove me'])->json('message.id');
        $this->deleteJson(route('admin.chats.messages.destroy', [$webinar, $id]))->assertOk();
        Event::assertDispatched(WebinarChatMessageDeleted::class, fn ($event) => $event->messageId === $id);
        $this->actingAs($learner)->getJson(route('webinars.chat.index', $webinar))->assertOk()->assertJsonCount(0, 'messages');
    }

    public function test_cross_event_delete_and_unregistered_history_are_denied(): void
    {
        Event::fake([WebinarChatMessageSent::class, WebinarChatMessageDeleted::class]);
        $admin = $this->user('super-admin');
        $webinar = $this->webinar($admin);
        $other = $this->webinar($admin);
        $id = $this->actingAs($admin)->postJson(route('admin.chats.store', $webinar), ['message' => 'Keep me'])->json('message.id');
        $this->deleteJson(route('admin.chats.messages.destroy', [$other, $id]))->assertNotFound();
        Event::assertNotDispatched(WebinarChatMessageDeleted::class);
        $this->actingAs($this->user('learner'))->getJson(route('webinars.chat.index', $webinar))->assertForbidden();
    }

    public function test_channel_authorization_requires_admin_or_event_registration(): void
    {
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        $payload = ['socket_id' => '123.456', 'channel_name' => 'private-webinar.chat.'.$webinar->id];
        $this->actingAs($learner)->postJson('/broadcasting/auth', $payload)->assertForbidden();
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);
        $this->postJson('/broadcasting/auth', $payload)->assertOk()->assertJsonStructure(['auth']);
        $this->actingAs($admin)->postJson('/broadcasting/auth', $payload)->assertOk();
    }

    public function test_admin_attachment_payload_is_in_participant_history(): void
    {
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);
        DB::table('chat_messages')->insert(['webinar_id' => $webinar->id, 'user_id' => $admin->id, 'message' => '', 'attachment_path' => '/uploads/chat/sample.pdf', 'attachment_name' => 'Agenda.pdf', 'sent_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $this->actingAs($learner)->getJson(route('webinars.chat.index', $webinar))->assertOk()->assertJsonPath('messages.0.attachment_name','Agenda.pdf');
    }
}
