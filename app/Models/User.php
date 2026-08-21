<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->email_verified_at !== null;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: ['GA'];
        $first = mb_strtoupper(mb_substr($parts[0] ?? 'G', 0, 1));
        $last = mb_strtoupper(mb_substr(end($parts) ?: '', 0, 1));
        $initials = $first.($last ?: '');

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 96 96">'
            .'<circle cx="48" cy="48" r="48" fill="#14141f"/>'
            .'<text x="48" y="48" fill="#ffffff" font-family="-apple-system,Segoe UI,Roboto,Arial,sans-serif" '
            .'font-size="38" font-weight="700" text-anchor="middle" dominant-baseline="central">%s</text>'
            .'</svg>',
            htmlspecialchars($initials, ENT_XML1, 'UTF-8')
        );

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
