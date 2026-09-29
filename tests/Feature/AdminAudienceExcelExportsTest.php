<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Poll;
use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use ZipArchive;

class AdminAudienceExcelExportsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_super_admin_can_download_users_poll_logs_comments_and_feedback_as_excel(): void
    {
        $admin = $this->userWithRole('super-admin');
        $attendee = User::factory()->create([
            'name' => 'Excel Audience Member',
            'email' => 'excel-audience@example.com',
            'mobile' => '+91 99887 77665',
            'status' => 'active',
        ]);
        $webinar = Webinar::create([
            'created_by' => $admin->id,
            'title' => 'Audience Export Event',
            'slug' => 'audience-export-event-'.uniqid(),
            'status' => 'completed',
            'timezone' => 'Asia/Kolkata',
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->subHour(),
            'comments_enabled' => true,
            'feedback_enabled' => true,
        ]);

        Registration::create([
            'webinar_id' => $webinar->id,
            'user_id' => $attendee->id,
            'email' => $attendee->email,
            'status' => 'approved',
            'registered_at' => now()->subDay(),
        ]);

        $poll = Poll::create([
            'webinar_id' => $webinar->id,
            'created_by' => $admin->id,
            'question' => 'Which export do you prefer?',
            'status' => 'ended',
        ]);
        $option = $poll->options()->create([
            'label' => 'Excel workbook',
            'display_order' => 0,
        ]);
        DB::table('poll_responses')->insert([
            'poll_id' => $poll->id,
            'poll_option_id' => $option->id,
            'user_id' => $attendee->id,
            'is_correct' => null,
            'voted_at' => now()->subMinutes(20),
            'created_at' => now()->subMinutes(20),
            'updated_at' => now()->subMinutes(20),
        ]);
        DB::table('comments')->insert([
            'webinar_id' => $webinar->id,
            'user_id' => $attendee->id,
            'comment' => 'Export this comment please',
            'status' => 'visible',
            'created_at' => now()->subMinutes(15),
            'updated_at' => now()->subMinutes(15),
        ]);
        DB::table('feedback')->insert([
            'webinar_id' => $webinar->id,
            'user_id' => $attendee->id,
            'rating' => 5,
            'message' => 'Export feedback works',
            'status' => 'new',
            'created_at' => now()->subMinutes(10),
            'updated_at' => now()->subMinutes(10),
        ]);

        $this->actingAs($admin);

        $this->assertWorkbookContains(
            $this->get(route('admin.users.export', ['search' => 'Excel Audience'])),
            ['User', 'Excel Audience Member', 'Audience Export Event']
        );
        $this->assertWorkbookContains(
            $this->get(route('admin.polls.logs.export', ['search' => 'Which export'])),
            ['Poll question', 'Which export do you prefer?', 'Excel workbook']
        );
        $this->assertWorkbookContains(
            $this->get(route('admin.comments.export', ['search' => 'Export this comment'])),
            ['Comment', 'Export this comment please', 'Excel Audience Member']
        );
        $this->assertWorkbookContains(
            $this->get(route('admin.feedback.export', ['search' => 'Export feedback'])),
            ['Rating', 'Export feedback works', 'Excel Audience Member']
        );
    }

    public function test_export_buttons_are_available_on_the_four_admin_lists(): void
    {
        $admin = $this->userWithRole('super-admin');

        foreach (['admin.users', 'admin.polls.logs', 'admin.comments.index', 'admin.feedback.index'] as $route) {
            $this->actingAs($admin)->get(route($route))
                ->assertOk()
                ->assertSee('Export Excel');
        }
    }

    public function test_sub_admin_export_permissions_map_to_each_excel_route(): void
    {
        $superAdmin = $this->userWithRole('super-admin');
        $subAdmin = $this->userWithRole('sub-admin');
        $webinar = Webinar::create([
            'created_by' => $superAdmin->id,
            'title' => 'Assigned Export Event',
            'slug' => 'assigned-export-event-'.uniqid(),
            'status' => 'scheduled',
            'timezone' => 'Asia/Kolkata',
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addHours(2),
            'comments_enabled' => true,
            'feedback_enabled' => true,
        ]);
        $subAdmin->assignedWebinars()->attach($webinar->id, ['assigned_by' => $superAdmin->id]);

        foreach (['users.export', 'poll-logs.export', 'q-and-a.export', 'feedback.export'] as $slug) {
            [$module] = explode('.', $slug);
            $permission = Permission::firstOrCreate(
                ['slug' => $slug],
                ['name' => $slug, 'module' => $module]
            );
            $subAdmin->webinarPermissions()->attach($permission->id, [
                'webinar_id' => $webinar->id,
                'assigned_by' => $superAdmin->id,
            ]);
        }

        foreach (['admin.users.export', 'admin.polls.logs.export', 'admin.comments.export', 'admin.feedback.export'] as $route) {
            $this->actingAs($subAdmin)->get(route($route))
                ->assertOk()
                ->assertDownload();
        }
    }

    private function assertWorkbookContains(TestResponse $response, array $values): void
    {
        $response->assertOk()->assertDownload();
        $this->assertStringEndsWith('.xlsx', (string) $response->headers->get('content-disposition'));
        $this->assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('content-type')
        );

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($response->baseResponse->getFile()->getPathname()) === true);
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $this->assertIsString($sheet);

        foreach ($values as $value) {
            $this->assertStringContainsString($value, $sheet);
        }

        $zip->close();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['slug' => $role], ['name' => $role]));

        return $user;
    }
}
