<?php

namespace Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('label')->unique();
            $table->text('icon')->nullable();
            $table->string('route')->nullable();
            $table->string('url')->nullable();
            $table->string('shortcut')->nullable();
            $table->integer('parent_id')->nullable();
            $table->integer('position')->nullable(false);
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
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
        Schema::dropIfExists('menu_items');
    }
};
