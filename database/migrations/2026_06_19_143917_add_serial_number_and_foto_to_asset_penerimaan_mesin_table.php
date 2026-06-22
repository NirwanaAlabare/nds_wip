<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSerialNumberAndFotoToAssetPenerimaanMesinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asset_penerimaan_mesin', function (Blueprint $table) {
            $table->string('serial_number')->nullable()->after('id_jenis');
            $table->string('foto')->nullable()->after('serial_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asset_penerimaan_mesin', function (Blueprint $table) {
            $table->dropColumn(['serial_number', 'foto']);
        });
    }
}
