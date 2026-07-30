<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    public static function log(
        $action,
        $module,
        $description = null
    ) {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
