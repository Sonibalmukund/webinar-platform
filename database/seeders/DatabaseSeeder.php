<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Permission;
use App\Models\Poll;
use App\Models\Role;
use App\Models\Speaker;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LocationSeeder::class);
        $this->call(AdminAccessSeeder::class);

        $admin=User::where('email','admin@gmail.com')->firstOrFail();
        $subRole=Role::where('slug','sub-admin')->firstOrFail();
        $learnerRole=Role::firstOrCreate(['slug'=>'learner'],['name'=>'Learner','description'=>'Webinar attendee access','is_system'=>true]);
        $subAdmin=User::create(['name'=>'Aarav Mehta','email'=>'subadmin@webinar.test','password'=>'Webinar@123','mobile'=>'+91 98765 43210','job_title'=>'Webinar Manager','company'=>'Nexa Digital','status'=>'active','timezone'=>'Asia/Kolkata']);
        $subAdmin->roles()->sync([$subRole->id]);
        $learner=User::create(['name'=>'Demo Attendee','email'=>'attendee@webinar.test','password'=>'Webinar@123','mobile'=>'+91 90000 00001','job_title'=>'Product Manager','company'=>'Acme Labs','status'=>'active','timezone'=>'Asia/Kolkata']);
        $learner->roles()->sync([$learnerRole->id]);

        $starts=now()->addDays(7)->setTime(18,0);
        $webinar=Webinar::create([
            'created_by'=>$admin->id,'title'=>'Future of Digital Healthcare 2026','slug'=>'future-of-digital-healthcare-2026',
            'short_description'=>'Join healthcare leaders for a practical look at AI, connected care, and the next generation of patient experiences.',
            'description'=>'Future of Digital Healthcare 2026 brings clinicians, technology leaders, and innovators together for an expert-led conversation on responsible AI, connected care, and patient-first transformation. Discover practical strategies, real-world use cases, and the decisions healthcare teams must make today to build a more accessible and intelligent future.',
            'status'=>'scheduled','language'=>'en','timezone'=>'Asia/Kolkata','starts_at'=>$starts,'ends_at'=>$starts->copy()->addMinutes(90),'max_attendees'=>500,
            'registration_type'=>'free','published_at'=>now(),'registration_deadline'=>$starts->copy()->subHour(),'live_provider'=>'youtube','live_url'=>'https://www.youtube.com/embed/jNQXAC9IVRw',
            'certificate_enabled'=>'yes','chat_enabled'=>true,'qa_enabled'=>false,'polls_enabled'=>true,'comments_enabled'=>true,'feedback_enabled'=>true,'auto_approve'=>true,'early_entry_minutes'=>30,
            'settings'=>['experience'=>['primary'=>'#5B3DF5','secondary'=>'#00A7B5','background'=>'#F5F7FF','text'=>'#101828','button'=>'#5B3DF5','layout'=>'presentation','logo_url'=>'https://placehold.co/260x90/5B3DF5/FFFFFF?text=NEXA+HEALTH','waiting_message'=>'The Digital Healthcare Summit will begin shortly.','post_message'=>'Thank you for joining Future of Digital Healthcare 2026.','registration_success_title'=>'Your seat is confirmed!','registration_success_message'=>'You are registered for Future of Digital Healthcare 2026.']],
        ]);

        $speakers=[
            ['name'=>'Dr. Maya Kapoor','slug'=>'dr-maya-kapoor','email'=>'maya@example.test','headline'=>'Chief Digital Health Officer','company'=>'Nexa Health','bio'=>'Dr. Maya Kapoor leads patient-centered digital transformation programs across hospital networks.','photo_path'=>'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=500&q=80'],
            ['name'=>'Arjun Rao','slug'=>'arjun-rao','email'=>'arjun@example.test','headline'=>'Director of Healthcare AI','company'=>'MedAxis Labs','bio'=>'Arjun builds responsible AI systems for clinical decision support and hospital operations.','photo_path'=>'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=500&q=80'],
            ['name'=>'Priya Menon','slug'=>'priya-menon','email'=>'priya@example.test','headline'=>'VP, Patient Experience','company'=>'CareBridge','bio'=>'Priya specializes in accessible, connected, and measurable patient experiences.','photo_path'=>'https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=500&q=80'],
        ];
        foreach($speakers as $order=>$data){$speaker=Speaker::create($data+['social_links'=>['linkedin'=>'https://linkedin.com'],'is_active'=>true]);$webinar->speakers()->attach($speaker->id,['role'=>$order===0?'Keynote Speaker':'Panel Speaker','display_order'=>$order+1]);}

        foreach([
            ['name'=>'Nexa Health','logo_path'=>'https://placehold.co/240x90/5B3DF5/FFFFFF?text=NEXA+HEALTH'],
            ['name'=>'MedAxis Labs','logo_path'=>'https://placehold.co/240x90/00A7B5/FFFFFF?text=MEDAXIS'],
            ['name'=>'CareBridge','logo_path'=>'https://placehold.co/240x90/16213E/FFFFFF?text=CAREBRIDGE'],
        ] as $data) Brand::create($data+['webinar_id'=>$webinar->id,'website_url'=>'https://example.com','is_active'=>true]);

        Banner::create(['webinar_id'=>$webinar->id,'title'=>'Digital Healthcare Summit Cover','media_type'=>'image','media_url'=>'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1600&q=85','is_active'=>true,'display_order'=>1]);
        Banner::create(['webinar_id'=>$webinar->id,'title'=>'Healthcare Innovation Preview','media_type'=>'video','media_url'=>'https://storage.googleapis.com/coverr-main/mp4/Mt_Baker.mp4','is_active'=>true,'display_order'=>2]);

        $form=$webinar->registrationForm()->create(['title'=>'Reserve your seat','description'=>'Complete the fields below to join this webinar.','is_active'=>true,'require_login'=>true,'success_message'=>'Your seat has been reserved successfully.']);
        foreach([
            ['label'=>'Full name','field_key'=>'full_name','field_type'=>'text','placeholder'=>'Enter your full name','is_required'=>true,'login_enabled'=>false,'width'=>'half'],
            ['label'=>'Email address','field_key'=>'email','field_type'=>'text','placeholder'=>'you@company.com','is_required'=>true,'login_enabled'=>true,'width'=>'half'],
            ['label'=>'Mobile number','field_key'=>'mobile','field_type'=>'text','placeholder'=>'+91 98765 43210','is_required'=>true,'login_enabled'=>false,'width'=>'half'],
            ['label'=>'Organization','field_key'=>'organization','field_type'=>'text','placeholder'=>'Company or institution','is_required'=>false,'login_enabled'=>false,'width'=>'half'],
            ['label'=>'City','field_key'=>'city','field_type'=>'city','placeholder'=>'Select city','is_required'=>true,'login_enabled'=>false,'width'=>'full'],
        ] as $order=>$field) $form->fields()->create($field+['icon'=>'input-cursor-text','is_enabled'=>true,'display_order'=>$order+1]);

        foreach([
            ['title'=>'Welcome and opening remarks','description'=>'Event overview and key themes.','starts_at'=>'18:00','duration_minutes'=>10],
            ['title'=>'AI-powered patient care','description'=>'Clinical use cases and responsible adoption.','starts_at'=>'18:10','duration_minutes'=>30],
            ['title'=>'Expert panel discussion','description'=>'Connected care, data, and patient experience.','starts_at'=>'18:40','duration_minutes'=>30],
            ['title'=>'Audience comments','description'=>'Private comments from attendees.','starts_at'=>'19:10','duration_minutes'=>20],
        ] as $order=>$item) DB::table('webinar_agenda_items')->insert($item+['webinar_id'=>$webinar->id,'display_order'=>$order+1,'created_at'=>now(),'updated_at'=>now()]);

        $poll=Poll::create(['webinar_id'=>$webinar->id,'created_by'=>$admin->id,'question'=>'Which digital health area will have the greatest impact in 2026?','allow_multiple'=>false,'status'=>'active','started_at'=>now()]);
        foreach(['Clinical AI','Remote patient monitoring','Connected health records','Patient experience'] as $order=>$label) $poll->options()->create(['label'=>$label,'is_correct'=>false,'display_order'=>$order+1]);
        $quiz=Poll::create(['webinar_id'=>$webinar->id,'created_by'=>$admin->id,'question'=>'Which HTML tag creates the largest heading?','allow_multiple'=>false,'status'=>'active','started_at'=>now()]);
        foreach(['<h1>','<h6>','<p>','<span>'] as $order=>$label) $quiz->options()->create(['label'=>$label,'is_correct'=>$order===0,'display_order'=>$order+1]);
        $registration=$webinar->registrations()->create(['user_id'=>$learner->id,'email'=>$learner->email,'status'=>'approved','source'=>'demo-seeder','registered_at'=>now(),'approved_at'=>now()]);
        $correctOption=$quiz->options()->where('is_correct',true)->firstOrFail();
        $quiz->responses()->create(['poll_option_id'=>$correctOption->id,'user_id'=>$learner->id,'is_correct'=>true,'voted_at'=>now()]);
        DB::table('chat_messages')->insert(['webinar_id'=>$webinar->id,'user_id'=>$subAdmin->id,'message'=>'Welcome to Future of Digital Healthcare 2026. Introduce yourself in the chat!','sent_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);

        $subAdmin->assignedWebinars()->attach($webinar->id,['assigned_by'=>$admin->id]);
        foreach(Permission::whereIn('module',['dashboard','webinars','speakers','registrations','polls','live-control','reports','chat','attendance'])->pluck('id') as $permissionId) DB::table('user_webinar_permissions')->insert(['user_id'=>$subAdmin->id,'webinar_id'=>$webinar->id,'permission_id'=>$permissionId,'assigned_by'=>$admin->id,'created_at'=>now(),'updated_at'=>now()]);

        $india=\App\Models\Country::where('iso2','IN')->first();
        $gujarat=\App\Models\State::where('country_id',$india?->id)->where('name','Gujarat')->first();
        foreach([
            ['group'=>'registration','key'=>'registration_email_enabled','value'=>'1','is_public'=>false],['group'=>'registration','key'=>'registration_email_required','value'=>'1','is_public'=>false],
            ['group'=>'registration','key'=>'registration_mobile_enabled','value'=>'1','is_public'=>false],['group'=>'registration','key'=>'registration_mobile_required','value'=>'0','is_public'=>false],
            ['group'=>'registration','key'=>'registration_password_enabled','value'=>'0','is_public'=>false],['group'=>'registration','key'=>'registration_password_required','value'=>'0','is_public'=>false],
            ['group'=>'registration','key'=>'registration_country_enabled','value'=>'1','is_public'=>false],['group'=>'registration','key'=>'registration_state_enabled','value'=>'1','is_public'=>false],
            ['group'=>'registration','key'=>'registration_city_enabled','value'=>'1','is_public'=>false],['group'=>'registration','key'=>'registration_default_country_id','value'=>(string)$india?->id,'is_public'=>false],
            ['group'=>'registration','key'=>'registration_default_state_id','value'=>(string)$gujarat?->id,'is_public'=>false],['group'=>'site','key'=>'site_name','value'=>'Nexa Health Events','is_public'=>true],
            ['group'=>'site','key'=>'footer_text','value'=>'© 2026 Nexa Health Events. All rights reserved.','is_public'=>true],
        ] as $setting) DB::table('settings')->insert($setting+['created_at'=>now(),'updated_at'=>now()]);
    }
}
