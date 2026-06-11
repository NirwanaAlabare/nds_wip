<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupplierToInmaterialBarcodeRiTempTable extends Migration
{
    public function up()
    {
        Schema::connection('mysql_sb')->table('whs_inmaterial_barcode_ri_temp', function (Blueprint $table) {
            $table->string('id_supplier', 50)->nullable()->after('no_bppb');
            $table->string('nama_supplier', 255)->nullable()->after('id_supplier');
        });
    }

    public function down()
    {
        Schema::connection('mysql_sb')->table('whs_inmaterial_barcode_ri_temp', function (Blueprint $table) {
            $table->dropColumn(['id_supplier', 'nama_supplier']);
        });
    }
}
