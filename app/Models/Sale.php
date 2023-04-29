<?php

namespace App\Models;

use App\Models\User;
use App\Models\Client;
use App\Models\SaleDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $fillable=[
        'total',
        'user_id',
        'status',
        'created_at'
    ];
    public function user()
    {
        return $this->belongsTo(User::class)->select('name');
    }

    public function saledetails()
    {
        return $this->hasMany(SaleDetail::class);
    }
    
}
