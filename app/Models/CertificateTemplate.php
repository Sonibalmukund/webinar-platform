<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateTemplate extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['design' => 'array', 'is_default' => 'boolean'];
    }
}
