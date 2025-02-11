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
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('logo')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('abonnement');
            $table->string('amount')->default(0);
            $table->string('password');
            $table->text('bio')->nullable();  
            $table->string('code_postal')->nullable(); 
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('numerofiscal')->nullable(); 
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
        Schema::dropIfExists('companies');
    }
};
