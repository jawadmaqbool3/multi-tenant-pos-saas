<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RestrictBulkPayrollMenuToPayrollCreators extends Migration
{
    /**
     * The "Bulk Payroll" menu link opens payrolls/create, which only users with
     * `create payroll` may use. It previously required `view payroll`, which
     * leaked the HR bulk-run surface into the accountant (pay-only) sidebar.
     *
     * Update every stored tenant/platform sidebar setting accordingly. Data
     * migration: direct DB access is intentional.
     *
     * @return void
     */
    public function up()
    {
        foreach (DB::table('settings')->get() as $setting) {
            if (!$setting->asidebar) {
                continue;
            }

            $asidebar = json_decode($setting->asidebar, true);

            if (!is_array($asidebar)) {
                continue;
            }

            $changed = false;

            foreach ($asidebar as $i => $category) {
                if (!isset($category['links']) || !is_array($category['links'])) {
                    continue;
                }

                foreach ($category['links'] as $j => $link) {
                    if (($link['title'] ?? null) === 'Bulk Payroll'
                        && ($link['permission'] ?? null) === 'view payroll') {
                        $asidebar[$i]['links'][$j]['permission'] = 'create payroll';
                        $changed = true;
                    }
                }
            }

            if ($changed) {
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
            if (!$setting->asidebar) {
                continue;
            }

            $asidebar = json_decode($setting->asidebar, true);

            if (!is_array($asidebar)) {
                continue;
            }

            $changed = false;

            foreach ($asidebar as $i => $category) {
                if (!isset($category['links']) || !is_array($category['links'])) {
                    continue;
                }

                foreach ($category['links'] as $j => $link) {
                    if (($link['title'] ?? null) === 'Bulk Payroll'
                        && ($link['permission'] ?? null) === 'create payroll') {
                        $asidebar[$i]['links'][$j]['permission'] = 'view payroll';
                        $changed = true;
                    }
                }
            }

            if ($changed) {
                DB::table('settings')->where('id', $setting->id)->update([
                    'asidebar' => json_encode($asidebar),
                ]);
            }
        }
    }
}
