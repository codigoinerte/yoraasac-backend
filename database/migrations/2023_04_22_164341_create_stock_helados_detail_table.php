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
        // Schema::table('productos', function (Blueprint $table) {
        //     $table->index('codigo');
        // });
        
        Schema::create('stock_helados_detail', function (Blueprint $table) {
            $table->id();
            $table->string('codigo');
            $table->foreignId("stock_helados_id")->references("id")->on("stock_helados")->onDelete('cascade');
            $table->integer("cantidad");
            $table->timestamps();

            $table->foreign("codigo")->references("codigo")->on("productos");
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('productos', function (Blueprint $table) {
        //     $table->dropIndex(['codigo']);
        // });

        Schema::dropIfExists('stock_helados_detail');        
    }
};
