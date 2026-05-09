<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('last_number')->default(0);
            $table->timestamps();
            
            $table->unique('year');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_sequences');
    }
};