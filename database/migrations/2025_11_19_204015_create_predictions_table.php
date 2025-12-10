<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Hipodromlar tablosu
        Schema::create('hippodromes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Veliefendi, İzmir, Ankara vs.
            $table->string('city');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tahminler tablosu
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hippodrome_id')->constrained()->onDelete('cascade');
            $table->date('race_date');
            $table->time('race_time')->nullable();
            $table->integer('race_number');
            $table->enum('access_level', ['standard', 'premium'])->default('standard');
            $table->string('race_title')->nullable();

            // Tahmin içerikleri
            $table->text('basic_prediction')->nullable(); // Standart kullanıcılar için
            $table->text('detailed_analysis')->nullable(); // Premium kullanıcılar için
            $table->text('banker_tips')->nullable(); // Sadece Premium
            $table->json('statistics')->nullable(); // İstatistikler (JSON)

            // Sonuç takibi
            $table->string('winning_horse')->nullable();
            $table->decimal('winning_odds', 8, 2)->nullable();
            $table->enum('prediction_result', ['won', 'lost', 'pending'])->default('pending');

            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['race_date', 'status']);
            $table->index(['hippodrome_id', 'race_date']);
            $table->index('access_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
        Schema::dropIfExists('hippodromes');
    }
};
