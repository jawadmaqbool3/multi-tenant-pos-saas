<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddBulkPayrollToHrMenu extends Migration
{
    /**
     * Bulk payroll generation is HR business: the "Bulk Payroll" link (the
     * payroll run generator) is appended to the HR section of every persisted
     * `settings.asidebar` JSON. New companies are handled by
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

            $sections = [];
            $found = false;
            $baseUrl = $this->baseUrl($asidebar);

            foreach ($asidebar as $section) {
                if (($section['name'] ?? null) === 'HR' && isset($section['links']) && is_array($section['links'])) {
                    $found = true;
                    $hasBulk = false;
                    foreach ($section['links'] as $index => $link) {
                        if (($link['title'] ?? null) === 'Bulk Payroll') {
                            $hasBulk = true;
                            // Normalise a previously added relative link to this
                            // setting's host.
                            $section['links'][$index]['link'] = $baseUrl . '/payrolls/create';
                            break;
                        }
                    }
                    if (!$hasBulk) {
                        $section['links'][] = [
                            'title' => 'Bulk Payroll',
                            'permission' => 'view payroll',
                            'link' => $baseUrl . '/payrolls/create',
                        ];
                    }
                }

                $sections[] = $section;
            }

            // Only touch settings that already have an HR section.
            if (!$found) {
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
     * Derive the setting's own scheme+host from any absolute link it already
     * stores, so a new link is consistent (and keeps a custom host such as an
     * ngrok tunnel). Falls back to the app URL.
     *
     * @param  array  $sections
     * @return string
     */
    protected function baseUrl(array $sections)
    {
        foreach ($sections as $section) {
            foreach (($section['links'] ?? []) as $link) {
                $url = $link['link'] ?? '';
                $scheme = parse_url($url, PHP_URL_SCHEME);
                $host = parse_url($url, PHP_URL_HOST);

                if ($scheme && $host) {
                    $port = parse_url($url, PHP_URL_PORT);

                    return $scheme . '://' . $host . ($port ? ':' . $port : '');
                }
            }
        }

        return rtrim(url('/'), '/');
    }
}
