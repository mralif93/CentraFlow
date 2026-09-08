<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements OAuthenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'staff_id',
        'phone',
        'password',
        'role',
        'department',
        'job_title',
        'designation',
        'employee_code',
        'avatar',
        'last_login_at',
        'last_login_ip',
        'status',
        'hrms_access',
        'hrms_role',
        'payroll_access',
        'payroll_role',
        'clinic_access',
        'clinic_role',
    ];

    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Standardized Enterprise Global Roles.
     */
    public const ROLES = [
        'superadmin',
        'hr_manager',
        'payroll_officer',
        'finance_officer',
        'employee',
    ];

    /**
     * Master Permission Matrix across all microservices.
     * Maps global CentraFlow roles to granular permissions for HRMS, Payroll, and Invoicing.
     */
    public const PERMISSIONS = [
        'superadmin' => [
            // HRMS
            'hrms:admin', 'hrms:employees.manage', 'hrms:attendance.manage', 'hrms:leaves.approve', 'hrms:performance.manage', 'hrms:recruitment.manage',
            // Payroll
            'payroll:admin', 'payroll:calculate', 'payroll:approve', 'payroll:lock', 'payroll:exports.bank', 'payroll:statutory.manage',
            // Invoicing (CIS)
            'invoice:admin', 'invoice:catalog.manage', 'invoice:create', 'invoice:void', 'invoice:reports.view',
        ],
        'hr_manager' => [
            'hrms:employees.manage', 'hrms:attendance.manage', 'hrms:leaves.approve', 'hrms:performance.manage', 'hrms:recruitment.manage',
            'payroll:employees.view', 'payroll:view',
            'invoice:view',
        ],
        'payroll_officer' => [
            'hrms:employees.view', 'hrms:attendance.view', 'hrms:leaves.view',
            'payroll:calculate', 'payroll:approve', 'payroll:lock', 'payroll:exports.bank', 'payroll:statutory.manage', 'payroll:view',
            'invoice:reports.view',
        ],
        'finance_officer' => [
            'hrms:claims.view',
            'payroll:view', 'payroll:exports.bank',
            'invoice:catalog.manage', 'invoice:create', 'invoice:void', 'invoice:reports.view',
        ],
        'employee' => [
            'hrms:self.profile', 'hrms:self.attendance', 'hrms:self.leaves', 'hrms:self.performance',
            'payroll:self.payslip',
            'invoice:self.view',
        ],
    ];

    /**
     * Sub-system specific role mappings (with per-user override support).
     */
    public function getSubsystemRole(string $system): string
    {
        $sys = strtolower($system);

        // Check if custom override is set
        if ($sys === 'hrms' && !empty($this->hrms_role)) {
            return $this->hrms_role;
        }
        if ($sys === 'payroll' && !empty($this->payroll_role)) {
            return $this->payroll_role;
        }
        if (in_array($sys, ['clinic', 'invoicing', 'cis'], true) && !empty($this->clinic_role)) {
            return $this->clinic_role;
        }

        return match ($sys) {
            'hrms' => match ($this->role) {
                'superadmin' => 'Super Admin',
                'hr_manager' => 'HR Administrator',
                default => 'Employee',
            },
            'payroll' => match ($this->role) {
                'superadmin' => 'super_admin',
                'payroll_officer' => 'payroll_officer',
                'finance_officer' => 'finance_director',
                default => 'auditor',
            },
            'clinic', 'invoicing', 'cis' => match ($this->role) {
                'superadmin', 'finance_officer' => 'admin',
                default => 'receptionist',
            },
            default => $this->role,
        };
    }

    /**
     * Get computed enterprise permissions for this identity.
     */
    public function getPermissions(): array
    {
        return self::PERMISSIONS[$this->role] ?? self::PERMISSIONS['employee'];
    }

    /**
     * Get subsystem-specific filtered permissions.
     */
    public function getSubsystemPermissions(string $system): array
    {
        $allPermissions = $this->getPermissions();
        $prefix = match (strtolower($system)) {
            'hrms' => 'hrms:',
            'payroll' => 'payroll:',
            'clinic', 'invoicing', 'cis' => 'invoice:',
            default => '',
        };

        if (empty($prefix)) {
            return $allPermissions;
        }

        return array_values(array_filter($allPermissions, fn ($p) => str_starts_with($p, $prefix)));
    }

    /**
     * Determine comprehensive access control matrix across all sub-systems.
     */
    public function getSubsystemAccess(): array
    {
        $isActive = empty($this->status) || ($this->status === 'active');

        $hrmsAllowed = $isActive && (bool) ($this->hrms_access ?? true);
        $payrollAllowed = $isActive && (bool) ($this->payroll_access ?? true);
        $clinicAllowed = $isActive && (bool) ($this->clinic_access ?? true);

        return [
            'hrms' => [
                'allowed' => $hrmsAllowed,
                'role' => $hrmsAllowed ? $this->getSubsystemRole('hrms') : null,
                'permissions' => $hrmsAllowed ? $this->getSubsystemPermissions('hrms') : [],
            ],
            'payroll' => [
                'allowed' => $payrollAllowed,
                'role' => $payrollAllowed ? $this->getSubsystemRole('payroll') : null,
                'permissions' => $payrollAllowed ? $this->getSubsystemPermissions('payroll') : [],
            ],
            'clinic' => [
                'allowed' => $clinicAllowed,
                'role' => $clinicAllowed ? $this->getSubsystemRole('clinic') : null,
                'permissions' => $clinicAllowed ? $this->getSubsystemPermissions('clinic') : [],
            ],
        ];
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'superadmin') {
            return true;
        }

        return in_array($permission, $this->getPermissions(), true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'hrms_access' => 'boolean',
            'payroll_access' => 'boolean',
            'clinic_access' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        if ($this->exists && $this->roles()->exists()) {
            return $this->roles()->whereIn('name', $roles)->exists();
        }

        return in_array($this->role, $roles, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin' || $this->hasRole(['superadmin', 'super_admin', 'Super Admin']);
    }

    public function getEmployeeCodeAttribute(): ?string
    {
        return $this->attributes['employee_code'] ?? $this->attributes['staff_id'] ?? null;
    }

    public function getDesignationAttribute(): ?string
    {
        return $this->attributes['designation'] ?? $this->attributes['job_title'] ?? null;
    }
}
