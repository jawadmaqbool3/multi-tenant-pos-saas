<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AppendEmployeesMenuToUsers extends Migration
{
    /**
     * The standalone Employees page was merged into Users, which left the
     * sidebar without a recognisable `Employees` entry. Add an Employees link
     * to the Users section of every company's persisted `settings.asidebar`
     * JSON so employee management stays discoverable. New companies are
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
            $asidebar = $this->appendEmployees($asidebar);

            if (json_encode($asidebar) === json_encode($original)) {
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
     * Insert the Employees link into the Users section (creating the section
     * when it is missing) while keeping the operation idempotent.
     *
     * @param  array  $asidebar
     * @return array
     */
    protected function appendEmployees(array $asidebar)
    {
        $employees = [
            'title' => 'Employees',
            'permission' => 'view user',
            'link' => '/users',
        ];

        foreach ($asidebar as $index => $section) {
            if (!isset($section['links']) || !is_array($section['links'])) {
                continue;
            }

            if (!$this->isUsersSection($section)) {
                continue;
            }

            foreach ($section['links'] as $link) {
                if (($link['title'] ?? null) === 'Employees') {
                    return $asidebar;
                }
            }

            array_unshift($asidebar[$index]['links'], $employees);

            return $asidebar;
        }

        $asidebar[] = [
            'name' => 'Users',
            'icon' => 'bi bi-people',
            'links' => [$employees],
        ];

        return $asidebar;
    }

    /**
     * A section holds the Users links when it is named "Users" or contains a
     * link pointing at the users index.
     *
     * @param  array  $section
     * @return bool
     */
    protected function isUsersSection(array $section)
    {
        if (($section['name'] ?? null) === 'Users') {
            return true;
        }

        foreach ($section['links'] as $link) {
            if (!isset($link['link'])) {
                continue;
            }

            if (rtrim($link['link'], '/') === '/users') {
                return true;
            }
        }

        return false;
    }
}
