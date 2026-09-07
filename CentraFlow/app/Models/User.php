<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
        'status',
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
     * Sub-system specific role mappings.
     */
    public function getSubsystemRole(string $system): string
    {
        return match (strtolower($system)) {
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
        ];
    }
}
