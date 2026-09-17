<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Poll;
use App\Models\Registration;
use App\Models\RegistrationField;
use App\Models\RegistrationForm;
use App\Models\Role;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDynamicColumnsSubAdminIsolationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_sub_admins_only_see_their_own_dynamic_field_labels_and_in_separate_columns(): void
    {
        $subAdminRole = Role::firstOrCreate(['slug' => 'sub-admin'], ['name' => 'Sub Admin', 'is_system' => true]);

        $subAdminA = User::factory()->create(['status' => 'active']);
        $subAdminA->roles()->syncWithoutDetaching([$subAdminRole->id]);

        $subAdminB = User::factory()->create(['status' => 'active']);
        $subAdminB->roles()->syncWithoutDetaching([$subAdminRole->id]);

        $webinarA = Webinar::create([
            'title' => 'Webinar Alpha',
            'slug' => 'webinar-alpha-'.uniqid(),
            'status' => 'scheduled',
            'created_by' => $subAdminA->id,
            'starts_at' => now(),
            'ends_at' => now()->addHours(2),
            'timezone' => 'Asia/Kolkata',
            'comments_enabled' => true,
            'feedback_enabled' => true,
            'qa_enabled' => true,
            'certificate_enabled' => 'yes',
        ]);
        $subAdminA->assignedWebinars()->syncWithoutDetaching([$webinarA->id]);

        $webinarB = Webinar::create([
            'title' => 'Webinar Beta',
            'slug' => 'webinar-beta-'.uniqid(),
            'status' => 'scheduled',
            'created_by' => $subAdminB->id,
            'starts_at' => now(),
            'ends_at' => now()->addHours(2),
            'timezone' => 'Asia/Kolkata',
            'comments_enabled' => true,
            'feedback_enabled' => true,
            'qa_enabled' => true,
            'certificate_enabled' => 'yes',
        ]);
        $subAdminB->assignedWebinars()->syncWithoutDetaching([$webinarB->id]);

        $perms = [
            'registrations.view', 'users.view', 'attendance.view',
            'poll-logs.view', 'certificate-logs.view', 'polls.view', 'certificates.view',
            'q-and-a.view', 'feedback.view'
        ];
        foreach ($perms as $slug) {
            $perm = Permission::firstOrCreate(['slug' => $slug], ['name' => $slug, 'module' => strtok($slug, '.')]);
            $subAdminA->webinarPermissions()->attach($perm->id, ['webinar_id' => $webinarA->id, 'assigned_by' => $subAdminA->id]);
            $subAdminB->webinarPermissions()->attach($perm->id, ['webinar_id' => $webinarB->id, 'assigned_by' => $subAdminB->id]);
        }

        $formA = RegistrationForm::create([
            'webinar_id' => $webinarA->id,
            'title' => 'Registration Form A',
        ]);

        $formB = RegistrationForm::create([
            'webinar_id' => $webinarB->id,
            'title' => 'Registration Form B',
        ]);

        $fieldA = RegistrationField::create([
            'registration_form_id' => $formA->id,
            'label' => 'College Alpha',
            'field_key' => 'college_alpha',
            'field_type' => 'text',
            'is_required' => false,
            'is_enabled' => true,
        ]);

        $fieldB = RegistrationField::create([
            'registration_form_id' => $formB->id,
            'label' => 'Department Beta',
            'field_key' => 'dept_beta',
            'field_type' => 'text',
            'is_required' => false,
            'is_enabled' => true,
        ]);

        $learner = User::factory()->create(['name' => 'John Learner', 'email' => 'john.learner@example.com']);

        $regA = Registration::create([
            'webinar_id' => $webinarA->id,
            'user_id' => $learner->id,
            'email' => $learner->email,
            'status' => 'approved',
            'registered_at' => now(),
        ]);
        DB::table('registration_answers')->insert([
            'registration_id' => $regA->id,
            'registration_field_id' => $fieldA->id,
            'value' => 'Harvard University',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $regB = Registration::create([
            'webinar_id' => $webinarB->id,
            'user_id' => $learner->id,
            'email' => $learner->email,
            'status' => 'approved',
            'registered_at' => now(),
        ]);
        DB::table('registration_answers')->insert([
            'registration_id' => $regB->id,
            'registration_field_id' => $fieldB->id,
            'value' => 'Computer Science Dept',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Poll Logs
        $pollA = Poll::create(['webinar_id' => $webinarA->id, 'created_by' => $subAdminA->id, 'question' => 'Question A', 'status' => 'active']);
        $optA = $pollA->options()->create(['label' => 'Option A1', 'display_order' => 0]);
        DB::table('poll_responses')->insert([
            'poll_id' => $pollA->id,
            'poll_option_id' => $optA->id,
            'user_id' => $learner->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pollB = Poll::create(['webinar_id' => $webinarB->id, 'created_by' => $subAdminB->id, 'question' => 'Question B', 'status' => 'active']);
        $optB = $pollB->options()->create(['label' => 'Option B1', 'display_order' => 0]);
        DB::table('poll_responses')->insert([
            'poll_id' => $pollB->id,
            'poll_option_id' => $optB->id,
            'user_id' => $learner->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Certificate download logs
        DB::table('certificate_downloads')->insert([
            'webinar_id' => $webinarA->id,
            'user_id' => $learner->id,
            'certificate_id' => 1,
            'ip_address' => '127.0.0.1',
            'downloaded_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('certificate_downloads')->insert([
            'webinar_id' => $webinarB->id,
            'user_id' => $learner->id,
            'certificate_id' => 2,
            'ip_address' => '127.0.0.2',
            'downloaded_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Certificate Approval Queue
        DB::table('certificates')->insert([
            'webinar_id' => $webinarA->id,
            'user_id' => $learner->id,
            'credential_id' => (string) \Illuminate\Support\Str::uuid(),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Comments
        DB::table('comments')->insert([
            'webinar_id' => $webinarA->id,
            'user_id' => $learner->id,
            'comment' => 'Great session A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('comments')->insert([
            'webinar_id' => $webinarB->id,
            'user_id' => $learner->id,
            'comment' => 'Great session B',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Feedback
        DB::table('feedback')->insert([
            'webinar_id' => $webinarA->id,
            'user_id' => $learner->id,
            'rating' => 5,
            'message' => 'Loved it A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('feedback')->insert([
            'webinar_id' => $webinarB->id,
            'user_id' => $learner->id,
            'rating' => 4,
            'message' => 'Loved it B',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Q&A
        DB::table('questions')->insert([
            'webinar_id' => $webinarA->id,
            'user_id' => $learner->id,
            'question' => 'How to do X?',
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('questions')->insert([
            'webinar_id' => $webinarB->id,
            'user_id' => $learner->id,
            'question' => 'How to do Y?',
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Sub Admin A visits Registrations
        $responseA = $this->actingAs($subAdminA)->get(route('admin.registrations'));
        $responseA->assertOk();
        $responseA->assertSee('<th>College Alpha</th>', false);
        $responseA->assertSee('Harvard University', false);
        $responseA->assertDontSee('Department Beta');
        $responseA->assertDontSee('Computer Science Dept');

        // 2. Sub Admin B visits Registrations
        $responseB = $this->actingAs($subAdminB)->get(route('admin.registrations'));
        $responseB->assertOk();
        $responseB->assertSee('<th>Department Beta</th>', false);
        $responseB->assertSee('Computer Science Dept', false);
        $responseB->assertDontSee('College Alpha');
        $responseB->assertDontSee('Harvard University');

        // 3. Sub Admin A visits Users
        $responseAUsers = $this->actingAs($subAdminA)->get(route('admin.users'));
        $responseAUsers->assertOk();
        $responseAUsers->assertSee('<th>College Alpha</th>', false);
        $responseAUsers->assertDontSee('Department Beta');

        // 4. Sub Admin B visits Users
        $responseBUsers = $this->actingAs($subAdminB)->get(route('admin.users'));
        $responseBUsers->assertOk();
        $responseBUsers->assertSee('<th>Department Beta</th>', false);
        $responseBUsers->assertDontSee('College Alpha');

        // 5. Sub Admin A visits Poll Logs
        $responseAPolls = $this->actingAs($subAdminA)->get(route('admin.polls.logs'));
        $responseAPolls->assertOk();
        $responseAPolls->assertSee('<th>College Alpha</th>', false);
        $responseAPolls->assertDontSee('Department Beta');

        // 6. Sub Admin B visits Poll Logs
        $responseBPolls = $this->actingAs($subAdminB)->get(route('admin.polls.logs'));
        $responseBPolls->assertOk();
        $responseBPolls->assertSee('<th>Department Beta</th>', false);
        $responseBPolls->assertDontSee('College Alpha');

        // 7. Sub Admin A visits Certificate Logs
        $responseACerts = $this->actingAs($subAdminA)->get(route('admin.certificates.logs'));
        $responseACerts->assertOk();
        $responseACerts->assertSee('<th>College Alpha</th>', false);
        $responseACerts->assertDontSee('Department Beta');

        // 8. Sub Admin B visits Certificate Logs
        $responseBCerts = $this->actingAs($subAdminB)->get(route('admin.certificates.logs'));
        $responseBCerts->assertOk();
        $responseBCerts->assertSee('<th>Department Beta</th>', false);
        $responseBCerts->assertDontSee('College Alpha');

        // 9. Comments
        $responseComments = $this->actingAs($subAdminA)->get(route('admin.comments.index'));
        $responseComments->assertOk();
        $responseComments->assertSee('<th>College Alpha</th>', false);
        $responseComments->assertDontSee('Department Beta');

        // 11. Feedback
        $responseFeedback = $this->actingAs($subAdminA)->get(route('admin.feedback.index'));
        $responseFeedback->assertOk();
        $responseFeedback->assertSee('<th>College Alpha</th>', false);
        $responseFeedback->assertDontSee('Department Beta');

        // 12. Q&A
        $responseQA = $this->actingAs($subAdminA)->get(route('admin.questions.index'));
        $responseQA->assertOk();
        $responseQA->assertSee('<th>College Alpha</th>', false);
        $responseQA->assertDontSee('Department Beta');

        // 13. Attendance: dynamic fields present, but CERTIFICATE column and access window subtitle removed
        $responseAttendance = $this->actingAs($subAdminA)->get(route('admin.attendance'));
        $responseAttendance->assertOk();
        $responseAttendance->assertDontSee('<th>Certificate</th>', false);
        $responseAttendance->assertDontSee('Attendance follows the webinar access window');
    }
}