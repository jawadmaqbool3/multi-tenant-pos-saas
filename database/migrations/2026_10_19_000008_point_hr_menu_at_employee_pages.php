<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class PointHrMenuAtEmployeePages extends Migration
{
    /**
     * The HR section now opens the dedicated employee screens instead of the
     * generic users list. Rewrite every company's persisted `settings.asidebar`
     * JSON so the HR -> Employees link points at `/employee` and HR -> Employee
     * Attendance points at `/employee/attendance`. Existing companies are
     * handled here; new companies are handled by AsideSettingService.
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
                    $target = $this->targetFor($link);

                    if ($target === null) {
                        continue;
                    }

                    $asidebar[$sIndex]['links'][$lIndex]['link'] = $target['link'];
                    $asidebar[$sIndex]['links'][$lIndex]['permission'] = $target['permission'];
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
     * The new link/permission for a menu entry, or null when it is not one we
     * manage here.
     *
     * @param  array  $link
     * @return array|null
     */
    protected function targetFor($link)
    {
        switch ($link['title'] ?? null) {
            case 'Employees':
                return [
                    'link' => $this->path($link['link'] ?? null, '/employee'),
                    'permission' => 'view employee',
                ];
            case 'Employee Attendance':
                return [
                    'link' => $this->path($link['link'] ?? null, '/employee/attendance'),
                    'permission' => 'view employee attendance report',
                ];
        }

        return null;
    }

    /**
     * Preserve the host of the stored URL (it may be an absolute seeder URL),
     * swapping only the path.
     *
     * @param  string|null  $url
     * @param  string  $path
     * @return string
     */
    protected function path($url, $path)
    {
        if (!is_string($url) || $url === '') {
            return $path;
        }

        $parts = parse_url($url);

        if (!isset($parts['scheme']) || !isset($parts['host'])) {
            return $path;
        }

        $base = $parts['scheme'] . '://' . $parts['host'];

        if (isset($parts['port'])) {
            $base .= ':' . $parts['port'];
        }

        return $base . $path;
    }
}
