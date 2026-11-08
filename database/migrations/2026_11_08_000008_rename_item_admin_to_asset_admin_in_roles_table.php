<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Rol;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $assetAdmin = Rol::firstOrCreate(['rol' => 'asset_admin']);
        $itemAdmin = Rol::where('rol', 'item_admin')->first();

        if ($itemAdmin) {
            $userIds = DB::table('usuario_roles')->where('rol_id', $itemAdmin->id)->pluck('user_id');
            foreach ($userIds as $userId) {
                DB::table('usuario_roles')->updateOrInsert([
                    'user_id' => $userId,
                    'rol_id' => $assetAdmin->id,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $itemAdmin = Rol::firstOrCreate(['rol' => 'item_admin']);
        $assetAdmin = Rol::where('rol', 'asset_admin')->first();

        if ($assetAdmin) {
            $userIds = DB::table('usuario_roles')->where('rol_id', $assetAdmin->id)->pluck('user_id');
            foreach ($userIds as $userId) {
                DB::table('usuario_roles')->updateOrInsert([
                    'user_id' => $userId,
                    'rol_id' => $itemAdmin->id,
                ]);
            }
        }
    }
};
