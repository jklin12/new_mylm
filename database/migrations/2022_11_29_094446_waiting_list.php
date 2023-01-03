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
        Schema::create('t_waiting_list_new', function (Blueprint $table) {
            $table->id('wi_id');
            $table->string('wi_am');
            $table->string('sp_code')->nullable();
            $table->tinyInteger('wi_name')->default(0); 
            $table->string('wi_phone');
            $table->string('wi_email'); 
            $table->string('wi_address')->nullable(); 
            $table->text('wi_note')->nullable(); 
            $table->text('wi_lat')->nullable(); 
            $table->text('wi_long')->nullable(); 
            $table->text('wi_file_identity')->nullable(); 
            $table->text('wi_file_survei')->nullable(); 
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
        //
    }
};
