<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'file_type',
        'assessment_year',
        'financial_year',
        'turnover',
        'status',
        'assigned_to',
        'estimated_completion_date',
        'actual_completion_date',
        'payment_request_date',
        'payment_id',
        'notes',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
