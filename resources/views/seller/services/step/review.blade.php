<form action="{{ route('seller.services.review') }}" method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-xl-6 col-lg-8 mx-auto">
                @csrf
                <div class="card col-md-12 mb-4">
                    <!-- Header -->
                    <div class="card-header">
                        <h4 class="card-header-title mb-0">Service information</h4>
                    </div>
                    <!-- End Header -->
                    <div class="card-body">
                        <input type="hidden" name="service_id" id="service_id" value="{{$post_id}}" >
                        <input type="hidden" name="step" id="name" value="{{$step}}" class="form-control">
                        @include('includes.validation-form')
                        <div class="mb-2">
                            <label for="name" class="w-100 mb-2">Name:</label>
                            <div>{{ $data->name }}</div>
                        </div>
                        <div class="mb-2">
                            <label for="desc" class="w-100 mb-2">Content:</label>
                            <div class="rounded p-3 border">
                            {!! $data->content !!}
                            </div>
                        </div>

                        <div class="mb-4 col-12">
                            <label for="category" class="w-100 mb-2">Thumb</label>
                            <div class="col-3">
                                <img class="w-100 shadow-1-strong rounded mb-4" src="/uploads/all/{{$data->uploads->file_name}}" alt="thumb" />
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="name" class="w-100 mb-2">Gallery:</label>
                            <div class="row">
                                @foreach ($data->galleries as $gallery)
                                    @if($gallery)
                                    <div class="col-3 float-left">
                                        <img class="w-100 shadow-1-strong rounded mb-4" src="/uploads/all/{{$gallery->file_name}}" alt="gallery" />
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="row justify-content-center justify-content-sm-between">
            <div class="col">
            <a type="button" class="btn btn-danger" href="{{route('seller.services.list')}}">Cancel</a>
            </div>
            <!-- End Col -->

            <div class="col-auto">
            <div class="d-flex flex-column gap-3">
                <!-- <button type="button" class="btn btn-light">Save Draft</button> -->
                <button type="submit" class="btn btn-primary">Publish</button>
                <a type="button" class="btn btn-light" href="{{"/seller/services/create/".($step-1)."/".$post_id}}">Back</a> 
            </div>
            </div>
            <!-- End Col -->
        </div>
        <!-- End Card -->
      </div>
    </form>
    </div>

@section('js')
    <script>
    </script>
@endsection
