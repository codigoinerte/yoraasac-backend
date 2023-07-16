<?php

namespace App\Models;

use App\Models\StockHeladosDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockHelados extends Model
{
    use HasFactory;

    public function stockHeladosDetail()
    {
        return $this->hasMany(stockHeladosDetail::class, 'stock_helados_id')->onDelete('cascade');
    }
}
