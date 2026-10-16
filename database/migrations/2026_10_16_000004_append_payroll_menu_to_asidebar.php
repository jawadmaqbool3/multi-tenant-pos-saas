<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AppendPayrollMenuToAsidebar extends Migration
{
    /**
     * Idempotently append the Payroll section to every company's persisted
     * `settings.asidebar` JSON. Skips a setting that already has a Payroll
     * section or any link pointing at a payroll route.
     *
     * @return void
     */
    public function up()
    {
        foreach (DB::table('settings')->get() as $setting) {
            $asidebar = json_decode($setting->asidebar, true);

            if (!is_array($asidebar) || $this->alreadyHasPayroll($asidebar)) {
                continue;
            }

            $asidebar[] = $this->payrollSection();

            DB::table('settings')->where('id', $setting->id)->update([
                'asidebar' => json_encode($asidebar),
            ]);
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

            $filtered = array_values(array_filter($asidebar, function ($section) {
                return ($section['name'] ?? null) !== 'Payroll';
            }));

            if (count($filtered) !== count($asidebar)) {
                DB::table('settings')->where('id', $setting->id)->update([
                    'asidebar' => json_encode($filtered),
                ]);
            }
        }
    }

    /**
     * @param  array  $asidebar
     * @return bool
     */
    protected function alreadyHasPayroll(array $asidebar)
    {
        foreach ($asidebar as $section) {
            if (($section['name'] ?? null) === 'Payroll') {
                return true;
            }

            foreach (($section['links'] ?? []) as $link) {
                $path = rtrim($link['link'] ?? '', '/');
                if (preg_match('#/(payrolls|allowances|deductions)$#', $path)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return array
     */
    protected function payrollSection()
    {
        return [
            'name' => 'Payroll',
            'icon' => 'bi bi-cash-stack',
            'links' => [
                [
                    'title' => 'Payroll Runs',
                    'permission' => 'view payroll',
                    'link' => url('payrolls'),
                ],
                [
                    'title' => 'Allowances',
                    'permission' => 'view allowance',
                    'link' => url('allowances'),
                ],
                [
                    'title' => 'Deductions',
                    'permission' => 'view deduction',
                    'link' => url('deductions'),
                ],
            ],
        ];
    }
}