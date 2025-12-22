<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Client extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'business_name',
        'contact_person',
        'file_id',
        'email',
        'phone',
        'alternate_phone',
        'whatsapp_number',
        'pan_number',
        'gst_number',
        'tin_number',
        'business_type',
        'filing_cycle',
        'address',
        'whatsapp_notification_enabled',
    ];
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    /**
     * Scope a query to only include clients assigned to the given user.
     */
    public function scopeForUser($query, User $user)
    {
        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            return $query;
        }

        return $query->whereHas('users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }
}
