<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'category_id',
        'product_name',
        'sku',
        'unit',
        'image_path',
        'low_stock_threshold',
        'color',
        'size',
        'weight',
        'price',
        'status',
        'stock_qty',
    ];

    protected $casts = [
        'status' => 'boolean',
        'price'  => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
