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
        DB::unprepared('
            CREATE TRIGGER after_fecha_apertura_update
            AFTER UPDATE ON nota_heladeros
            FOR EACH ROW
            BEGIN
                IF OLD.fecha_apertura <> NEW.fecha_apertura THEN
                    INSERT INTO asistencias (user_id, fecha)
                    VALUES (NEW.user_id, NOW());
                END IF;
            END
        ');
       
        DB::unprepared('
            CREATE TRIGGER after_fecha_apertura_insert
            AFTER INSERT ON nota_heladeros
            FOR EACH ROW
            BEGIN
                IF NEW.estado <> 4 THEN
                    INSERT INTO asistencias (user_id, fecha)
                    VALUES (NEW.user_id, NOW());
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS after_fecha_apertura_update');

        DB::unprepared('DROP TRIGGER IF EXISTS after_fecha_apertura_insert');
    }
};
