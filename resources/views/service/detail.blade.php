<x-app-layout page-title="{{$service->name}} | Jewelry CG">

    <div class="service-container col-lg-8 col-md-10 py-8 mx-auto">
        <div class="container">
            <div class="col-xl-10 mx-auto">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="mb-3 py-3">
                            <div class="d-flex align-items-center">
                                <a href="/u/{{ $service->postauthor->username }}">
                                    <img id="fileManagerPreview"
                                        src="{{ $service->postauthor->uploads->getImageOptimizedFullName(200,200) }}"
                                        class="product-seller border rounded h-60px mr-5px">
                                </a>
                                <div class="product-details-title px-2">
                                    <div class="fs-20 fw-600">{{$service->name}}</div>
                                    <div class="link">
                                        <span><a href="/u/{{ $service->postauthor->username }}" class="text-black fw-600">{{ '@'.$service->postauthor->username }}</a></span>
                                        @if ($review->count() > 0)
                                        <span>| <i class="bi bi-star-fill fs-20 text-blue"></i> {{ $average_rating }}</span>
                                        <span>({{ $review_count }})</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="service-images">
                            <div class="row">
                                <div class="col-lg-12 mb-4">
                                    <div class="slide card p-1 m-0">
                                        <img src="{{$service->galleries[0]->getImageOptimizedFullName(800,450) }}" class="rounded w-100">
                                    </div>
                                </div>

                                @for ($i = 1; $i < count($service->galleries); ++$i)
                                    <div class="col-lg-3 mb-4">
                                        <div class="card p-1 m-0">
                                            <img src="{{$service->galleries[$i]->getImageOptimizedFullName(800,450) }}" class="rounded w-100">
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-6 about-service">
                                    <div class="section-header-title mb-3 text-uppercase fw-700 border p-3 card rounded">About This Service</div>
                                    <div>{!! $service->content !!}</div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="service-packages-card card p-3">
                                    @if ($service->option_custompricing == 1)
                                        <div class="alert alert-info" role="alert">Please message the seller for a personalized quote as they only offer custom pricing for this service.</div>
                                        <a class="btn btn-lg btn-primary" href="{{route('create_chat_room',['conversation_id'=>$service->seller->user->username])}}">Message Me</a>
                                    @else
                                        <ul class="nav nav-pills nav-fill mb-3 service-packages-pill rounded p-2" id="pills-tab" role="tablist">
                                            @foreach ($service->packages as $k => $package)
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link {{ $k == 0 ? 'active' : '' }}"
                                                            id="pills-{{ $package->id }}-tab"
                                                            data-bs-toggle="pill"
                                                            data-bs-target="#pills-{{ $package->id }}" type="button" role="tab"
                                                            aria-controls="pills-{{ $package->id }}"
                                                            aria-selected="true">{{ $package->name }}
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="tab-content" id="pills-tabContent">
                                            @foreach ($service->packages as $k => $package)
                                                <div class="tab-pane fade {{ $k == 0 ? 'show active' : '' }}"
                                                    id="pills-{{ $package->id }}" role="tabpanel"
                                                    aria-labelledby="pills-{{ $package->id }}-tab">
                                                    <h3>${{number_format($package->price / 100, 2)}}</h3>
                                                    <h4>{{$package->name}}</h4>
                                                    <p>{{$package->description}}</p>
                                                    <p>{{$package->delivery_time}} Day Delivery</p>
                                                    <p>{{$package->revisions}} Revisions</p>
                                                    <a href="/services/checkout/{{$package->id}}" type="button"
                                                    class="btn btn-primary w-100">Purchase</a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>                         
                            </div>
                        </div><!-- end row-->

