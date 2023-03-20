<script>
    let options = {
        modules: {
            toolbar: [
                [{
                    header: [1, 2, false]
                }],
                ['bold', 'italic', 'underline'],
                ['image', 'code-block']
            ]
        },
        placeholder: 'Type your text here...',
        theme: 'snow' // or 'bubble'
    }
    new Quill('#quill_1', options);
    new Quill('#quill_2', options);




    $("#date_picker").flatpickr({
        enableTime: true,
        dateFormat: "Y-m-d H:i",
    });

    $('#status').change(function(){
        let option = $(this).find('option:selected');
        $('#status_sign').removeClass('bg-success').removeClass('bg-danger').removeClass('bg-warning').addClass(option.data('statusSign'));
        if($(this).val() == 'scheduled')
        {
            $('.date-selector').removeClass('d-none');
        }
        else
        {
            $('.date-selector').addClass('d-none');
        }
    })  
</script>
