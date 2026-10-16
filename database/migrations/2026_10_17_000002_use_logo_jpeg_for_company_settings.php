<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UseLogoJpegForCompanySettings extends Migration
{
    /**
     * Point the seeded (global + company) settings at the bundled logo.jpeg
     * default instead of the placeholder logo.svg.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->where('logo', 'logo.svg')->update(['logo' => 'logo.jpeg']);
    }

    /**
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where('logo', 'logo.jpeg')->update(['logo' => 'logo.svg']);
    }
}