<div class="row">
    <div class="section-header">
        <div class="section-header-title mb-3 text-uppercase fw-700 border p-3 card rounded">About this seller</div>
    </div>
    <div class="col-lg-12">
    <div class="card p-3">
        <div class="row">
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="w-100px">
                        <img src="{{ $service->postauthor->uploads->getImageOptimizedFullName(200,200) }}" alt="avatar" class="rounded img-fluid border">
                    </div>
                    <div class="ml-15px">
                        <a href="/u/{{ $service->postauthor->username }}" class="fs-18 fw-700 text-black">{{ $service->postauthor->full_name }} ({{ '@'. $service->postauthor->username }})</a>
                        <p class="mb-1 mt-1">{{ $service->seller->slogan ?? 'No Slogan' }}</p>
                        @if ($rating->count > 0)
                        <div class="mb-1">
                            <span><i class="bi bi-star-fill fs-20 text-blue"></i> {{ $rating->rating ?: "0.0" }}</span>
                            <span class="text-secondary">({{$rating->count}})</span>
                        </div>
                        @endif
                        <div class="d-flex justify-content-start d-none">
                            <a class="text-primary" href="{{route('create_chat_room',['conversation_id'=>$service->seller->user->username])}}">Message Me</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="mb-4 about-seller">
                    <div class="row">
                        <div class="col mb-3">
                            <span class="text-muted mb-0">Member since</span>
                            <div class="fw-700">{{ $service->postauthor->created_at->format('M Y') }}</div>
                        </div>
                        <div class="col mb-3">
                            <span class="text-muted mb-0">Avg. response time</span>
                            <div class="fw-700">{{ !$service->postauthor->get_avg_response_time() == '-' ? '-' : (round($service->postauthor->get_avg_response_time() * 60) . (round($service->postauthor->get_avg_response_time() * 60) == 1 ? ' Minute' : ' Minutes')) }}</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <span class="text-muted mb-0">Last delivery</span>
                            <div class="fw-700">{{ !$service->postauthor->last_delivery_time() ? 'None' : $service->postauthor->last_delivery_time()->diffForHumans() }}</div>
                        </div>
                        <div class="col">
                            <span class="text-muted mb-0">Total deliveries</span>
                            <div class="fw-700">
                                @if ($rating->count > 0)
                                {{$rating->count}}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>    
            </div>    
            <div class="col-lg-12">        
                <div class="car">
                    <div class="card-bod">
                        {{ $service->seller->about }}
                    </div>
                </div>
            </div> 
        </div>
    </div>
    </div>
