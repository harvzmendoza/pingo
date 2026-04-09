<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('business_name')->nullable()->after('email');
            $table->text('business_description')->nullable()->after('business_name');
            $table->string('business_category')->nullable()->after('business_description');
            $table->timestamp('onboarding_completed_at')->nullable()->after('business_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'business_name',
                'business_description',
                'business_category',
                'onboarding_completed_at',
            ]);
        });
    }
};
