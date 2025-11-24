<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_name',
        'contact_person',
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
    ];
    public function users()
    {
        return $this->belongsToMany(User::class);
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
