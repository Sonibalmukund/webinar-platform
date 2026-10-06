<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class WebinarEditorAndLivePreviewTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::firstOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin'],
        ));

        return $admin;
    }

    private function webinar(User $admin, array $attributes = []): Webinar
    {
        return Webinar::create(array_merge([
            'created_by' => $admin->id,
            'title' => 'Preview Webinar',
            'slug' => 'preview-webinar-'.uniqid(),
            'status' => 'draft',
            'language' => 'en',
            'timezone' => 'Asia/Kolkata',
            'settings' => [],
        ], $attributes));
    }

    public function test_edit_wizard_shows_intermediate_save_action_and_keeps_selected_step(): void
    {
        Event::fake();
        $admin = $this->admin();
        $webinar = $this->webinar($admin);

        $this->actingAs($admin)->get(route('admin.webinars.edit', $webinar))
            ->assertOk()
            ->assertSee('id="btnSaveChanges"', false)
            ->assertSee('Save Changes')
            ->assertSee('setupDesignContentSwitches', false)
            ->assertSee('design-content-switch', false);

        $this->put(route('admin.webinars.update', $webinar), [
            'title' => 'Updated Preview Webinar',
            'slug' => $webinar->slug,
            'status' => 'draft',
            'language' => 'en',
            'timezone' => 'Asia/Kolkata',
            'live_provider' => null,
            'save_action' => 'stay',
            'wizard_step' => '2',
        ])->assertRedirect(route('admin.webinars.edit', [
            'webinar' => $webinar,
            'step' => '2',
        ]));

        $this->assertSame('Updated Preview Webinar', $webinar->fresh()->title);
    }

    public function test_live_control_prefers_iframe_and_falls_back_to_waiting_room_image(): void
    {
        $admin = $this->admin();
        $webinar = $this->webinar($admin, [
            'settings' => ['experience' => ['waiting_media_url' => '/storage/webinars/waiting-room.jpg']],
        ]);

        $this->actingAs($admin)->get(route('admin.webinars.live', $webinar))
            ->assertOk()
            ->assertSee('src="/storage/webinars/waiting-room.jpg"', false)
            ->assertDontSee('<iframe src=', false);

        $webinar->update([
            'live_provider' => 'custom',
            'live_url' => 'https://player.example.com/live',
        ]);

        $this->get(route('admin.webinars.live', $webinar->fresh()))
            ->assertOk()
            ->assertSee('<iframe src="https://player.example.com/live"', false);
    }

    public function test_live_control_can_open_early_room_immediately(): void
    {
        Event::fake();
        $admin = $this->admin();
        $webinar = $this->webinar($admin, [
            'status' => 'scheduled',
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(2)->addHour(),
            'early_entry_minutes' => 30,
        ]);

        $this->assertFalse($webinar->canEnter());

        $this->actingAs($admin)->get(route('admin.webinars.live', $webinar))
            ->assertOk()
            ->assertSee('name="early_room_open"', false)
            ->assertSee('Live Early Room Access');

        $this->putJson(route('admin.webinars.controls', $webinar), [
            'status' => 'scheduled',
            'early_entry_minutes' => 30,
            'early_room_open' => 1,
        ])->assertOk()
            ->assertJsonPath('early_room_open', true);

        $webinar->refresh();
        $this->assertTrue((bool) data_get($webinar->settings, 'experience.early_room_open'));
        $this->assertTrue($webinar->canEnter());

        $this->putJson(route('admin.webinars.controls', $webinar), [
            'status' => 'scheduled',
            'early_entry_minutes' => 30,
        ])->assertOk();

        $this->assertFalse($webinar->fresh()->canEnter());
    }
}
