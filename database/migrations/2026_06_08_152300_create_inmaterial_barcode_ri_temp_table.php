<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInmaterialBarcodeRiTempTable extends Migration
{
    public function up()
    {
        Schema::connection('mysql_sb')->create('whs_inmaterial_barcode_ri_temp', function (Blueprint $table) {
            $table->id();
            $table->string('id_roll', 50);
            $table->string('no_bppb', 50)->nullable();
            $table->unsignedBigInteger('id_bppb')->nullable();
            $table->unsignedBigInteger('id_so_det')->nullable();
            $table->unsignedBigInteger('id_item')->nullable();
            $table->unsignedBigInteger('id_jo')->nullable();
            $table->unsignedBigInteger('id_po')->nullable();
            $table->string('no_ws', 100)->nullable();
            $table->string('pono', 100)->nullable();
            $table->string('goods_code', 100)->nullable();
            $table->string('itemdesc', 255)->nullable();
            $table->string('unit', 30)->nullable();
            $table->decimal('qty_out', 20, 2)->default(0);
            $table->decimal('qty_retur', 20, 2)->default(0);
            $table->decimal('qty_reject', 20, 2)->default(0);
            $table->string('created_by', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('mysql_sb')->dropIfExists('whs_inmaterial_barcode_ri_temp');
    }
}
