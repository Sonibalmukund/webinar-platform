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

    public function test_chat_messages_are_ordered_by_upvotes_descending(): void
    {
        $admin = $this->user('super-admin');
        $learner1 = $this->user('learner');
        $learner2 = $this->user('learner');
        $webinar = $this->webinar($admin);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner1->id, 'email' => $learner1->email, 'status' => 'approved']);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner2->id, 'email' => $learner2->email, 'status' => 'approved']);

        $msgLow = DB::table('chat_messages')->insertGetId(['webinar_id' => $webinar->id, 'user_id' => $learner1->id, 'message' => 'Low upvotes', 'sent_at' => now()->subMinutes(5), 'created_at' => now(), 'updated_at' => now()]);
        $msgHigh = DB::table('chat_messages')->insertGetId(['webinar_id' => $webinar->id, 'user_id' => $learner2->id, 'message' => 'High upvotes', 'sent_at' => now()->subMinutes(10), 'created_at' => now(), 'updated_at' => now()]);

        // Cast 2 votes on msgHigh and 0 on msgLow
        DB::table('chat_message_votes')->insert([
            ['chat_message_id' => $msgHigh, 'user_id' => $learner1->id, 'created_at' => now(), 'updated_at' => now()],
            ['chat_message_id' => $msgHigh, 'user_id' => $learner2->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->actingAs($learner1)->getJson(route('webinars.chat.index', $webinar));
        $response->assertOk();
        $this->assertEquals($msgHigh, $response->json('messages.0.id'));
        $this->assertEquals(2, $response->json('messages.0.votes_count'));
        $this->assertEquals($msgLow, $response->json('messages.1.id'));
    }

    public function test_attendee_and_admin_can_reply_to_chat_messages(): void
    {
        Event::fake([WebinarChatMessageSent::class]);
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = $this->webinar($admin);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);

        // Parent message by learner
        $parentId = DB::table('chat_messages')->insertGetId(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'message' => 'Original question', 'sent_at' => now(), 'created_at' => now(), 'updated_at' => now()]);

        // Learner replies to parent
        $replyResponse = $this->actingAs($learner)->postJson(route('webinars.chat.store', $webinar), [
            'message' => 'Follow up clarification',
            'reply_to_id' => $parentId,
        ]);
        $replyResponse->assertCreated()
            ->assertJsonPath('message.reply_to_id', $parentId)
            ->assertJsonPath('message.reply_to_user_name', $learner->name)
            ->assertJsonPath('message.reply_to_message', 'Original question');

        // Admin replies to learner
        $adminReplyResponse = $this->actingAs($admin)->postJson(route('admin.chats.store', $webinar), [
            'message' => 'Admin official answer',
            'reply_to_id' => $parentId,
        ]);
        $adminReplyResponse->assertCreated()
            ->assertJsonPath('message.reply_to_id', $parentId)
            ->assertJsonPath('message.reply_to_user_name', $learner->name);

        // Fetching history includes reply quotes
        $history = $this->actingAs($learner)->getJson(route('webinars.chat.index', $webinar))->json('messages');
        $replies = collect($history)->where('reply_to_id', $parentId);
        $this->assertCount(2, $replies);
    }
}
