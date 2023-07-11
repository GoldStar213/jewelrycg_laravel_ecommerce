<x-app-layout :page-title="'Order #' . $order->order_id . ' | Jewelry CG'">
    <meta name="_token" content="{{csrf_token()}}"/>
    <link rel="stylesheet" href="{{ asset('dropzone/css/dropzone.css') }}">
    <style>


        .was-validated .required .invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url(data:image/svg+xml,%3csvg xmlns= 'http://www.w3.org/2000/svg' viewBox= '0 0 12 12' width= '12' height= '12' fill= 'none' stroke= '%23dc3545' %3e%3ccircle cx= '6' cy= '6' r= '4.5' /%3e%3cpath stroke-linejoin= 'round' d= 'M5.8 3.6h.4L6 6.5z' /%3e%3ccircle cx= '6' cy= '8.2' r= '.6' fill= '%23dc3545' stroke= 'none' /%3e%3c/svg%3e);
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .was-validated .required .valid {
            border-color: #198754;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url(data:image/svg+xml,%3csvg xmlns= 'http://www.w3.org/2000/svg' viewBox= '0 0 8 8' %3e%3cpath fill= '%23198754' d= 'M2.3 6.73.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z' /%3e%3c/svg%3e);
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
    </style>
    <div class="container">
        <div class="col-lg-11 col-md-10 py-9 mx-auto checkout-wrap">
            <div class="row">
                <div class="col-lg-9">
                    @include('includes.validation-form')
                    @if (session('success'))
                        <!--<div class="alert alert-success" role="alert">{{session('success')}}</div>-->
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">{{session('error')}}</div>
                    @endif
                    <div class="card mb-4">
                        <div class="card-body">
                            @if ($order->status == 0)
                                <h4 class="fw-700">Order Received</h4>
                                <p class="mb-0">Please submit the requirements in order to start job.</p>
                            @else
                                <h4 class="fw-700">Order Started</h4>
                                <p class="mb-0">You sent all the information needed and your order has started.</p>
                            @endif
                        </div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="timeline-item pb-3 mb-3 border-bottom">
                                <i class="bi bi-clipboard-check p-1"></i>
                                <span
                                    class="">You placed the order {{ date('F d, Y h:i A', strtotime($order->created_at)) }}</span>
                            </div>
                            @if ($order->status != 0)
                                @if (count($requirements) > 0)
                                    <div class="timeline-item pb-3 mb-3 border-bottom">
                                        <i class="bi bi-clipboard-check p-1"></i>
                                        <span
                                            class="">You sent the requirements {{ date('F d, Y h:i A', strtotime($order->original_delivery_time)) }}</span>
                                    </div>
                                @endif

                                @if (count($answers) > 0)
                                    <div class="card">
                                        <div class="card-header fw-700">Requirements</div>
                                        <div class="card-body">
                                            @foreach ($answers as $answer)
                                                <div class="col">
                                                    <h4>{{ $answer->requirement->delivery }}</h4>

                                                    @if ($answer->requirement->type == 0)
                                                        <p>{{ $answer->answer }}</p>

                                                    @elseif ($answer->requirement->type == 1)
                                                        <ul>
                                                            @foreach ($answer->attaches as $attach)
                                                                <li>
                                                                    <a href="/uploads/all/{{ $attach->file_name }}"
                                                                       download>
                                                                        {{ $attach->file_original_name . "." . $attach->extension }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>

                                                    @elseif ($answer->requirement->type == 2)
                                                        <p>{{$answer->answer}}</p>
                                                    @else
                                                        <ul>
                                                            @foreach ($answer->answers as $answer)
                                                                <li><p>{{ $answer }}</p></li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endif
                            @if ($order->status != 0)
                                <div class="timeline-item pb-3 mb-3 border-bottom">
                                    <i class="bi bi-clipboard-check p-1"></i>
                                    <span
                                        class="">The order started {{ date('F d, Y h:i A', strtotime($order->original_delivery_time)) }}</span>
                                </div>
                                <div class="timeline-item pb-3 mb-3 border-bottom">
                                    <i class="bi bi-clipboard-check p-1"></i>
                                    <span
                                        class="">Your delivery date was updated to {{ date('F d, Y h:i A', strtotime($order->original_delivery_time)) }}</span>
                                </div>

                                @if (count($deliveries) > 0)
                                   <!--<div class="timeline-item pb-3 mb-3 border-bottom">
                                        <i class="bi bi-clipboard-check p-1"></i>
                                        <span class=""><b>{{ $seller->first_name . " " . $seller->last_name }}</b> delivered the order {{ date('F d, Y h:i A', strtotime($deliveries[0]->created_at)) }}</span>
                                    </div>-->
                                @endif
                            @endif

                            @foreach ($deliveries as $key => $delivery)
                            @if ($key >= 0)
                                <div class="timeline-item pb-3 mb-3 border-bottom">
                                    <i class="bi bi-clipboard-check p-1"></i>
                                    <span class=""><b>{{ $seller->first_name . " " . $seller->last_name }}</b> delivered the order {{ date('F d, Y h:i A', strtotime($delivery->created_at)) }}</span>
                                </div>
                                <div class="card">
                                    <div class="card-header">Deliver #{{$key + 1}}</div>
                                    <div class="card-body">
                                        <p>{!! $delivery->message !!}</p>
                                        <ul>
                                            @foreach ($delivery->attaches as $attach)
                                                <li>
                                                    <a href="/uploads/all/{{ $attach->file_name }}" download>
                                                        {{ $attach->file_original_name . "." . $attach->extension }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>

                                @if (!$delivery->revision && $order->status == 4)
                                    <div class="card">
                                        <div class="card-header">
                                            You received delivery
                                            from {{$seller->first_name . " " . $seller->last_name}}<br>
                                            Are you pleased with the delivery and ready to approve it?
                                        </div>
                                        <div class="card-body">
                                            @if (count($deliveries) == ($key+1))
                                            <div class="row">
                                                <form action="{{ route('services.order_complete') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="order_id" value="{{$order->id}}">
                                                    <div class="alert alert-success" role="alert">
                                                        You have <span id="orderLeftDay"></span> and 
                                                        <span id="orderLeftHr"></span>
                                                        <span id="orderLeftMin"></span>
                                                        to review and approve or request changes before its automatically approved.
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-auto mb-2">
                                                            <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapseSubmit" role="button" aria-expanded="false" aria-controls="collapseSubmit">I approve delivery</a>
                                                        </div>
                                                        <div class="col-auto">
                                                            @if ($order->revisions)
                                                                <button type="button" class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#messageModal">I'm not ready yet</button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="collapse" id="collapseSubmit">
                                                        <div class="card card-body mt-3">
                                                            <p class="text-danger">Are you sure you approve the delivery?</p>

                                                            <div class="row">
                                                                <div class="col-auto mb-2">
                                                                    <button class="btn btn-primary" type="submit">Yes, I approve delivery</button>
                                                                </div>
                                                                <div class="col-auto">
                                                                    <button class="btn btn-danger" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSubmit" aria-expanded="false" aria-controls="collapse Submit">Not Yet</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="collapse" id="messageModal" data-bs-backdrop="static"
                                         data-bs-keyboard="false" tabindex="-1" aria-labelledby="messageModalLabel"
                                         aria-hidden="true">
                                        <form action="{{ route('services.order_revision') }}" method="POST">
                                            @csrf
                                            <div class="card col-md-12 mb-4">
                                                <div class="card-header">
                                                    What revisions would you
                                                    like {{$seller->first_name . " " . $seller->last_name}} to make?
                                                </div>
                                                <!-- End Header -->
                                                <div class="card-body">
                                                    <input type="hidden" name="order_id" id="order_id"
                                                           value="{{ $order->id }}">
                                                    <input type="hidden" name="delivery_id" id="delivery_id"
                                                           value="{{ $delivery->id }}">
                                                    <div class="mb-2">
                                                        <label for="message" class="w-100 mb-2">Message</label>
                                                        <textarea name="message" id="message" rows="6"
                                                                  class="form-control">{{ old('message') }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Close
                                            </button>
                                            <button type="submit" class="btn btn-primary">Submit Message</button>
                                        </form>
                                    </div>
                                @elseif ($order->status != 5)
                                    <div class="timeline-item pb-3 mb-3 border-bottom">
                                        <i class="bi bi-clipboard-check p-1"></i>
                                        <span
                                            class="">You requested a revision on this delivery {{ date('F d, Y h:i A', strtotime($delivery->revision->created_at)) }}</span>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">Revision #{{$key + 1}}</div>
                                        <div class="card-body">
                                            <p>{!! $delivery->revision->message ?? 'No revision message available' !!}
</p>
                                        </div>
                                    </div>
                                @endif
                            @endif
                            @endforeach
                            

                            @if ($order->status == 5)
                                <div class="timeline-item pb-3 mb-3 border-bottom">
                                    <i class="bi bi-clipboard-check p-1"></i>
                                    <span class="">You approved delivery at {{ date('F d, Y h:i A', strtotime($order->updated_at)) }}. Order completed</span>
                                </div>
                                @if (count($order->review))
                                    <div class="timeline-item pb-3 mb-3 border-bottom">
                                        <i class="bi bi-clipboard-check p-1"></i>
                                        <span
                                            class="">You left a review to service at {{ date('F d, Y h:i A', strtotime($order->review[0]->created_at)) }}</span>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Your Review</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="rate pb-3">
                                                @for ($i = 5; $i > 0; $i--)
                                                    <input type="radio" id="star{!! $i !!}" class="rate" name="rating1" value="{!! $i !!}"
                                                        {{ $order->review[0]->rating == $i ? "checked" : "" }} disabled/>
                                                    <label for="star{!! $i !!}" class="d-flex relative">
                                                        @if ($order->review[0]->rating >= $i)
                                                            <img src="/assets/img/star_blue.png" width="25" class="label-check absolute d-none"/>
                                                        @else
                                                            <img src="/assets/img/star_gray.png" width="25" class="label-not-check"/>
                                                        @endif
                                                    </label>
                                                @endfor
                                            </div>
                                            
                                            <div style="clear: left;">
                                                {{ $order->review[0]->review }}
                                            </div>
                                        </div>
                                    </div>
                                    @if (count($order->review) == 2)
                                        <div class="timeline-item pb-3 mb-3 border-bottom">
                                            <i class="bi bi-clipboard-check p-1"></i>
                                            <span class="">{{$order->service->postauthor->first_name . " " . $order->service->postauthor->last_name}} sent review to you at {{ date('F d, Y h:i A', strtotime($order->review[1]->created_at)) }}</span>
                                        </div>
                                        <div class="card">
                                            <div class="card-header">
                                                <h5>{{$order->service->postauthor->first_name . " " . $order->service->postauthor->last_name}}
                                                    's Review</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="rate pb-3">
                                                    @for ($i = 5; $i > 0; $i--)
                                                        <input type="radio" id="star{!! $i !!}" class="rate" name="rating2" value="{!! $i !!}"
                                                            {{ $order->review[1]->rating == $i ? "checked" : "" }} disabled/>
                                                        <label for="star{!! $i !!}" class="d-flex relative">
                                                            @if ($order->review[1]->rating >= $i)
                                                                <img src="/assets/img/star_blue.png" width="25" class="label-check absolute d-none"/>
                                                            @else
                                                                <img src="/assets/img/star_gray.png" width="25" class="label-not-check"/>
                                                            @endif
                                                        </label>
                                                    @endfor
                                                </div>
                                                <div style="clear: left;">
                                                    {{ $order->review[1]->review }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <a href="{{ route('services.review', $order->order_id) }}">Leave a review</a>
                                @endif
                            @endif

                            @if(Session::get('message') != null)
                                <div class="alert alert-success">
                                    {{ Session::get('message') }}
                                </div>
                            @endif
                            @if (count($requirements) > 0 && $order->status == 0)
                                <div class="card">
                                    <div class="card-header">Submit Requirements</div>
                                    <div class="card-body">
                                        <form id="question-form" class="needs-validation"
                                              action="{{ route('services.answer') }}" method="post"
                                              enctype="multipart/form-data" novalidate>
                                            @csrf
                                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                                            @foreach ($requirements as $requirement)
                                                <div class="mb-3">
                                                    <label
                                                        class="fs-4 mb-2 {{ $requirement->required ? "required" : "" }}"
                                                        for="answer-{{$requirement->id}}">- {{ $requirement->question }}</label>
                                                    @if($requirement->type == 0)
                                                        <div class="form-group">
                                                            <textarea type="text" class="form-control"
                                                                      id="answer-{{$requirement->id}}"
                                                                      data-id="{{$requirement->id}}" name="answer[]"
                                                                      placeholder="Type question here" {{ $requirement->required ? "required" : "" }}></textarea>
                                                        </div>
                                                    @elseif($requirement->type == 1)
                                                        <div
                                                            class="form-group {{ $requirement->required ? "required" : "" }}">
                                                            <input class="answer" type="hidden"
                                                                   id="answer-{{$requirement->id}}"
                                                                   data-id="{{$requirement->id}}" name="answer[]">
                                                            <div
                                                                class="form-control invalid attach-dropzone dropzone attach-{{$requirement->id}}"
                                                                data-id="{{$requirement->id}}"></div>
                                                        </div>
                                                    @elseif($requirement->type == 2)
                                                        <div
                                                            class="form-group {{ $requirement->required ? "required" : "" }}">
                                                            <input class="answer" type="hidden"
                                                                   id="answer-{{$requirement->id}}"
                                                                   data-id="{{$requirement->id}}" name="answer[]">
                                                            @foreach($requirement->choices as $key => $choice)
                                                                <div
                                                                    class="select-option form-row-between invalid single">{{$choice->choice}}</div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div
                                                            class="form-group {{ $requirement->required ? "required" : "" }}">
                                                            <input class="answer" type="hidden"
                                                                   id="answer-{{$requirement->id}}"
                                                                   data-id="{{$requirement->id}}" name="answer[]">
                                                            @foreach($requirement->choices as $key => $choice)
                                                                <div
                                                                    class="select-option form-row-between invalid multi">{{$choice->choice}}</div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach

                                            <div class="mb-0">
                                                <button type="submit" class="btn btn-primary">Submit Requirements
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    @if ($order->status == 1 || $order->status == 2)
                        <div class="card mb-4">
                            <div class="card-header">Extend Delivery Time Request</div>
                            <div class="card-body">
                                @if($extend_requests->count() > 0)
                                        @foreach($extend_requests as $request)
                                            <div class="w-100">
                                                <span>+{{ $request->days }} Days:</span>
                                                @if($request->status == 0)
                                                    <button class="btn btn-success approve-request" data-request-id="{{ $request->id }}">Approve</button>
                                                    <button class="btn btn-danger decline-request" data-request-id="{{ $request->id }}">Decline</button>
                                                @elseif($request->status == 1)
                                                    <span class="text-success">Approved</span>
                                                @elseif($request->status == 2)
                                                    <span class="text-danger">Declined</span>
                                                @endif
                                            </div>
                                        @endforeach
   
                                @else
                                    <p>No extend delivery time requests found.</p>
                                @endif
                            </div>
                        </div>
                        <div class="card mb-4 time-left">
                            <div class="card-header" id="count_title">Time left to deliver</div>
                            <div class="card-body">
                                <div class="col-md-12 d-flex justify-content-between align-items-center my-2">
                                    <div class="d-flex flex-column align-items-center" style="width: 23%;">
                                        <h5 id="count_day">00</h5>
                                        <p class="opacity-70 mb-0">Days</p>
                                    </div>
                                    <div class="bg-black opacity-70" style="width: 1px; height: 30px;"></div>
                                    <div class="d-flex flex-column align-items-center" style="width: 23%;">
                                        <h5 id="count_hour">00</h5>
                                        <p class="opacity-70 mb-0">Hours</p>
                                    </div>
                                    <div class="bg-black opacity-70" style="width: 1px; height: 30px;"></div>
                                    <div class="d-flex flex-column align-items-center" style="width: 23%;">
                                        <h5 id="count_min">00</h5>
                                        <p class="opacity-70 mb-0">Minutes</p>
                                    </div>
                                    <div class="bg-black opacity-70" style="width: 1px; height: 30px;"></div>
                                    <div class="d-flex flex-column align-items-center" style="width: 23%;">
                                        <h5 id="count_sec">00</h5>
                                        <p class="opacity-70 mb-0">Seconds</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="card mb-4 order-details">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-3">
                                    <img src="{{ $order->service->uploads->getImageOptimizedFullName(150) }}" alt=""
                                         class="thumbnail border w-100">
                                </div>
                                <div class="col-9">
                                    <div class="fs-18 fw-700">{{ $order->service->name }} ({{ $order->package_name }})</div>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-row mb-1 justify-content-between">
                                <span>Status</span>
                                <span>{{ Config::get('constants.service_order_status')[$order->status] }}</span>
                            </div>
                            <div class="d-flex flex-row mb-1 justify-content-between">
                                <span>Ordered from</span>
                                <span>{{ $seller->first_name . " " . $seller->last_name }}</span>
                            </div>
                            <div class="d-flex flex-row mb-1 justify-content-between">
                                <span>Delivery Date</span>
                                <span>{{ date('F d, Y h:i A', strtotime($order->original_delivery_time)) }}</span>
                            </div>
                            <div class="d-flex flex-row mb-1 justify-content-between">
                                <span>Total Price</span>
                                <span>${{ number_format($order->package_price / 100, 2) }}</span>
                            </div>
                            <div class="d-flex flex-row mb-1 justify-content-between">
                                <span>Order Number</span>
                                <span>{{ $order->order_id }}</span>
                            </div>
                            <a class="btn btn-primary w-100 mt-2" href="{{route('create_chat_room',['conversation_id'=>$seller->username])}}">Message Seller</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @section('js')
        <script src="{{ asset('dropzone/js/dropzone.js') }}"></script>
<script>
    // Wait for the document to be ready
    document.addEventListener('DOMContentLoaded', function () {
        // Add event listener to each approve button
        $('.approve-request').off('click').on('click', function() {
            var requestId = $(this).data('request-id');
            sendRequest(requestId, 'approve');
        });

        $('.decline-request').off('click').on('click',function() {
            var requestId = $(this).data('request-id');
            sendRequest(requestId, 'decline');
        });

        // Function to send the AJAX request
        function sendRequest(requestId, action) {
            // Prepare the data to be sent
            var data = {
                extend_delivery_id: requestId,
                buyer_id: '{{ $order->buyer_id }}',
                action: action
            };

            // Send the AJAX request
            fetch('/services/extend-service-delivery/answer-request', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(function(response) {
                window.location.reload();
                // return response.json();
            })
            .then(function(data) {
                // Show the response message
                // You can update the UI as needed based on the response
                // For example, you can remove the request item from the list
                // or refresh the page to update the data.
            })
            .catch(function(error) {
                console.error('Error:', error);
            });
        }
    });
</script>

        <script>

            var originDeliverDate = "{{ $order->original_delivery_time }}";
            var deliveredDate = "{{ count($deliveries) > 0 ? $deliveries[count($deliveries)-1]->created_at : '0000-00-00 00:00:00' }}";

            var deliveredCountDownDate, originalCountDownDate;


            function getDeliveredCountDownDate() {
                if (deliveredDate !== "0000-00-00 00:00:00") {
                    var dateTimeParts = deliveredDate.split(" ");
                    var dateParts = dateTimeParts[0].split("-");
                    var timeParts = dateTimeParts[1].split(":");

                    var year = parseInt(dateParts[0]);
                    var month = parseInt(dateParts[1]) - 1; // months are 0-based in JavaScript
                    var day = parseInt(dateParts[2]);

                    var hour = parseInt(timeParts[0]);
                    var minute = parseInt(timeParts[1]);
                    var second = parseInt(timeParts[2]);

                    deliveredCountDownDate = new Date(year, month, day, hour, minute, second);
                    deliveredCountDownDate.setDate(deliveredCountDownDate.getDate() + 2); // Add 2 days to the date

                    deliveredCountDownDate = deliveredCountDownDate.getTime();
                } else {
                    deliveredCountDownDate = 0; // Set the countdown date to 0 if deliveredDate is "0000-00-00 00:00:00"
                }
            }

            function getOriginalCountDownDate() {
                var dateTimeParts = originDeliverDate.split(" ");
                var dateParts = dateTimeParts[0].split("-");
                var timeParts = dateTimeParts[1].split(":");

                var year = parseInt(dateParts[0]);
                var month = parseInt(dateParts[1]) - 1; // months are 0-based in JavaScript
                var day = parseInt(dateParts[2]);

                var hour = parseInt(timeParts[0]);
                var minute = parseInt(timeParts[1]);
                var second = parseInt(timeParts[2]);

                originalCountDownDate = new Date(year, month, day, hour, minute, second).getTime();
            }

            function padLeadingZeros(num, size) {
            var s = num.toString();
            while (s.length < size) s = "0" + s;
            return s;
            }

            function calculateCountdown() {
                // Get today's date and time
                var now = new Date().getTime();

                // Find the distance between now and the count down date
                var distance = originalCountDownDate - now;

                // Time calculations for days, hours, minutes and seconds
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                $('#count_day').text(padLeadingZeros(days, 2));
                $('#count_hour').text(padLeadingZeros(hours, 2));
                $('#count_min').text(padLeadingZeros(minutes, 2));
                $('#count_sec').text(padLeadingZeros(seconds, 2));

                // If the count down is finished, write some text
                if (distance < 0) {
                    clearInterval(x);
                    $('#count_title').text("Delivery time has already passed");
                }
            }

            function calculateCountdownAlert() {
                // Get today's date and time
                var now = new Date().getTime();

                // Find the distance between now and the count down date
                var distance = now - deliveredCountDownDate;

                // Time calculations for days, hours, minutes and seconds
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                Math.abs(days) > 1 ? $('#orderLeftDay').text(Math.abs(days) + ' days') :  $('#orderLeftDay').text(Math.abs(days) + ' day');
                Math.abs(hours) > 1 ? $('#orderLeftHr').text(Math.abs(hours) + ' hours') : $('#orderLeftHr').text(Math.abs(hours) + ' hour');
                Math.abs(minutes) > 1 ? $('#orderLeftMin').text(Math.abs(minutes) + ' minutes') : $('#orderLeftMin').text(Math.abs(minutes) + ' minute');
                Math.abs(seconds) > 1 ? $('#orderLeftSec').text(Math.abs(seconds) + ' seconds') : $('#orderLeftSec').text(Math.abs(seconds) + ' second');

                // If the count down is finished, write some text
                if (distance > 3 && distance < 0) {
                    clearInterval(y);
                }
            }

            // get each countdown
            getDeliveredCountDownDate();
            getOriginalCountDownDate();

            console.log(deliveredCountDownDate);
            // Call function immediately
            calculateCountdown();
            calculateCountdownAlert();

            // Then set interval
            var x = setInterval(calculateCountdown, 1000);
            var y = setInterval(calculateCountdownAlert, 1000);

        </script>
    @endsection
</x-app-layout>
