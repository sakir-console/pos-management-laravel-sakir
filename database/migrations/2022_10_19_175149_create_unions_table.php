<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *llldvgigig
     * @return void
     */
    public function up()
    {
        Schema::create('unions', function (Blueprint $table) {
            $table->id();
            $table->string('name',40);
            $table->string('bn_name',40);
            $table->foreignId('upazila_id')->constrained()->onDelete('cascade');
            $table->decimal('lgt',10,8)->nullable();
            $table->decimal('ltt',10,8)->nullable();
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
        Schema::dropIfExists('natives');
    }
};
