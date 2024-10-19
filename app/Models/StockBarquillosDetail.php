<?php

namespace App\Models;

use App\Models\StockBarquillos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockBarquillosDetail extends Model
{
    use HasFactory;

    protected $table = 'stock_barquillos_detail';

    public function StockBarquillos()
    {
        return $this->belongsTo(StockBarquillos::class, 'id');
    }
}
