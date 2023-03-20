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
    new Tagify(document.querySelector('#input_tag'));






    $("#date_picker").flatpickr({
        enableTime: true,
        dateFormat: "Y-m-d H:i",
    });

    $('#status').change(function() {
        let option = $(this).find('option:selected');
        $('#status_sign').removeClass('bg-success').removeClass('bg-danger').removeClass('bg-warning').addClass(
            option.data('statusSign'));
        if ($(this).val() == 'scheduled') {
            $('.date-selector').removeClass('d-none');
        } else {
            $('.date-selector').addClass('d-none');
        }
    })


    $(document).on('click', '.file-upload-box', function(e) {
        if (e.target.className != 'fa fa-times' && e.target.className != 'btn-remove-image text-danger') {
            $(this).find('.file-selector')[0].click();
        }
    });

    $(document).on('change', '.file-selector', function(e) {
        let file = e.target.files[0];
        if (typeof(file) == 'undefined') {
            $(this).parents('.file-upload-box').remove();
            return;
        }
        var selector = $(this);
        let reader = new FileReader();
        reader.onload = (function(theFile) {
            return function(e) {
                if (typeof(selector.siblings('.image-box').html()) != "string") {
                    selector.parents('.images-container').append(`
                            <div class="col-xs-2 col-md-3 file-upload-box mt-1 position-relative">
                            
                            <div class="text-white text-center upload-box _image-box">
                                <i class="fa fa-upload fs-1 mt-5"></i>
                            </div>
                            <input type="file" name="images[]" class="file-selector" style="display:none" id="">
                        </div>`);
                    selector.siblings('.upload-box').removeClass('upload-box').addClass('image-box')
                        .html(`
                        <button class="btn-remove-image text-danger"><i class="fa fa-times"></i></button>
                        <img src="${e.target.result}" alt="">
                            `);
                } else {
                    selector.siblings('.image-box').html(`
                        <button class="btn-remove-image text-danger"><i class="fa fa-times"></i></button>
                        <img src="${e.target.result}" alt="">
                            `);
                }




            };
        })(file);

        // Read in the image file as a data URL.
        reader.readAsDataURL(file);

    });


    $(document).on('click', '.btn-remove-image', function() {
        $(this).parents('.file-upload-box').remove();
    });
</script>
