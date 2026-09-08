<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubAdminController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.subadmins.index', ['subAdmins'=>User::whereHas('roles',fn($q)=>$q->where('slug','sub-admin'))->with('assignedWebinars')->latest()->paginate(15)]);
    }
    public function create(): View { return $this->form(new User); }
    public function edit(User $subAdmin): View { abort_unless($subAdmin->hasRole('sub-admin'),404); return $this->form($subAdmin->load('assignedWebinars')); }
    private function form(User $subAdmin): View { return view('pages.admin.subadmins.form',['subAdmin'=>$subAdmin,'webinars'=>Webinar::orderBy('title')->get()]); }
    public function store(Request $request): RedirectResponse { $this->save($request,new User); return redirect()->route('admin.subadmins.index')->with('status','Sub admin created successfully.'); }
    public function update(Request $request, User $subAdmin): RedirectResponse { abort_unless($subAdmin->hasRole('sub-admin'),404); $this->save($request,$subAdmin); return redirect()->route('admin.subadmins.index')->with('status','Sub admin updated successfully.'); }
    private function save(Request $request, User $user): void
    {
        $data=$request->validate(['name'=>['required','string','max:255'],'email'=>['required','email',Rule::unique('users')->ignore($user->id)],'mobile'=>['nullable','string','max:30'],'job_title'=>['nullable','string','max:255'],'status'=>['required','in:active,inactive'],'password'=>[$user->exists?'nullable':'required','string','min:6'],'webinars'=>['nullable','array'],'webinars.*'=>['integer','exists:webinars,id']]);
        $webinars=$data['webinars']??[]; unset($data['webinars']); if(blank($data['password']??null)) unset($data['password']); $user->fill($data)->save();
        $role=Role::where('slug','sub-admin')->firstOrFail(); $user->roles()->sync([$role->id]);
        $user->assignedWebinars()->syncWithPivotValues($webinars,['assigned_by'=>$request->user()->id]);
    }
    public function destroy(User $subAdmin): RedirectResponse { abort_unless($subAdmin->hasRole('sub-admin'),404); $subAdmin->delete(); return back()->with('status','Sub admin deleted.'); }
}
