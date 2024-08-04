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
        Schema::table('stock_helados_detail', function (Blueprint $table) {
            $table->addColumn("integer", "caja")->default(0);
            $table->addColumn("integer", "caja_cantidad")->default(0);            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_helados_detail', function (Blueprint $table) {
            $table->dropColumn("caja");
            $table->dropColumn("caja_cantidad");
        });
    }
};
