<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\Payment;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'file_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'total_amount',
        'tax_amount',
        'total_tax_amount',
        'auditor_fee',
        'status',
        'notes',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function file()
    {
        return $this->belongsTo(\App\Models\File::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
