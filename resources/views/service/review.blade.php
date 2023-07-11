<x-app-layout page-title="Leave review to Order #{{$order->order_id}} | Jewelry CG">
  <div class="container">
    <div class="col-lg-8 col-md-10 py-8 mx-auto checkout-wrap">
      @if (session('success'))
        <div class="alert alert-success mt-3 text-center">{{session('success')}}</div>
      @endif
      @if (session('error'))
        <div class="alert alert-danger mt-3 text-center">{{session('error')}}</div>
      @endif
      <form action="{{ route('services.review.post') }}" method="post" enctype="multipart/form-data">
        <div class="row">
          <div class="col-md-12">
            @csrf
            <div class="card col-md-12 mb-4">
              <!-- Header -->
              <div class="card-header">
                <h4 class="card-header-title mb-0">Leave review to Order #{{$order->order_id}}</h4>
                <input type="hidden" name="order_id" value="{{ $order->id }}">
              </div>
              <!-- End Header -->
              <div class="card-body">
                @include('includes.validation-form')
                <input type="hidden" value="{{ $order->attachment_featured }}" name="review_attachement_id">
                @if (Auth::user()->role != 2)
                  @if($order->attachment_featured)
                    <div class="w-100 mb-4">
                      <label class="w-100 mb-2 fw-600">
                        <input type="checkbox" name="exampleCheckbox" id="exampleCheckbox" checked>
                        Show Image Of Delivery?
                      </label>
                      <img src="{{ $order->service->uploads->getImageOptimizedFullName(150) }}" class="w-200px border" id="delivery_img">
                    </div>
                  @endif
                @endif
                <div class="rate pb-3">
                  @for ($i = 5; $i > 0; $i--)
                    <input type="radio" id="star{!! $i !!}" class="rate" name="rating" value="{!! $i !!}"/>
                    <label for="star{!! $i !!}" class="d-flex relative">
                      <img src="/assets/img/star_blue.png" width="25" class="label-check absolute d-none"/>
                      <img src="/assets/img/star_gray.png" width="25" class="label-not-check"/>
                    </label>
                  @endfor
                </div>
                <div class="mb-4 col-12">
                  <label for="method" class="w-100 mb-2">Review comment</label>
                  <textarea name="review" class="form-control"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Save Review</button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
  <script type="text/javascript">
    $(function() {
      $('#exampleCheckbox').change((function() {
        $(this).is(':checked') ? $('#delivery_img').removeClass('d-none') : $('#delivery_img').addClass('d-none')
      }));
    })
  </script>
</x-app-layout>
