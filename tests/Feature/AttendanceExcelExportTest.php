<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use ZipArchive;

class AttendanceExcelExportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_super_admin_downloads_a_valid_excel_attendance_workbook(): void
    {
        $admin = $this->user('super-admin');
        $attendee = User::factory()->create(['name' => 'Excel Attendee', 'mobile' => '+91 98765 43210']);
        $webinar = $this->webinar($admin, 'Excel Export Event');

        DB::table('webinar_attendees')->insert([
            'webinar_id' => $webinar->id,
            'user_id' => $attendee->id,
            'joined_at' => '2026-09-26 04:30:00',
            'left_at' => '2026-09-26 05:00:00',
            'watch_seconds' => 1800,
            'last_seen_at' => '2026-09-26 05:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.export'));

        $response->assertOk()->assertDownload();
        $this->assertStringEndsWith('.xlsx', (string) $response->headers->get('content-disposition'));
        $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));

        $path = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path) === true);
        $this->assertNotFalse($zip->locateName('xl/workbook.xml'));
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $this->assertIsString($sheet);
        $this->assertStringContainsString('Excel Attendee', $sheet);
        $this->assertStringContainsString('Excel Export Event', $sheet);
        $this->assertStringContainsString('+91 98765 43210', $sheet);
        $this->assertStringContainsString('<v>1800</v>', $sheet);
        $this->assertStringContainsString('state="frozen"', $sheet);
        $zip->close();
    }

    public function test_sub_admin_does_not_see_webinars_navigation_or_an_export_button_without_export_permission(): void
    {
        $admin = $this->user('super-admin');
        $subAdmin = $this->user('sub-admin');
        $webinar = $this->webinar($admin, 'Assigned Event');
        $subAdmin->assignedWebinars()->attach($webinar->id, ['assigned_by' => $admin->id]);

        foreach (['dashboard.view', 'webinars.view', 'attendance.view'] as $slug) {
            $permission = Permission::where('slug', $slug)->firstOrFail();
            $subAdmin->webinarPermissions()->attach($permission->id, [
                'webinar_id' => $webinar->id,
                'assigned_by' => $admin->id,
            ]);
        }

        $this->actingAs($subAdmin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('href="/admin/webinars"', false);

        $this->get(route('admin.attendance'))
            ->assertOk()
            ->assertDontSee('Export Excel');
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['slug' => $role], ['name' => $role]));

        return $user;
    }

    private function webinar(User $admin, string $title): Webinar
    {
        return Webinar::create([
            'created_by' => $admin->id,
            'title' => $title,
            'slug' => str($title)->slug().'-'.uniqid(),
            'status' => 'completed',
            'timezone' => 'Asia/Kolkata',
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->subHour(),
        ]);
    }
}
