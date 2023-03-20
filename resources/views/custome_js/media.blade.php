<script>
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
                            <div class="col-sm-6 col-md-4 file-upload-box mt-1 position-relative">
                            
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
