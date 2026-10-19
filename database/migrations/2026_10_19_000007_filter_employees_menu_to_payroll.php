<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FilterEmployeesMenuToPayroll extends Migration
{
    /**
     * The Employees screen is the payroll roster, so the HR -> Employees link
     * must open the users list narrowed to payroll-enabled staff. Rewrite every
     * company's persisted `settings.asidebar` JSON: point the Employees link at
     * `/users?filter=payroll`. Existing companies are handled here; new
     * companies are handled by AsideSettingService.
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
                    if (!$this->isEmployeesLink($link)) {
                        continue;
                    }

                    $asidebar[$sIndex]['links'][$lIndex]['link'] = $this->payrollUrl($link['link'] ?? '/users');
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
     * A link is the Employees entry when its title is "Employees".
     *
     * @param  array  $link
     * @return bool
     */
    protected function isEmployeesLink($link)
    {
        return ($link['title'] ?? null) === 'Employees';
    }

    /**
     * Force the stored link onto the payroll-filtered users list while keeping
     * its path (so a customised slug still resolves).
     *
     * @param  string  $url
     * @return string
     */
    protected function payrollUrl($url)
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            $path = '/users';
        }

        return rtrim($path, '/') . '?filter=payroll';
    }
}
