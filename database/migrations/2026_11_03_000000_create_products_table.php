<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name')->nullable();
            $table->unsignedSmallInteger('warranty_months')->nullable();
            $table->boolean('online_activation_enabled')->default(true)->index();
            $table->timestamp('catalog_synced_at')->nullable();
            $table->timestamp('warranty_checked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('products')->insert([
            'code' => '300101014',
            'online_activation_enabled' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
