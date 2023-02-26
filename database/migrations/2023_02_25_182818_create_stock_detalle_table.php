<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_detalle', function (Blueprint $table) {
            $table->id();
            $table->string("codigo");
            $table->integer("cantidad");
            $table->string("num_serie",50);
            $table->string("ubicacion",50);
            $table->foreignId("stocks_id")->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_detalle');
    }
};
