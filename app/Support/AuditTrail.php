<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AuditTrail
{
    public static function record(string $action, ?Model $subject = null, ?string $description = null, array $properties = []): void
    {
        DB::table('activity_logs')->insert(['user_id' => auth()->id(), 'action' => $action, 'subject_type' => $subject?->getMorphClass(), 'subject_id' => $subject?->getKey(), 'description' => $description, 'ip_address' => request()?->ip(), 'properties' => $properties ? json_encode($properties) : null, 'created_at' => now(), 'updated_at' => now()]);
    }
}
