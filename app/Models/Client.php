<?php

namespace App\Models;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'phone',
        'direction'
    ];

}
