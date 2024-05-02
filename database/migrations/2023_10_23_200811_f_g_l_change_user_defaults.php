<?php

use App\Enum\StatusVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $v = StatusVisibility::PRIVATE->value;
        DB::statement('ALTER TABLE users MODIFY private_profile TINYINT(1) DEFAULT 1 NOT NULL');
        DB::statement('ALTER TABLE users MODIFY default_status_visibility TINYINT UNSIGNED DEFAULT ' . $v . ' NOT NULL');
        DB::statement('ALTER TABLE users MODIFY prevent_index TINYINT(1) DEFAULT 1 NOT NULL');
        DB::statement('ALTER TABLE users MODIFY language VARCHAR(12) DEFAULT "de"');
        DB::statement('ALTER TABLE users MODIFY likes_enabled TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(): void
    {
        $v = StatusVisibility::PUBLIC->value;
        DB::statement('ALTER TABLE users MODIFY private_profile TINYINT(1) DEFAULT 0 NOT NULL');
        DB::statement('ALTER TABLE users MODIFY default_status_visibility TINYINT UNSIGNED DEFAULT ' . $v . ' NOT NULL');
        DB::statement('ALTER TABLE users MODIFY prevent_index TINYINT(1) DEFAULT 0 NOT NULL');
        DB::statement('ALTER TABLE users MODIFY language VARCHAR(12)');
        DB::statement('ALTER TABLE users MODIFY likes_enabled TINYINT(1) DEFAULT 1 NOT NULL');
    }
};
