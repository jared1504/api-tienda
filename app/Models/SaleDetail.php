<?php

namespace App\Models;

use App\Models\Sale;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'amount',
        'price_sale',
        'sale_id',
        'product_id',
    ];
    
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
}
