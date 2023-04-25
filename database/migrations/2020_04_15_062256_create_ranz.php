<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRanz extends Migration
{
    public function up()
    {
        Schema::create('ranz', function (Blueprint $table) {
            $table->id();
            $table->date('trans_date');
            $table->time('trans_time');
            $table->integer('trans_combo');
            $table->string('trans_text');
            $table->decimal('trans_number',20,6);
            $table->integer('trans_id');
            $table->string('trans_desc');
            $table->string('status');
            $table->timestamps();
        });
        Schema::create('ranz_details', function (Blueprint $table) {
            $table->id();
            $table->integer('main_id');
            $table->string('item_id');
            $table->string('item_description');
            $table->decimal('item_qty',20,6);
            $table->decimal('item_amount',20,6);
            $table->decimal('item_total',20,6);
            $table->timestamps();

        });

    }
    public function down()
    {
        Schema::dropIfExists('ranz');
        Schema::dropIfExists('ranz_details');
    }
    
}
