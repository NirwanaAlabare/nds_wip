<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQcInspectListInspectDet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qc_inspect_list_inspect_det', function (Blueprint $table) {
            $table->id();
            $table->string('result')->nullable();
            $table->decimal('rata_rata', 10, 2)->nullable();
            $table->foreignId('id_master_group_inspect')->nullable()->constrained('qc_inspect_master_group_inspect')->onDelete('set null');
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
        Schema::dropIfExists('qc_inspect_list_inspect_det');
    }
}
