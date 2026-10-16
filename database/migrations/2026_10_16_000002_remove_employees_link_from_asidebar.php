<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RemoveEmployeesLinkFromAsidebar extends Migration
{
    /**
     * The Employees CRUD is merged into Users, so the HR -> Employees sidebar
     * link is dead. Strip it from every company's persisted `settings.asidebar`
     * JSON (new companies are handled by AsideSettingService / SettingSeeder).
     *
     * @return void
     */
    public function up()
    {
        $settings = DB::table('settings')->get();

        foreach ($settings as $setting) {
            $asidebar = json_decode($setting->asidebar, true);

            if (!is_array($asidebar)) {
                continue;
            }

            $changed = false;
            $sections = [];

            foreach ($asidebar as $section) {
                if (!isset($section['links']) || !is_array($section['links'])) {
                    $sections[] = $section;
                    continue;
                }

                $links = array_values(array_filter($section['links'], function ($link) {
                    return !$this->isEmployeesLink($link);
                }));

                if (count($links) !== count($section['links'])) {
                    $changed = true;
                }

                if (empty($links)) {
                    // Drop a section that only contained the removed link.
                    continue;
                }

                $section['links'] = $links;
                $sections[] = $section;
            }

            if ($changed) {
                DB::table('settings')->where('id', $setting->id)->update([
                    'asidebar' => json_encode($sections),
                ]);
            }
        }
    }

    /**
     * A link is the Employees entry when its title is "Employees" or its path
     * ends with "/employees".
     *
     * @param  array  $link
     * @return bool
     */
    protected function isEmployeesLink($link)
    {
        if (($link['title'] ?? null) === 'Employees') {
            return true;
        }

        if (!isset($link['link'])) {
            return false;
        }

        return Str::endsWith(rtrim($link['link'], '/'), '/employees');
    }

    /**
     * Not reversible — the removed link is re-added by the sidebar template for
     * new companies only. Existing companies are intentionally left without it.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
