<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MoveEmployeesMenuToHr extends Migration
{
    /**
     * Employee management belongs with HR, so the Employees link moves out of
     * the Users section into a dedicated HR section. Rewrite every company's
     * persisted `settings.asidebar` JSON: pull the Employees link out of
     * whichever section currently holds it and insert it into an HR section,
     * creating that section just before Users when necessary. Existing
     * companies are handled here; new companies are handled by
     * AsideSettingService.
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

            // Idempotent: already sitting in an HR section.
            if ($this->hrEmployeesPresent($asidebar)) {
                continue;
            }

            $original = $asidebar;
            $employees = null;
            $sections = [];

            foreach ($asidebar as $section) {
                if (!isset($section['links']) || !is_array($section['links'])) {
                    $sections[] = $section;
                    continue;
                }

                $links = [];

                foreach ($section['links'] as $link) {
                    if ($this->isEmployeesLink($link)) {
                        $employees = $employees ?: $link;
                        continue;
                    }

                    $links[] = $link;
                }

                if (empty($links)) {
                    // Drop a section that only held Employees.
                    continue;
                }

                $section['links'] = $links;
                $sections[] = $section;
            }

            // Fall back to the canonical link for companies that never had an
            // Employees entry in their stored menu.
            if ($employees === null) {
                $employees = [
                    'title' => 'Employees',
                    'permission' => 'view user',
                    'link' => '/users',
                ];
            }

            $sections = $this->insertHrSection($sections, $employees);

            if (json_encode($sections) === json_encode($original)) {
                continue;
            }

            DB::table('settings')->where('id', $setting->id)->update([
                'asidebar' => json_encode($sections),
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
     * Whether an HR section already holds the Employees link.
     *
     * @param  array  $sections
     * @return bool
     */
    protected function hrEmployeesPresent(array $sections)
    {
        foreach ($sections as $section) {
            if (($section['name'] ?? null) !== 'HR') {
                continue;
            }

            foreach ($section['links'] ?? [] as $link) {
                if ($this->isEmployeesLink($link)) {
                    return true;
                }
            }
        }

        return false;
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
     * Insert the HR section containing the Employees link just before the
     * Users section (appending when Users is missing).
     *
     * @param  array  $sections
     * @param  array  $employees
     * @return array
     */
    protected function insertHrSection(array $sections, array $employees)
    {
        $hr = [
            'name' => 'HR',
            'icon' => 'bi bi-person-bounding-box',
            'links' => [$employees],
        ];

        foreach ($sections as $index => $section) {
            if (($section['name'] ?? null) === 'Users') {
                array_splice($sections, $index, 0, [$hr]);

                return $sections;
            }
        }

        $sections[] = $hr;

        return $sections;
    }
}
