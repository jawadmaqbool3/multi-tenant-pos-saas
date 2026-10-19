<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class PointEmployeeAttendanceMenuAtReport extends Migration
{
    /**
     * The Employee Attendance menu entry reuses the existing `/attendance`
     * report screen rather than a dedicated `/employee/attendance` page. Rewrite
     * every company's persisted `settings.asidebar` JSON so the link points back
     * at `/attendance`. Existing companies are handled here; new companies are
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

                    $asidebar[$sIndex]['links'][$lIndex]['link'] = $this->attendanceUrl($link['link'] ?? null);
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

    /**
     * Force the stored link onto `/attendance`, preserving the host of absolute
     * URLs so dev tunnels keep working.
     *
     * @param  string|null  $url
     * @return string
     */
    protected function attendanceUrl($url)
    {
        $parts = is_string($url) ? parse_url($url) : [];

        if (!isset($parts['scheme']) || !isset($parts['host'])) {
            return '/attendance';
        }

        $base = $parts['scheme'] . '://' . $parts['host'];

        if (isset($parts['port'])) {
            $base .= ':' . $parts['port'];
        }

        return $base . '/attendance';
    }
}
