        function getAttendance(id, month) {
        $.ajax({
        url: "{{ route('attendance.index') }}",
        type: "GET",
        data: {
        id: id,
        month: month
        },
        success: function(response) {
        if (response.success) {
        $('#attendance_report_wrapper').html(response.view);
        {{-- $('#attendance_table').dataTable(); --}}
        }
        },
        error: function(response) {
        console.error('failed to fetch attendance history');
        },
        complete: function() {}
        });
        }
