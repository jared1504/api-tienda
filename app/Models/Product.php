<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SaleDetail;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'price_sale',
        'price_order',
        'stock',
    ];

    public function saledetails()
    {
        return $this->hasMany(SaleDetail::class);
    }
}
