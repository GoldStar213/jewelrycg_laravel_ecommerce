<x-app-layout page-title="Create a service | Jewelry CG">
    <meta name="_token" content="{{csrf_token()}}" />
    <link rel="stylesheet" href="{{ asset('dropzone/css/dropzone.css') }}">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-9">
        <div class="container">
            <style>
                .multi-steps > li.is-active:before, .multi-steps > li.is-active ~ li:before {
                    content: counter(stepNum);
                    font-family: inherit;
                    font-weight: 700;
                }
                .multi-steps > li.is-active:after, .multi-steps > li.is-active ~ li:after {
                    background-color: #ededed;
                }
                .multi-steps {
                    display: table;
                    table-layout: fixed;
                    width: 100%;
                }
                .multi-steps > li {
                    counter-increment: stepNum;
                    text-align: center;
                    display: table-cell;
                    position: relative;
                    color: green;
                }
                .multi-steps > li:before {
                    content: '\f00c';
                    content: '\2713;';
                    content: '\10003';
                    content: '\10004';
                    content: '\2713';
                    display: block;
                    margin: 0 auto 4px;
                    background-color: #fff;
                    width: 36px;
                    height: 36px;
                    line-height: 32px;
                    text-align: center;
                    font-weight: bold;
                    border-width: 2px;
                    border-style: solid;
                    border-color: green;
                    border-radius: 50%;
                }
                .multi-steps > li:after {
                    content: '';
                    height: 2px;
                    width: 100%;
                    background-color: green;
                    position: absolute;
                    top: 16px;
                    left: 50%;
                    z-index: -1;
                }
                .multi-steps > li:last-child:after {
                    display: none;
                }
                .multi-steps > li.is-active:before {
                    background-color: #fff;
                    border-color: green;
                }
                .multi-steps > li.is-active ~ li {
                    color: #808080;
                }
                .multi-steps > li.is-active ~ li:before {
                    background-color: #ededed;
                    border-color: #ededed;
                }

            </style>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <ul class="list-unstyled multi-steps mb-0">
                                <li class="{{$step == 0 ? 'is-active': ''}}">Overview</li>
                                <li class="{{$step == 1 ? 'is-active': ''}}">Pricing</li>
                                <li class="{{$step == 2 ? 'is-active': ''}}">Requirement</li>
                                <li class="{{$step == 3 ? 'is-active': ''}}">Gallery</li>
                                <li class="{{$step == 4 ? 'is-active': ''}}">Review</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            @if($step == 0)
                @include('seller.services.step.overview')
            @elseif($step == 1)
                @include('seller.services.step.pricing')
            @elseif($step == 2)
                @include('seller.services.step.requirement')
            @elseif($step == 3)
                @include('seller.services.step.gallery')
            @elseif($step == 4)
                @include('seller.services.step.review')
            @endif

            @section('js')
                <script>
                    $(document).ready(function() {
                        $('#desc').trumbowyg();
                        // $('#meta_description').trumbowyg();
                    })
                    $(".imgAdd").click(function() {
                        $(this).closest(".row").find('.imgAdd').before(
                            '<div class="col-sm-2 imgUp"><div class="imagePreview"></div><label class="btn btn-primary">Upload<input type="file" class="uploadFile img" value="Upload Photo" style="width:0px;height:0px;overflow:hidden;"></label><i class="fa fa-times del"></i></div>'
                        );
                    });
                    $(document).on("click", "i.del", function() {
                        $(this).parent().remove();
                    });
                    $(function() {
                        $(document).on("change", ".uploadFile", function() {
                            var uploadFile = $(this);
                            var files = !!this.files ? this.files : [];
                            if (!files.length || !window.FileReader)
                                return; // no file selected, or no FileReader support

                            if (/^image/.test(files[0].type)) { // only image file
                                var reader = new FileReader(); // instance of the FileReader
                                reader.readAsDataURL(files[0]); // read the local file

                                reader.onloadend = function() { // set image data as background of div
                                    //alert(uploadFile.closest(".upimage").find('.imagePreview').length);
                                    uploadFile.closest(".imgUp").find('.imagePreview').css("background-image",
                                        "url(" + this.result + ")");
                                }
                            }

                        });

                        $('.select2').select2({
                            tags: true,
                            maximumSelectionLength: 10,
                            tokenSeparators: [','],
                            placeholder: "Select or type keywords",
                        })
                    });



                </script>
            @endsection
        </div>
    </div>
</x-app-layout>
