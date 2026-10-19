<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ReorganizePayrollMenuSections extends Migration
{
    /**
     * Payroll Runs moves into the Bank (accounting) section while Allowances
     * and Deductions move into HR, retiring the interim "Accountant" section.
     * Existing company/platform settings are rewritten here; new companies are
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

            $payrollRun = null;
            $allowanceLines = [];
            $sections = [];

            // First pass: pull the payroll links out of the Accountant section.
            foreach ($asidebar as $section) {
                if (($section['name'] ?? null) === 'Accountant') {
                    foreach ($section['links'] ?? [] as $link) {
                        $title = $link['title'] ?? null;
                        if ($title === 'Payroll Runs') {
                            $payrollRun = $link;
                        } elseif (in_array($title, ['Allowances', 'Deductions'], true)) {
                            $allowanceLines[] = $link;
                        }
                    }
                    continue;
                }

                $sections[] = $section;
            }

            // Second pass: fold the harvested links into Bank / HR. If more than
            // one section shares a target name, the first is used and the rest
            // are merged into it.
            $this->mergeLinkInto($sections, 'Bank', $payrollRun, 'bi bi-bank');
            $this->mergeLinksInto($sections, 'HR', $allowanceLines, 'bi bi-person-bounding-box');

            // Third pass: collapse any duplicate-named sections.
            $sections = $this->collapseDuplicates($sections);

            if (json_encode($sections) === json_encode($asidebar)) {
                continue;
            }

            DB::table('settings')->where('id', $setting->id)->update([
                'asidebar' => json_encode($sections),
            ]);
        }
    }

    /**
     * Add a single link to the named section (creating it when absent).
     *
     * @param  array  $sections
     * @param  string  $name
     * @param  array|null  $link
     * @param  string  $icon
     * @return void
     */
    protected function mergeLinkInto(array &$sections, string $name, ?array $link, string $icon): void
    {
        if (!$link) {
            return;
        }

        $this->mergeLinksInto($sections, $name, [$link], $icon);
    }

    /**
     * Add links to the named section (creating it when absent), skipping
     * titles already present anywhere in that section.
     *
     * @param  array  $sections
     * @param  string  $name
     * @param  array  $links
     * @param  string  $icon
     * @return void
     */
    protected function mergeLinksInto(array &$sections, string $name, array $links, string $icon): void
    {
        if (empty($links)) {
            return;
        }

        $index = null;

        foreach ($sections as $i => $section) {
            if (($section['name'] ?? null) === $name) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            $sections[] = [
                'name' => $name,
                'icon' => $icon,
                'links' => $links,
            ];
            return;
        }

        $titles = array_map(fn ($l) => $l['title'] ?? null, $sections[$index]['links'] ?? []);

        foreach ($links as $link) {
            if (in_array($link['title'] ?? null, $titles, true)) {
                continue;
            }
            $sections[$index]['links'][] = $link;
            $titles[] = $link['title'] ?? null;
        }
    }

    /**
     * Merge same-named sections into the first occurrence.
     *
     * @param  array  $sections
     * @return array
     */
    protected function collapseDuplicates(array $sections): array
    {
        $result = [];
        $positions = [];

        foreach ($sections as $section) {
            $name = $section['name'] ?? null;

            if ($name !== null && isset($positions[$name])) {
                $pos = $positions[$name];
                $titles = array_map(fn ($l) => $l['title'] ?? null, $result[$pos]['links'] ?? []);
                foreach ($section['links'] ?? [] as $link) {
                    if (!in_array($link['title'] ?? null, $titles, true)) {
                        $result[$pos]['links'][] = $link;
                        $titles[] = $link['title'] ?? null;
                    }
                }
                continue;
            }

            $positions[$name] = count($result);
            $result[] = $section;
        }

        return $result;
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
