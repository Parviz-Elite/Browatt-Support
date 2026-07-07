<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $columns = [
                'recipient_first_name',
                'recipient_last_name',
                'mobile',
                'district',
                'postal_code',
                'latitude',
                'longitude',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('user_addresses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            if (! Schema::hasColumn('user_addresses', 'recipient_first_name')) {
                $table->string('recipient_first_name')->nullable()->after('title');
            }

            if (! Schema::hasColumn('user_addresses', 'recipient_last_name')) {
                $table->string('recipient_last_name')->nullable()->after('recipient_first_name');
            }

            if (! Schema::hasColumn('user_addresses', 'mobile')) {
                $table->string('mobile')->nullable()->after('recipient_last_name');
            }

            if (! Schema::hasColumn('user_addresses', 'district')) {
                $table->string('district')->nullable()->after('city');
            }

            if (! Schema::hasColumn('user_addresses', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('district');
            }

            if (! Schema::hasColumn('user_addresses', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('address');
            }

            if (! Schema::hasColumn('user_addresses', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }
};
