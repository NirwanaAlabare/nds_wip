<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQcInspectListInspect extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qc_inspect_list_inspect', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_whs_lokasi_inmaterial')->nullable()->constrained('whs_lokasi_inmaterial')->onDelete('set null');
            $table->date('tgl_pl')->nullable();
            $table->string('no_pl')->nullable();
            $table->string('no_lot')->nullable();
            $table->string('color')->nullable();
            $table->integer('id_item')->nullable();
            $table->string('supplier')->nullable();
            $table->string('buyer')->nullable();
            $table->string('style')->nullable();
            $table->integer('qty_roll')->nullable();
            $table->string('notes')->nullable();
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
        Schema::dropIfExists('qc_inspect_list_inspect');
    }
}
