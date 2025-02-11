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
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title'); 
            $table->text('theme'); 
            $table->string('association_name');
            $table->string('city'); 
            $table->text('events_offered'); 
            $table->string('iban'); 
            $table->text('description'); 
            $table->text('image')->nullable();
            $table->text('video')->nullable();
            $table->text('fiscal_receipt')->nullable(); 
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
        Schema::dropIfExists('projects');
    }
};
