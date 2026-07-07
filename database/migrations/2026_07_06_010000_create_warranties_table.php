<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('product_serial')->index();
            $table->string('product_code')->nullable()->index();
            $table->string('product_name')->nullable();
            $table->string('production_date')->nullable();
            $table->string('warranty_type')->nullable();
            $table->unsignedSmallInteger('warranty_period_months')->nullable();
            $table->timestamp('activated_at')->nullable()->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->string('activation_status')->default('inquired')->index();
            $table->unsignedTinyInteger('cust_type')->nullable();
            $table->unsignedTinyInteger('cust_sex')->nullable();
            $table->string('cust_name')->nullable();
            $table->string('national_code')->nullable();
            $table->string('state_code')->nullable();
            $table->string('state_name')->nullable();
            $table->string('city_code')->nullable();
            $table->string('city_name')->nullable();
            $table->text('address')->nullable();
            $table->string('mehrsoft_sync_status')->default('not_sent')->index();
            $table->timestamp('mehrsoft_synced_at')->nullable();
            $table->string('mehrsoft_document_no')->nullable();
            $table->string('mehrsoft_fix_no')->nullable();
            $table->text('mehrsoft_last_error')->nullable();
            $table->json('mehrsoft_product_payload')->nullable();
            $table->json('mehrsoft_save_payload')->nullable();
            $table->json('mehrsoft_save_response')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'product_serial', 'deleted_at'], 'warranties_user_serial_deleted_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranties');
    }
};
