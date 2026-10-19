<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RenamePayrollMenuToAccountant extends Migration
{
    /**
     * Payroll runs, allowances and deductions are the accountant's domain, so
     * the "Payroll" sidebar section is relabelled "Accountant". Existing
     * companies are rewritten here; new companies are handled by
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
            $payrollLinks = [];

            foreach ($asidebar as $section) {
                $name = $section['name'] ?? null;

                if ($name === 'Payroll') {
                    // Collect its links and drop the old section; they are
                    // merged into (or become) the Accountant section below.
                    $payrollLinks = array_merge($payrollLinks, $section['links'] ?? []);
                    continue;
                }

                if ($name === 'Accountant') {
                    // An Accountant section already exists: keep it and merge
                    // any Payroll links into it.
                    $section['links'] = array_merge($section['links'] ?? [], $payrollLinks);
                    $payrollLinks = [];
                }

                $sections[] = $section;
            }

            if (!empty($payrollLinks)) {
                $sections[] = [
                    'name' => 'Accountant',
                    'icon' => 'bi bi-cash-stack',
                    'links' => $payrollLinks,
                ];
            }

            if (json_encode($sections) === json_encode($asidebar)) {
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
}
