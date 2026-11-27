<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->default('Digital Saldo');
            $table->string('icon', 50)->default('card_giftcard');
            $table->string('badge')->nullable();
            $table->unsignedInteger('points_cost');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reward_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reward_name'); // salinan nama saat ditukar
            $table->unsignedInteger('points_spent');
            $table->string('status', 20)->default('diproses'); // diproses | selesai
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redemptions');
        Schema::dropIfExists('reward_items');
    }
};
