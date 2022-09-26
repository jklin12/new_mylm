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
        Schema::create('t_bukti_tf', function (Blueprint $table) {
            $table->id('bukti_tf_id');
            $table->string('bukti_tf_cust');
            $table->string('bukti_tf_inv')->nullable();
            $table->tinyInteger('bukti_tf_status')->default(0); 
            $table->date('bukti_tf_date');
            $table->text('bukti_tf_file'); 
            $table->text('bukti_tf_desc')->nullable(); 
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
        Schema::dropIfExists('t_bukti_tf');
    }
};
