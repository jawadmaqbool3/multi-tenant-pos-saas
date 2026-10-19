<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RenameEmployeeAttendanceMenu extends Migration
{
    /**
     * The HR "Employee Attendance" entry is just an attendance report, so label
     * it "Attendance". Rewrite every company's persisted `settings.asidebar`
     * JSON title. Existing companies are handled here; new companies are
     * handled by AsideSettingService.
     *
     * @return void
     */
    public function up()
    {
        foreach (DB::table('settings')->get() as $setting) {
            $asidebar = json_decode($setting->asidebar, true);

            if (!is_array($asidebar)) {
                continue;
            }

            $original = $asidebar;
            $changed = false;

            foreach ($asidebar as $sIndex => $section) {
                if (!isset($section['links']) || !is_array($section['links'])) {
                    continue;
                }

                foreach ($section['links'] as $lIndex => $link) {
                    if (($link['title'] ?? null) !== 'Employee Attendance') {
                        continue;
                    }

                    $asidebar[$sIndex]['links'][$lIndex]['title'] = 'Attendance';
                    $changed = true;
                }
            }

            if (!$changed || json_encode($asidebar) === json_encode($original)) {
                continue;
            }

            DB::table('settings')->where('id', $setting->id)->update([
                'asidebar' => json_encode($asidebar),
            ]);
        }
    }

    /**
     * Not reversible — the sidebar layout is owned by AsideSettingService.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
