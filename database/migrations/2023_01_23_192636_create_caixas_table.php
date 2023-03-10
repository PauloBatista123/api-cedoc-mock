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
        Schema::create('caixas', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['disponivel', 'ocupado'])->default('disponivel');
            $table->string('numero')->unique();
            $table->decimal('espaco_total', 8, 2);
            $table->decimal('espaco_ocupado', 8, 2)->nullable();
            $table->decimal('espaco_disponivel', 8, 2)->nullable();
            $table->unsignedBigInteger('predio_id');
            $table->unsignedBigInteger('andar_id');
            $table->timestamps();

            $table->foreign('predio_id')->references('id')->on('predios');
            $table->foreign('andar_id')->references('id')->on('andars');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('caixas');
    }
};
