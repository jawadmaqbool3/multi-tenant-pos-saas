<?php

namespace Database\Seeders;

use App\Support\DefaultAccounts;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoEmployeeSeeder extends Seeder
{
    /**
     * Flag the demo staff with payroll/attendance so a freshly seeded install
     * has records on the Employees and Attendance screens. Mirrors the
     * `2026_10_19_000011_seed_demo_employees` migration, which backfills
     * databases created before this seeder existed.
     *
     * @return void
     */
    public function run()
    {
        $employees = [
            'employee@gmail.com' => ['payroll' => 5000, 'attendance' => true],
            'sales@gmail.com' => ['payroll' => 4000, 'attendance' => true],
            'stock@gmail.com' => ['payroll' => 3500, 'attendance' => true],
            'accountant@gmail.com' => ['payroll' => 6000, 'attendance' => false],
            'hr@gmail.com' => ['payroll' => 7000, 'attendance' => true],
        ];

        foreach ($employees as $email => $flags) {
            $user = DB::table('users')->where('email', $email)->first();

            if (!$user) {
                continue;
            }

            DB::table('users')->where('id', $user->id)->update([
                'payroll_enabled' => $flags['payroll'] ? 1 : 0,
                'attendance_enabled' => $flags['attendance'] ? 1 : 0,
            ]);

            if (!$flags['payroll']) {
                continue;
            }

            DB::table('employee_personal_information')->updateOrInsert(
                ['employee_id' => $user->id],
                ['salary' => $flags['payroll']]
            );
        }

        $this->seedAttendanceHistory();
    }

    /**
     * Seed a week of clock in/out history for the attendance-enabled staff so
     * the report is not blank on first run.
     *
     * @return void
     */
    protected function seedAttendanceHistory()
    {
        if (DB::table('attendance_histories')->exists()) {
            return;
        }

        $staff = DB::table('users')
            ->where('attendance_enabled', 1)
            ->get(['id']);

        foreach ($staff as $employee) {
            foreach (range(0, 6) as $daysAgo) {
                $day = now()->subDays($daysAgo);

                if ($day->isWeekend()) {
                    continue;
                }

                $checkIn = $day->copy()->setTime(9, 0);
                $checkOut = $day->copy()->setTime(17, 0);

                DB::table('attendance_histories')->insert([
                    'user_id' => $employee->id,
                    'type' => 'clockin',
                    'time' => $checkIn,
                    'created_at' => $checkIn,
                    'updated_at' => $checkIn,
                ]);

                DB::table('attendance_histories')->insert([
                    'user_id' => $employee->id,
                    'type' => 'clockout',
                    'time' => $checkOut,
                    'created_at' => $checkOut,
                    'updated_at' => $checkOut,
                ]);
            }
        }
    }
}
