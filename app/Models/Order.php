<?php

namespace App\Models;

use App\Models\OrderDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    protected $fillable=[
        'total',
        'user_id',
        'status'
    ];


    public function user()
    {
        return $this->belongsTo(User::class)->select('name');
    }

    public function orderdetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
