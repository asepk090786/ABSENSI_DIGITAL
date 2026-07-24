<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // Use raw SQL to alter column type to TEXT (MySQL)
        DB::statement('ALTER TABLE pengembangan_diri MODIFY pemateri TEXT NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE pengembangan_diri MODIFY pemateri VARCHAR(255) NULL');
    }
};
