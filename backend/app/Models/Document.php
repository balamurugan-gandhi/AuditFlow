<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\File;
use App\Models\User;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'uploaded_by',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'type',
    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
