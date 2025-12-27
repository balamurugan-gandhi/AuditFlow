<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'icon',
        'icon_color',
        'enabled',
    ];
}