</div>


                        <div class="row">
                            <div class="col-lg-12">
                                <div class="section-header">
                                    <div class="section-header-title mb-3 text-uppercase fw-700 border p-3 card rounded">Reviews</div>
                                </div>

                                @if ($review->count() > 0)
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="text-center">
                                                <div class="star-ratings me-auto ml-auto mb-2">
                                                    <div class="relative">
                                                        <div class="fill-ratings" style="width: {{ $average_rating * 150 / 5 }}px;">
                                                            <img src="/assets/img/star_fill.png" width="150">
                                                        </div>
                                                        <div class="empty-ratings">
                                                            <img src="/assets/img/star_empty.png" width="150">
                                                        </div>
                                                    </div>
                                                </div>
                                                <h1 class="text-black fs-30 fw-700">{{ $average_rating }}</h1>
                                                <p>based on {{ $review_count }} reviews</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="card-body">
                                            @foreach ($review as $reviewItem)
                                                <div class="user-review-item pb-4 mb-4">
                                                    <div class="row">
                                                        <div class="col-lg-3">
                                                            <div class="d-flex pb-3">
                                                                <img id="fileManagerPreview" src="{{ $reviewItem->avatar_url }}" class="reviewer_avatar border rounded h-60px mr-15px">
                                                                <div class="review-details-meta">
                                                                    <div class="fs-18 fw-600 reviewer_name w-100">
                                                                        <span>{{ $reviewItem->first_name }} {{ $reviewItem->last_name }}</span>
                                                                    </div>
                                                                    <div class="fs-15 fw-600 opacity-50 reviewer_name w-100">
                                                                        <span>{{ '@' . $reviewItem->username }}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-9">
                                                            <div class="pb-3">
                                                                <div class="d-flex pb-2">
                                                                    <div class="star-ratings fs-12">
                                                                        <div class="fill-ratings" style="width: {{ $reviewItem->rating * 20 }}px;">
                                                                            <img src="/assets/img/star_fill.png" width="100">
                                                                        </div>
                                                                        <div class="empty-ratings">
                                                                            <img src="/assets/img/star_empty.png" width="100">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="rated_date fs-15 fw-600 opacity-50 pb-2">Rated at {{ $reviewItem->created_at->format('M d, Y') }}</div>
                                                                <div class="pb-2">
                                                                    @if ($reviewItem->featured_img)
                                                                        <img src="{{ $reviewItem->featured_img }}" alt="" class="reviewer_avatar border rounded" width="150">
                                                                    @endif
                                                                </div>
                                                                <span>{{ $reviewItem->review }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach

                                        </div>
                                    </div>
                                @else
                                    <div class="card">
                                        <div class="card-body">
                                            <p class="text-left mb-0">No reviews posted.</p>
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @section('hasMessageBox')
        @if(Auth::check() && Auth::user()->id != optional($service->seller)->user_id)
        <div class="service-message-button d-flex align-items-center">
            <div>
                <img src="{{ $service->postauthor->uploads->getImageOptimizedFullName(200,200) }}" alt="avatar" class="rounded img-fluid border" width="50">
            </div>
            <div class="ml-10px">
                <div class="message-btn-title fw-500 fs-18"> Message {{ '@'.$service->postauthor->username }}</div>
                <div class="message-btn-content fs-15">
                    <span class="text-muted mb-0">Avg. response time : </span>
                    <span>{{ !$service->postauthor->get_avg_response_time() == '-' ? '-' : (round($service->postauthor->get_avg_response_time() * 60) . (round($service->postauthor->get_avg_response_time() * 60) == 1 ? ' Minute' : ' Minutes')) }}</span>
                </div>
            </div>
        </div>
        @endif

        <div class="service-message-container border d-none">
            <div class="msg-reference-title d-flex align-items-center justify-content-center">
                <span class="fs-12">It might take some time to get a response</span>
            </div>
            <div class="msg-box-seller-info d-flex align-items-center">
                <div>
                    <img src="{{ asset('assets/img/avatar.png') }}"
                        alt="avatar"
                        class="rounded-circle img-fluid border" width="50">
                </div>
                <div class="ml-10px w-100">
                    <div class="message-btn-title fw-500 fs-18 d-flex justify-content-between"> 
                        <div>
                            Message {{ $service->postauthor->full_name }}
                        </div>                    
                        <div class="msg-box-close">X</div>
                    </div>
                    <div class="message-btn-content fs-15">
                        <span class="text-muted mb-0">Avg. response time : </span>
                        <span>{{ !$service->postauthor->get_avg_response_time() == '-' ? '-' : (round($service->postauthor->get_avg_response_time() * 60) . (round($service->postauthor->get_avg_response_time() * 60) == 1 ? ' Minute' : ' Minutes')) }}</span>
                    </div>
                </div>
            </div>
            <div class="msg-box">
                <div class="msg-display">
                </div>
                <input class="msg-type" placeholder="Type here...">
                <div class="msg-btns">
                    <button class="fs-12 p-2" id="sendMsg"><i class="bi bi-send"></i> Send Message</button>
                </div>
            </div>
        </div>

        <div class="service-message-sent-container d-none">
            <div class="text-right msg-box-close">X</div>
            <div class="text-center mt-10">
                <div class="fw-700 fs-20 mb-3">Message sent!</div>
                <div class="fs-15">{{ $service->postauthor->full_name }} usually responds within 1 Hour.</div>
                <div class="fs-15">An email will be sent once they reply.</div>
            </div>
            <div class="mt-7 w-100">
                <button class="w-100 btn btn-success mb-1 msg-box-close">Got it</button>
                <button class="w-100 btn btn-default" id="view-msg">View Message</button>
            </div>
        </div>

        <script type="text/javascript">
            $(function() {
                $('.service-message-button').click(function(e) {
                    
                    $.ajax({
                        url: '{{ route("services.messageCheckExisted") }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            seller: '{{ $service->postauthor->id }}',
                        },
                        dataType: 'JSON',
                        success: function(res) {
                            if (res > 0) {
                                location.href = `/chat/{{ $service->postauthor->username }}`;
                            } else {
                                $('.service-message-button').addClass('d-none');
                                $('.service-message-container').removeClass('d-none');
                            }
                        }
                    })
                })

                $('.msg-box-close').click(function(e) {
                    $('.service-message-button').removeClass('d-none');
                    $('.service-message-container').addClass('d-none');
                    $('.service-message-sent-container').addClass('d-none');
                })

                $('#view-msg').click(function(e) {
                    location.href = `/chat/{{ $service->postauthor->username }}`;
                })

                $('#sendMsg').click(function(e) {

                    if ($('.msg-type').val() != '') {

                        $.ajax({
                            url: '{{ route("services.message") }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                seller: '{{ $service->postauthor->id }}',
                                content: $('.msg-type').val()
                            },
                            dataType: 'JSON',
                            success: function(res) {

                                var msg = "<div class='msg-content'>" + $('.msg-type').val() + "</div>";
                                $('.msg-display').prepend(msg);
                                $('.msg-type').val('');

                                $('.service-message-container').addClass('d-none');
                                $('.service-message-sent-container').removeClass('d-none');
                            }
                        })
                    }
                })
            })
        </script>
    @stop

</x-app-layout>
