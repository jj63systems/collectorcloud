<?php

namespace App\Models\Tenant;

use App\Models\Traits\HasTeamRoles;
use App\Notifications\WelcomeSetPassword;
use App\Services\AccessService;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


class TenantUser extends Authenticatable implements HasEmailAuthentication, MustVerifyEmail, CanResetPasswordContract
{
    use UsesTenantConnection;
    use Notifiable;
    use HasFactory;
    use LogsActivity;
    use CanResetPassword;
    use HasTeamRoles;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_superuser',
        'is_external_user',
        'current_team_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'has_email_authentication' => 'boolean',
        ];
    }

    public function hasEmailAuthentication(): bool
    {
        return $this->has_email_authentication;
    }

    public function toggleEmailAuthentication(bool $condition): void
    {
        $this->has_email_authentication = $condition;
        $this->save();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'email',
                'is_superuser',
                'is_external_user',
                'has_email_authentication',
            ])
            ->useLogName('Users')
            ->logOnlyDirty();
    }


    public function sendPasswordSetupNotification(string $token): void
    {
        $this->notify(new WelcomeSetPassword($token));
    }


    public function teams()
    {
        return $this->belongsToMany(
            \App\Models\Tenant\CcTeam::class,
            'cc_team_user',
            'user_id',
            'team_id'
        )->withTimestamps();
    }

    public function currentTeam()
    {
        return $this->belongsTo(CcTeam::class, 'current_team_id');
    }

    public function canAccess(string $permission, CcTeam $team): bool
    {
        return AccessService::canAccess($this, $team, $permission);
    }


}
