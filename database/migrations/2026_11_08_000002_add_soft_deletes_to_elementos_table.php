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
        Schema::table('elementos', function (Blueprint $table) {
            if (! Schema::hasColumn('elementos', 'deleted_at')) {
                $table->softDeletes();
            }
            if (! Schema::hasColumn('elementos', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->after('deleted_at')->constrained('usuarios')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('elementos', function (Blueprint $table) {
            if (Schema::hasColumn('elementos', 'deleted_by')) {
                $table->dropForeign(['deleted_by']);
                $table->dropColumn('deleted_by');
            }
            if (Schema::hasColumn('elementos', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
