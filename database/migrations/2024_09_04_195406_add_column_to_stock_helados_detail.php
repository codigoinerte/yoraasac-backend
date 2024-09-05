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
            $table->addColumn("double", "cant_litro_devuelta")->default(0);
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
            $table->dropColumn("cant_litro_devuelta");
        });
    }
};
