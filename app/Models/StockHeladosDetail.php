<?php

namespace App\Models;

use App\Models\StockHelados;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockHeladosDetail extends Model
{
    use HasFactory;

    protected $table = 'stock_helados_detail';

    public function StockHelados()
    {
        return $this->belongsTo(StockHelados::class, 'id');
    }
}
