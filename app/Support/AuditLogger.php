<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AuditLogger
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public static function log(string $action, ?Model $subject = null, array $properties = []): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties === [] ? null : $properties,
            'ip_address' => request()?->ip(),
        ]);
    }
}
