<?php

namespace App\Observers;

use App\Models\User;
use App\Services\AuditLogService;

class UserObserver
{
    public function __construct(
        private readonly AuditLogService $auditLogs,
    ) {
    }

    public function created(User $user): void
    {
        $this->auditLogs->creation($this->module($user), auth()->user(), request()->ip());
    }

    public function updated(User $user): void
    {
        $champsModifies = array_diff(array_keys($user->getChanges()), [
            'remember_token',
            'updated_at',
        ]);

        if ($champsModifies === []) {
            return;
        }

        $this->auditLogs->modification($this->module($user), auth()->user(), request()->ip());
    }

    public function deleted(User $user): void
    {
        $this->auditLogs->suppression($this->module($user), auth()->user(), request()->ip());
    }

    private function module(User $user): string
    {
        return "Utilisateurs - #{$user->id}";
    }
}
