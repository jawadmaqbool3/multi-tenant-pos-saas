        <div class="card card-flush py-4 mt-5">
            <div class="card-header">
                <div class="card-title">
                    <h2>{{ $title }}</h2>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="fv-row mb-2">
                    <div class="col-md-12">

                        <div class="form-group">
                            <div class="row images-container">
                                @if ($images)
                                    @foreach ($images as $image)
                                        <div class="col-sm-6 col-md-4 file-upload-box mt-1 position-relative ">

                                            <div class="text-white text-center image-box _image-box">
                                                <button class="btn-remove-image text-danger"><i
                                                        class="fa fa-times"></i></button>
                                                <img src="{{ asset($directory) . '/' . $image }}"
                                                    alt="">
                                                <input type="text" name="old_images[]" value="{{ $image }}"
                                                    class="file-selector" style="display:none" id="">
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                                <div class="col-sm-6 col-md-4 file-upload-box mt-1  position-relative">
                                    <div class="text-white text-center upload-box _image-box">
                                        <i class="fa fa-upload fs-1 mt-5"></i>
                                    </div>
                                    <input type="file" name="images[]" class="file-selector" style="display:none"
                                        id="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-muted fs-7">{{ $tagLine }}</div>
            </div>
        </div>
