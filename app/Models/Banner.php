<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Banner extends Model {protected $guarded=[];protected function casts():array{return ['is_active'=>'boolean','starts_at'=>'datetime','ends_at'=>'datetime'];}public function webinar():BelongsTo{return $this->belongsTo(Webinar::class);}}
