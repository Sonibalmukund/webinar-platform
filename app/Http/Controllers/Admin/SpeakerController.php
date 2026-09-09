<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Speaker;
use App\Models\Webinar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SpeakerController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.general.index', ['type' => 'speaker', 'rows' => Speaker::with('webinars')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return $this->form(new Speaker);
    }

    public function edit(Speaker $speaker): View
    {
        return $this->form($speaker->load('webinars'));
    }

    private function form(Speaker $speaker): View
    {
        return view('pages.admin.general.speaker-form', ['speaker' => $speaker, 'webinars' => Webinar::orderBy('title')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->save($request, new Speaker);
    }

    public function update(Request $request, Speaker $speaker): RedirectResponse
    {
        return $this->save($request, $speaker);
    }

    private function save(Request $request, Speaker $speaker): RedirectResponse
    {
        $data = $request->validate(['webinar_id' => ['required', 'exists:webinars,id'], 'name' => ['required', 'string', 'max:255'], 'email' => ['nullable', 'email'], 'headline' => ['nullable', 'string', 'max:255'], 'company' => ['nullable', 'string', 'max:255'], 'bio' => ['nullable', 'string'], 'photo' => ['nullable', 'image', 'max:5120']]);
        if ($request->hasFile('photo')) {
            $directory = public_path('uploads/speakers');
            File::ensureDirectoryExists($directory);
            $name = Str::uuid().'.'.$request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move($directory, $name);
            $data['photo_path'] = '/uploads/speakers/'.$name;
        }unset($data['photo'],$data['webinar_id']);
        $data['slug'] = $speaker->exists ? $speaker->slug : Str::slug($data['name']).'-'.Str::lower(Str::random(4));
        $data['is_active'] = $request->boolean('is_active');
        $speaker->fill($data)->save();
        $speaker->webinars()->sync([$request->integer('webinar_id') => ['role' => 'speaker', 'display_order' => 0]]);

        return redirect()->route('admin.speakers.index')->with('status', 'Speaker saved.');
    }

    public function destroy(Speaker $speaker): RedirectResponse
    {
        $speaker->delete();

        return back()->with('status', 'Speaker deleted.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array', 'min:1'], 'ids.*' => ['integer', 'exists:speakers,id']])['ids'];
        Speaker::whereIn('id', $ids)->delete();

        return back()->with('status', count($ids).' speakers deleted.');
    }

    public function toggle(Speaker $speaker): RedirectResponse
    {
        $speaker->update(['is_active' => ! $speaker->is_active]);

        return back()->with('status', 'Speaker status updated.');
    }
}
