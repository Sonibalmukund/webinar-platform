<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Brand extends Model {protected $guarded=[];protected function casts():array{return ['is_active'=>'boolean'];}public function webinar():BelongsTo{return $this->belongsTo(Webinar::class);}}
