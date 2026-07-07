<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('app_settings')
            ->whereNotNull('value')
            ->orderBy('id')
            ->each(function (object $setting): void {
                $value = (string) $setting->value;

                if ($this->isJson($value)) {
                    return;
                }

                $decrypted = Crypt::decryptString($value);

                DB::table('app_settings')
                    ->where('id', $setting->id)
                    ->update([
                        'value' => $this->encodeJson($this->decodeJson($decrypted)),
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        DB::table('app_settings')
            ->whereNotNull('value')
            ->orderBy('id')
            ->each(function (object $setting): void {
                $value = (string) $setting->value;

                if (! $this->isJson($value)) {
                    return;
                }

                DB::table('app_settings')
                    ->where('id', $setting->id)
                    ->update([
                        'value' => Crypt::encryptString($this->encodeJson($this->decodeJson($value))),
                        'updated_at' => now(),
                    ]);
            });
    }

    private function isJson(string $value): bool
    {
        json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(string $value): array
    {
        $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, mixed>  $value
     */
    private function encodeJson(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
};
