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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default(0);
            $table->float('rating')->default(0);
            $table->float('points')->default(0);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email',30)->nullable();
            $table->string('phone',20)->nullable();
            $table->string('address')->nullable();
            $table->string('bio')->nullable();
            $table->string('facebook',50)->nullable();
            $table->string('instagram',50)->nullable();
            $table->string('logo',80)->default('images/default/logo.png')->nullable();
            $table->string('cover',80)->default('images/default/cover.png')->nullable();
            $table->foreignId('vendor_cat_id')->constrained();
            $table->foreignId('vendor_subcat_id')->constrained();
            $table->decimal('lgt',10,8)->nullable();
            $table->decimal('ltt',10,8)->nullable();
            $table->foreignId('division_id')->constrained();
            $table->foreignId('district_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('upazila_id')->nullable()->constrained()->onDelete('cascade');

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
        Schema::dropIfExists('vendors');
    }
};
