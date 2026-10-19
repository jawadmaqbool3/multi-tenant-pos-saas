<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MoveEmployeeAttendanceMenuToHr extends Migration
{
    /**
     * The Attendance report is HR business, not a general report, so the
     * "Employee Attendance" link moves out of the Reports section into the HR
     * section. Rewrite every company's persisted `settings.asidebar` JSON: pull
     * the link out of whichever section currently holds it and append it to
     * HR, creating that section just before Users when necessary. Existing
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

            // Idempotent: already sitting in the HR section.
            if ($this->hrAttendancePresent($asidebar)) {
                continue;
            }

            $original = $asidebar;
            $attendance = null;
            $sections = [];

            foreach ($asidebar as $section) {
                if (!isset($section['links']) || !is_array($section['links'])) {
                    $sections[] = $section;
                    continue;
                }

                $links = [];

                foreach ($section['links'] as $link) {
                    if ($this->isEmployeeAttendanceLink($link)) {
                        $attendance = $attendance ?: $link;
                        continue;
                    }

                    $links[] = $link;
                }

                if (empty($links)) {
                    // Drop a section that only held the Attendance link.
                    continue;
                }

                $section['links'] = $links;
                $sections[] = $section;
            }

            // Fall back to the canonical link for companies that never had an
            // Employee Attendance entry in their stored menu.
            if ($attendance === null) {
                $attendance = [
                    'title' => 'Employee Attendance',
                    'permission' => 'view employee attendance report',
                    'link' => '/attendance',
                ];
            }

            $sections = $this->addToHr($sections, $attendance);

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
     * Whether the HR section already holds the Employee Attendance link.
     *
     * @param  array  $sections
     * @return bool
     */
    protected function hrAttendancePresent(array $sections)
    {
        foreach ($sections as $section) {
            if (($section['name'] ?? null) !== 'HR') {
                continue;
            }

            foreach ($section['links'] ?? [] as $link) {
                if ($this->isEmployeeAttendanceLink($link)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * A link is the Employee Attendance entry when its title is "Employee
     * Attendance" or its path is exactly "/attendance" (the My Attendance link
     * points at "/attendance/me" and must not match).
     *
     * @param  array  $link
     * @return bool
     */
    protected function isEmployeeAttendanceLink($link)
    {
        if (($link['title'] ?? null) === 'Employee Attendance') {
            return true;
        }

        if (!isset($link['link'])) {
            return false;
        }

        $path = parse_url($link['link'], PHP_URL_PATH);

        if ($path === null || $path === false) {
            $path = $link['link'];
        }

        return rtrim($path, '/') === '/attendance';
    }

    /**
     * Append the Attendance link to the HR section, creating that section just
     * before Users when it is missing.
     *
     * @param  array  $sections
     * @param  array  $attendance
     * @return array
     */
    protected function addToHr(array $sections, array $attendance)
    {
        foreach ($sections as $index => $section) {
            if (($section['name'] ?? null) === 'HR') {
                $sections[$index]['links'][] = $attendance;

                return $sections;
            }
        }

        $hr = [
            'name' => 'HR',
            'icon' => 'bi bi-person-bounding-box',
            'links' => [$attendance],
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
