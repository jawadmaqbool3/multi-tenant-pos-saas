<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AppendLedgerBalanceSheetMenuToAsidebar extends Migration
{
    /**
     * Idempotently append the Ledger and Balance Sheet links to the Bank
     * section of every company's persisted `settings.asidebar` JSON. Existing
     * links (matched by path) are left untouched and the Bank section is
     * created when it does not exist.
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

            if ($this->appendReportLinks($asidebar)) {
                DB::table('settings')->where('id', $setting->id)->update([
                    'asidebar' => json_encode($asidebar),
                ]);
            }
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        foreach (DB::table('settings')->get() as $setting) {
            $asidebar = json_decode($setting->asidebar, true);

            if (!is_array($asidebar)) {
                continue;
            }

            $changed = false;

            foreach ($asidebar as &$section) {
                if (!isset($section['links']) || !is_array($section['links'])) {
                    continue;
                }

                $links = [];
                foreach ($section['links'] as $link) {
                    $path = rtrim((string) parse_url($link['link'] ?? '', PHP_URL_PATH), '/');
                    if (in_array($path, ['/reports/ledger', '/reports/balance-sheet'], true)) {
                        $changed = true;
                        continue;
                    }
                    $links[] = $link;
                }

                $section['links'] = array_values($links);
            }
            unset($section);

            if ($changed) {
                DB::table('settings')->where('id', $setting->id)->update([
                    'asidebar' => json_encode($asidebar),
                ]);
            }
        }
    }

    /**
     * @param  array  $asidebar
     * @return bool  true when $asidebar was modified
     */
    protected function appendReportLinks(array &$asidebar)
    {
        $bankIndex = null;

        foreach ($asidebar as $index => $section) {
            if (($section['name'] ?? null) === 'Bank') {
                $bankIndex = $index;
                break;
            }
        }

        if ($bankIndex === null) {
            $asidebar[] = [
                'name' => 'Bank',
                'icon' => 'bi bi-bank',
                'links' => [],
            ];
            $bankIndex = array_key_last($asidebar);
        }

        $existing = [];
        foreach (($asidebar[$bankIndex]['links'] ?? []) as $link) {
            $path = rtrim((string) parse_url($link['link'] ?? '', PHP_URL_PATH), '/');
            if ($path !== '') {
                $existing[] = $path;
            }
        }

        $wanted = [
            '/reports/ledger' => ['title' => 'Ledger', 'permission' => 'view ledger'],
            '/reports/balance-sheet' => ['title' => 'Balance Sheet', 'permission' => 'view balance sheet'],
        ];

        $added = false;

        foreach ($wanted as $path => $meta) {
            if (in_array($path, $existing, true)) {
                continue;
            }

            $asidebar[$bankIndex]['links'][] = [
                'title' => $meta['title'],
                'permission' => $meta['permission'],
                'link' => $path,
            ];
            $added = true;
        }

        return $added;
    }
}
