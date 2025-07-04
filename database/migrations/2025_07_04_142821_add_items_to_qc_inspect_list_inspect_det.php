<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddItemsToQcInspectListInspectDet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qc_inspect_list_inspect_det', function (Blueprint $table) {
            $table->foreignId('id_inspect_list_header')->nullable()->constrained('qc_inspect_list_inspect')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qc_inspect_list_inspect_det', function (Blueprint $table) {
            //
        });
    }
}
