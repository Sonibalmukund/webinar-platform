<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminAccessSeeder extends Seeder
{
    public function run(): void
    {
        $superRole = Role::firstOrCreate(['slug'=>'super-admin'], ['name'=>'Super Admin', 'description'=>'Full platform access', 'is_system'=>true]);
        $subRole = Role::firstOrCreate(['slug'=>'sub-admin'], ['name'=>'Sub Admin', 'description'=>'Event-scoped access', 'is_system'=>true]);
        $admin = $superRole->users()->first() ?? User::where('email', 'admin@gmail.com')->first() ?? new User;
        $admin->fill(['name'=>'Super Admin', 'email'=>'admin@gmail.com', 'password'=>'123456', 'job_title'=>'Platform Administrator', 'status'=>'active'])->save();
        $admin->roles()->sync([$superRole->id]);

        $definitions = [
            'webinars'=>['view','create','edit','delete'], 'registrations'=>['view','approve','export'],
            'polls'=>['view','create','edit','delete','manage'], 'certificates'=>['view','create','edit','hide'],
            'live-control'=>['view','manage'], 'reports'=>['view','export'],
            'chat'=>['view','manage','moderate'], 'attendance'=>['view','export'], 'notifications'=>['view','create'],
        ];
        $ids = [];
        foreach ($definitions as $module=>$actions) foreach ($actions as $action) {
            $permission = Permission::updateOrCreate(['slug'=>"$module.$action"], ['name'=>ucwords(str_replace('-',' ',$module)).' '.ucfirst($action), 'module'=>$module]);
            $ids[] = $permission->id;
        }
        $superRole->permissions()->sync($ids);
        $subRole->permissions()->sync([]);
    }
}
