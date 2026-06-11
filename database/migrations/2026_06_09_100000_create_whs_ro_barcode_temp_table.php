<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhsRoBarcodeTempTable extends Migration
{
    public function up()
    {
        Schema::connection('mysql_sb')->create('whs_ro_barcode_temp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_lokasi')->nullable();
            $table->string('no_barcode', 50)->nullable();
            $table->string('no_dok_in', 50)->nullable();
            $table->string('no_ws', 50)->nullable();
            $table->unsignedBigInteger('id_jo')->nullable();
            $table->unsignedBigInteger('id_item')->nullable();
            $table->string('goods_code', 50)->nullable();
            $table->string('itemdesc', 255)->nullable();
            $table->string('unit', 20)->nullable();
            $table->string('kode_lok', 50)->nullable();
            $table->string('no_lot', 50)->nullable();
            $table->string('no_roll', 50)->nullable();
            $table->decimal('qty_aktual', 18, 2)->nullable();
            $table->decimal('qty_ro', 18, 2)->nullable();
            $table->string('created_by', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('mysql_sb')->dropIfExists('whs_ro_barcode_temp');
    }
}
