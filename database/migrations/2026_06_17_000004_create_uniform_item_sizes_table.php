<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uniform_item_sizes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('uniform_item_id')->constrained('uniform_items')->cascadeOnDelete();
            $table->string('size');
            $table->unsignedInteger('stock')->default(0);
            $table->timestamps();

            $table->unique(['uniform_item_id', 'size']);
        });

        DB::table('uniform_items')
            ->whereNotNull('size')
            ->orderBy('id')
            ->get()
            ->each(function ($item): void {
                DB::table('uniform_item_sizes')->insertOrIgnore([
                    'uniform_item_id' => $item->id,
                    'size' => $item->size,
                    'stock' => max(0, (int) $item->available_quantity),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('uniform_item_sizes');
    }
};
