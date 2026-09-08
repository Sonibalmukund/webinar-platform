<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Webinar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
class GeneralSettingsController extends Controller {
    public function site():View{return view('pages.admin.general.site',['settings'=>DB::table('settings')->where('group','site')->pluck('value','key')]);}
    public function updateSite(Request $request):RedirectResponse{$data=$request->validate(['site_name'=>['required','string','max:100'],'footer_text'=>['required','string','max:500'],'admin_email'=>['required','email'],'site_logo'=>['nullable','image','max:5120'],'small_logo'=>['nullable','image','max:2048'],'favicon'=>['nullable','image','max:1024']]);foreach(['site_logo','small_logo','favicon'] as $key)if($request->hasFile($key))$data[$key]=$this->upload($request->file($key),'site');foreach($data as $key=>$value)DB::table('settings')->updateOrInsert(['key'=>$key],['group'=>'site','value'=>$value,'is_public'=>true,'created_at'=>now(),'updated_at'=>now()]);return back()->with('status','Site settings saved.');}
    public function banners():View{return view('pages.admin.general.index',['type'=>'banner','rows'=>Banner::with('webinar')->latest()->paginate(15)]);}
    public function bannerForm(?Banner $banner=null):View{return view('pages.admin.general.banner-form',['banner'=>$banner??new Banner,'webinars'=>Webinar::orderBy('title')->get()]);}
    public function saveBanner(Request $request,?Banner $banner=null):RedirectResponse{
        $banner=$banner??new Banner;
        $type=(string)$request->input('media_type');
        $data=$request->validate([
            'webinar_id'=>['required','exists:webinars,id'],'title'=>['required','string','max:255'],'media_type'=>['required','in:image,video'],
            'image_media'=>[\Illuminate\Validation\Rule::requiredIf($type==='image'&&(!$banner->exists||$banner->media_type!=='image')),'nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'video_media'=>[\Illuminate\Validation\Rule::requiredIf($type==='video'&&!$request->filled('media_url')&&(!$banner->exists||$banner->media_type!=='video')),'nullable','file','mimes:mp4,webm,mov','max:20480'],
            'media_url'=>['nullable','url'],
            'starts_at'=>['nullable','date'],'ends_at'=>['nullable','date','after:starts_at'],
        ]);
        $file=$type==='image'?$request->file('image_media'):$request->file('video_media');
        if($file)$data['media_path']=$this->upload($file,'banners');
        if($type==='image')$data['media_url']=null;
        unset($data['image_media'],$data['video_media']);
        $data['is_active']=$banner->exists?$banner->is_active:true;$banner->fill($data)->save();
        return redirect()->route('admin.general.banners')->with('status','Banner saved.');
    }
    public function destroyBanner(Banner $banner):RedirectResponse{$banner->delete();return back()->with('status','Banner deleted.');}
    public function bulkDeleteBanners(Request $request):RedirectResponse{$ids=$request->validate(['ids'=>['required','array','min:1'],'ids.*'=>['integer','exists:banners,id']])['ids'];Banner::whereIn('id',$ids)->delete();return back()->with('status',count($ids).' banners deleted.');}
    public function toggleBanner(Banner $banner):RedirectResponse{$banner->update(['is_active'=>!$banner->is_active]);return back()->with('status','Banner status updated.');}
    public function reorderBanners(Request $request):RedirectResponse{$ids=$request->validate(['order'=>['required','array'],'order.*'=>['integer','exists:banners,id']])['order'];foreach($ids as $index=>$id)Banner::whereKey($id)->update(['display_order'=>$index]);return back()->with('status','Banner order saved.');}
    public function brands():View{return view('pages.admin.general.index',['type'=>'brand','rows'=>Brand::with('webinar')->latest()->paginate(15)]);}
    public function brandForm(?Brand $brand=null):View{return view('pages.admin.general.brand-form',['brand'=>$brand??new Brand,'webinars'=>Webinar::orderBy('title')->get()]);}
    public function saveBrand(Request $request,?Brand $brand=null):RedirectResponse{$brand=$brand??new Brand;$data=$request->validate(['webinar_id'=>['required','exists:webinars,id'],'name'=>['required','string','max:255'],'website_url'=>['nullable','url'],'logo'=>[$brand->exists?'nullable':'required','image','max:5120']]);if($request->hasFile('logo'))$data['logo_path']=$this->upload($request->file('logo'),'brands');unset($data['logo']);$data['is_active']=$brand->exists?$brand->is_active:true;$brand->fill($data)->save();return redirect()->route('admin.general.brands')->with('status','Brand saved.');}
    public function destroyBrand(Brand $brand):RedirectResponse{$brand->delete();return back()->with('status','Brand deleted.');}
    public function bulkDeleteBrands(Request $request):RedirectResponse{$ids=$request->validate(['ids'=>['required','array','min:1'],'ids.*'=>['integer','exists:brands,id']])['ids'];Brand::whereIn('id',$ids)->delete();return back()->with('status',count($ids).' brands deleted.');}
    public function toggleBrand(Brand $brand):RedirectResponse{$brand->update(['is_active'=>!$brand->is_active]);return back()->with('status','Brand status updated.');}
    private function upload($file,string $folder):string{$directory=public_path('uploads/'.$folder);File::ensureDirectoryExists($directory);$name=Str::uuid().'.'.$file->getClientOriginalExtension();$file->move($directory,$name);return '/uploads/'.$folder.'/'.$name;}
}
