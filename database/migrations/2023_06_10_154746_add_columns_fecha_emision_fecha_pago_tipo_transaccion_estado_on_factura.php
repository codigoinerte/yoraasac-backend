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
        Schema::table('facturas', function (Blueprint $table) {
            $table->integer("sucursals_id")->nullable();
            $table->date("fecha_emision")->nullable();
            $table->date("fecha_pago")->nullable()->change();
            $table->integer("tipo_transaccion")->nullable()->default(0);
            $table->integer("id_estado")->nullable()->default(0);
            $table->integer("id_moneda")->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropColumn("sucursals_id");
            $table->dropColumn("fecha_emision");
            $table->date("fecha_pago")->change();
            $table->dropColumn("tipo_transaccion");
            $table->dropColumn("id_estado");
            $table->dropColumn("id_moneda");
        });
    }
};
