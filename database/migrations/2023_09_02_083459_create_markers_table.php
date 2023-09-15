<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarkersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('markers', function (Blueprint $table) {
            $table->id();
            $table->date('tgl_cutting');
            $table->string('kode');
            $table->bigInteger('act_costing_id');
            $table->string('act_costing_ws');
            $table->string('color');
            $table->string('panel');
            $table->double('panjang_marker');
            $table->string('unit_panjang_marker');
            $table->string('comma_marker');
            $table->string('unit_comma_marker');
            $table->double('lebar_marker');
            $table->string('unit_lebar_marker');
            $table->integer('gelar_qty');
            $table->string('po_marker');
            $table->string('urutan_marker');
            $table->string('cons_marker');
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
        Schema::dropIfExists('marker');
    }
}
