<table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="attendance_table">
    <thead>
        <tr class="border-0">
            <th class="fw-bold p-0">SR#</th>
            <th class="fw-bold p-0 min-w-150px text-start">Date</th>
            <th class="fw-bold p-0 min-w-150px text-start">Clock In</th>
            <th class="fw-bold p-0 min-w-150px text-start">Clock Out</th>
        </tr>
    </thead>
    <tbody>
        @php
            $day = Carbon\Carbon::create($month . '-01');
            
            if ($month == Carbon\Carbon::now()->format('Y-m')) {
                $lastDay = Carbon\Carbon::now()->format('d');
            } else {
                $lastDay = Carbon\Carbon::create($month . '-01')
                    ->endOfMonth()
                    ->format('d');
            }
        @endphp
        @for ($i = 1; $i <= $lastDay; $i++)
            @php
                $clockin = $user->clockin($day->format('Y-m-d'));
                $clockout = $user->clockout($day->format('Y-m-d'));
            @endphp
            <tr class="@if ($clockin == false) bg-light-danger @endif">
                <td class="">{{ $i }}</td>
                <td class="">{{ $day->format('d M, Y') }}</td>
                @if ($clockin == false)
                    <td class="" colspan="2">Absent</td>
                @else
                    <td class="">
                        {{ @$clockin->time ? Carbon\Carbon::create($clockin->time)->format('h:i A') : '' }}
                    </td>
                    <td class="">
                        {{ @$clockout->time ? Carbon\Carbon::create($clockout->time)->format('h:i A') : '' }}
                    </td>
                @endif

            </tr>
            @php
                $day->addDay();
            @endphp
        @endfor
    </tbody>
</table>
