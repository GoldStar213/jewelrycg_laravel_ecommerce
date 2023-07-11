<form action="{{ route('seller.services.gallery') }}" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-xl-6 col-lg-8 mx-auto">
            @csrf
            <div class="card col-md-12 mb-4">
                <div class="card-body">
                    <div class="mb-4">
                        <label for="name" class="w-100 mb-2 fw-700">Highlight your service with a creative thumbnail and gallery of your work</label>
                        <p>Present a range of your work to motivate buyers to opt for your Service</p>
                    </div>
                    <input type="hidden" name="service_id" id="service_id" value="{{$post_id}}" >
                    <input type="hidden" name="step" id="name" value="{{$step}}" class="form-control">
                    <input type="hidden" id="thumb" name="thumb" value="{{ null !== old('thumb') ? old('thumb') : (isset($data->thumbnail) ? $data->thumbnail : "") }}">
                    <input type="hidden" id="gallery" name="gallery" value="{{ null !== old('gallery') ? old('gallery') : (isset($data->gallery) ? $data->gallery : "") }}">
                    @include('includes.validation-form')
                    <div class="card mb-3 mb-4">
                        <div class="card-body">
                            <div class="mb-4">
                                <label for="name" class="w-100 mb-2 fw-700">Thumbnail</label>
                                <p>Select a thumbnail for your service 4:3 aspect ratio. Min Width 600px, Min Height 400px.</p>
                            </div>
                            <div class="alert alert-danger" role="alert" style="display: none;" id="imageErrorThumbnail">
                                Thumbnail image resolution is too low. Please upload an image with a resolution of at least 800x600.
                            </div>
                            <div id="thumbnail" class="thumbnail">
                                <div class="dropzone" id="thumbnail_dropzone"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="mb-4">
                                <label for="name" class="w-100 mb-2 fw-700">Gallery</label>
                                <p>Attract the attention of potential clients by showcasing visual examples of your services.</p>
                            </div>
                            <div class="alert alert-danger" role="alert" style="display: none;" id="imageErrorGallary">
                                Gallary image resolution is too low. Please upload an image with a resolution of at least 800x600.
                            </div>
                            <div id="gallery_container" class="gallery_container">
                                <div class="dropzone" id="gallery_dropzone">
                                </div>
                            </div>
                        </div>
                        <!-- Body -->
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 col-lg-8 mx-auto">
            <div class="row justify-content-center justify-content-sm-between">
                <div class="col">
                <a class="btn btn-danger" href="{{route('seller.services.list')}}">Cancel</a>
                </div>
                <!-- End Col -->

                <div class="col-auto">
                <div class="d-flex flex-column gap-3">
                    <button type="submit" class="btn btn-primary">Save & Continue</button>
                    <a class="btn btn-light" href="{{"/seller/services/create/".($step-1)."/".$post_id}}">Back</a>
                </div>
                </div>
                <!-- End Col -->
            </div>
        </div>
    </div>
    </div>
</form>

<div id='ajaxCalls'>
</div>

@section('js')
<script src="{{ asset('dropzone/js/dropzone.js') }}"></script>
<script>
    var galleries = {!! json_encode($data->galleries) !!}
    var thumbnail = {!! json_encode($data->uploads) !!}
    var currentFile = null;
    Dropzone.autoDiscover = false;
    var thumbnailDropzone, galleryDropzone;
    $(document).ready(function() {

        $("#thumbnail_dropzone").dropzone({
            method:'post',
            url: "{{ route('seller.file.thumb') }}",
            dictDefaultMessage: "Select photos",
            paramName: "file",
            maxFilesize: 2,
            maxFiles: 1,
            clickable: true,
            addRemoveLinks: true,
            acceptedFiles: "image/*",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            },
            init: function () {
                thumbnailDropzone = this;

                if(thumbnail.id) {
                    var mockFile = { name: thumbnail.file_original_name, size: thumbnail.file_size };

                    thumbnailDropzone.emit("addedfile", mockFile);
                    thumbnailDropzone.emit("thumbnail", mockFile, `{{asset("/uploads/all")}}/${thumbnail.file_name}`);
                    thumbnailDropzone.emit("complete", mockFile);
                }

                const that = this;
                
                this.on("thumbnail", function(file) {
                    if (file.width < 800 || file.height < 600) {
                        $('#imageErrorThumbnail').css('display', 'block');
                        that.removeFile(file);
                    }
                })

            },
            success: (file, response) => {
                var inputs = $("#inputs");
                var last = $("#thumb");

                last.val(response.id)
                thumbnail = response;

            },
            removedfile: function(file) {
                $.ajax({
                    url: `/seller/file/destroy/${thumbnail.id}`,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    },
                    success: function(result) {
                        var last = $("#thumb");
                        last.val("")
                        $(file.previewElement).remove();
                    },
                    error: function(error) {
                        return false;
                    }
                });
            }
        })



        $("#gallery_dropzone").dropzone({
            method:'post',
            url: "{{ route('seller.file.image') }}",
            dictDefaultMessage: "Select thumbnail",
            paramName: "file",
            maxFilesize: 5,
            clickable: true,
            addRemoveLinks: true,
            acceptedFiles: "image/*",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            },
            init: function () {
                galleryDropzone = this;

                for (const gallery of galleries) {
                    if(!gallery) {
                        continue;
                    }

                    var mockFile = { name: gallery.file_original_name, size: gallery.file_size };

                    galleryDropzone.emit("addedfile", mockFile);
                    galleryDropzone.emit("thumbnail", mockFile, `{{asset("/uploads/all")}}/${gallery.file_name}`);
                    galleryDropzone.emit("complete", mockFile);
                }

                const that = this;
                
                this.on("thumbnail", function(file) {
                    if (file.width < 800 || file.height < 600) {
                        $('#imageErrorGallary').css('display', 'block');
                        that.removeFile(file);
                    }
                })
            },
            success: (file, response) => {
                var inputs = $("#inputs");
                var last = $("#gallery");
                var lastValue = last.val().split(',');

                lastValue.push(response.id);
                galleries.push(response);
                last.val(lastValue);
                console.log(galleries);
            },
            removedfile: function(file) {
                for(var i=0;i<galleries.length;++i){
                    if(!galleries[i]) {
                        continue;
                    }
                    console.error(file, galleries[i]);
                    if(file.name.includes(galleries[i].file_original_name)) {
                        $.ajax({
                            url: `/seller/file/destroy/${galleries[i].id}`,
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            },
                            success: function(result) {
                                var last = $("#gallery");
                                var lastValue = last.val().split(',');
                                lastValue.splice(i, 1);
                                last.val(lastValue);
                                $(file.previewElement).remove();
                                galleries.splice(i, 1)
                            },
                            error: function(error) {
                                return false;
                            }
                        });
                        break;
                    }
                }
            }
        })
    });
</script>
@endsection
