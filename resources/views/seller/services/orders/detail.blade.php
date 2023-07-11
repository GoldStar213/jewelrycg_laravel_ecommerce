<x-app-layout :page-title="'ORDER #' . $order->order_id">
    <meta name="_token" content="{{csrf_token()}}"/>
    <div class="container">
        <div class="col-lg-11 col-md-10 py-9 mx-auto checkout-wrap">
            <div class="row">
                <div class="col-9">
                    @include('includes.validation-form')
                    @if (session('success'))
                        <!--<div class="alert alert-success" role="alert">{{session('success')}}</div>-->
                    @endif
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="fw-700">Order Started</h4>
                            @if ($order->status == 0)
                                <p class="p-0">Pending requirements in order to start job. Contact to
                                    <b>{{ $order->user->first_name . " " . $order->user->last_name }}</b> and let them
                                    know to submit the requirements.</p>
                            @else
                                <p class="p-0"><b>{{ $order->user->first_name . " " . $order->user->last_name }}</b>
                                    sent all the information you need so you can start working on this order. You got
                                    this!</p>
                            @endif
                        </div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="timeline-item pb-3 mb-3 border-bottom">
                                <i class="bi bi-clipboard-check p-1"></i>
                                <span class=""><b>{{ $order->user->first_name . " " . $order->user->last_name }}</b> placed the order {{ date('F d, Y h:i A', strtotime($order->created_at)) }}</span>
                            </div>
                            @if ($order->status != 0)
                                <div class="timeline-item pb-3 mb-3 border-bottom">
                                    <i class="bi bi-clipboard-check p-1"></i>
                                    <span class=""><b>{{ $order->user->first_name . " " . $order->user->last_name }}</b> sent the requirements {{ date('F d, Y h:i A', strtotime($order->original_delivery_time)) }}</span>
                                </div>

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
                            <div class="timeline-item pb-3 mb-3 border-bottom">
                                <i class="bi bi-clipboard-check p-1"></i>
                                <span class="">The order started {{ date('F d, Y h:i A', strtotime($order->original_delivery_time)) }}</span>
                            </div>
                            <div class="timeline-item pb-3 mb-3 border-bottom">
                                <i class="bi bi-clipboard-check p-1"></i>
                                <span class="">Your delivery date was updated to {{ date('F d, Y h:i A', strtotime($order->original_delivery_time)) }}</span>
                            </div>

                            <!--
                            @if (count($deliveries) > 0)
                                <div class="timeline-item pb-3 mb-3 border-bottom">
                                    <i class="bi bi-clipboard-check p-1"></i>
                                    <span class="">You delivered the order {{ date('F d, Y h:i A', strtotime($order->original_delivery_time)) }}</span>
                                </div>
                            @endif
                            -->

                            @foreach ($deliveries as $key => $delivery)
                                <div class="timeline-item pb-3 mb-3 border-bottom">
                                    <i class="bi bi-clipboard-check p-1"></i>
                                    <span class="">You delivered the order {{ date('F d, Y h:i A', strtotime($delivery->created_at)) }}</span>
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

                                @if ($delivery->revision)
                                    <div class="timeline-item pb-3 mb-3 border-bottom">
                                        <i class="bi bi-clipboard-check p-1"></i>
                                        <span class="">{{$buyer->first_name . " " . $buyer->last_name}} requested a revision on this delivery {{ date('F d, Y h:i A', strtotime($delivery->revision->created_at)) }}</span>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">Revision #{{$key + 1}}</div>
                                        <div class="card-body">
                                            <p>{!! $delivery->revision->message !!}</p>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            @if ($order->status == 5)
                                <div class="timeline-item pb-3 mb-3 border-bottom">
                                    <i class="bi bi-clipboard-check p-1"></i>
                                    <span class="">Your approved delivery at {{ date('F d, Y h:i A', strtotime($order->updated_at)) }}. Order completed</span>
                                </div>
                                @if (count($order->review))
                                    <div class="timeline-item pb-3 mb-3 border-bottom">
                                        <i class="bi bi-clipboard-check p-1"></i>
                                        <span class="">{{$order->user->first_name . "" . $order->user->last_name }} left a review to your service at {{ date('F d, Y h:i A', strtotime($order->review[0]->created_at)) }}</span>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>{{$order->user->first_name . "" . $order->user->last_name }}'s Review</h5>
                                        </div>
                                        <div class="card-body">
                                            @if(count($order->review) == 2)
                                                
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
                                                <div style="clear: left">
                                                    {{ $order->review[0]->review }}
                                                </div>
                                            @else
                                                Rate the buyer({{ $order->user->full_name }}) to see their review.
                                            @endif
                                        </div>
                                    </div>
                                    @if (count($order->review) == 2)
                                        <div class="timeline-item pb-3 mb-3 border-bottom">
                                            <i class="bi bi-clipboard-check p-1"></i>
                                            <span class="">You sent a review to {{$order->user->first_name . " " . $order->user->last_name}} at {{ date('F d, Y h:i A', strtotime($order->review[1]->created_at)) }}</span>
                                        </div>
                                        <div class="card">
                                            <div class="card-header">
                                                <h5>Your Review</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="rate pb-3">
                                                    @for ($i = 5; $i > 0; $i--)
                                                        <input type="radio" id="star{!! $i !!}" class="rate" name="rating" value="{!! $i !!}"
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
                                                <div style="clear: left">
                                                    {{ $order->review[1]->review }}
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <a href="{{ route('services.review', $order->order_id) }}">Rate the buyer</a>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>

                </div>
                <div class="col-3">
                    <div class="card mb-4 time-left">
                        <div class="card-header" id="count_title">Time left to deliver</div>
                        <div class="card-body">
                            @php
                                // Calculate the remaining time until the original delivery time
                                $originalDeliveryTime = strtotime($order->original_delivery_time);
                                $remainingTime = $originalDeliveryTime - time();
                            @endphp
                            @if ($order->status == 1 || $order->status == 2)
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
                                @if($order->status == 1 && $order->order_service_revision_requests->count() == 0)
                                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#deliverModal">Deliver Now</button>
                                @else
                                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#deliverModal">Deliver again</button>
                                @endif
                                @if ($remainingTime > 0 && $remainingTime <= 48 * 60 * 60)
                                <button id="extendDeliveryBtn" class="btn btn-danger w-100 mt-2">Extend Delivery Time</button>
                                <div id="extendDeliveryForm" style="display: none;">
                                    <select class="form-select my-2" id="daysInput">
                                        <option value="1">1 day</option>
                                        @for ($i = 2; $i <= 30; $i ++)
                                            <option value="{{ $i }}">{{ $i }} days</option>
                                        @endfor
                                    </select>
                                    <input type="hidden" id="orderIdInput" value="{{ $order->order_id }}">
                                    <input type="hidden" id="buyerIdInput" value="{{ $order->user_id }}">
                                    <button id="requestExtensionBtn" class="btn btn-success w-100">Submit Request</button>
                                    <div id="requestMessage" class="alert alert-success mt-2 d-none">Extention requested.</div>
                                </div>
                                @endif
                              

                            @elseif ($order->status == 0)
                                <div class="col-md-12">
                                    Didn't receive requirement yet
                                </div>
                            @elseif ($order->status == 3)
                                <div class="col-md-12">
                                    Order canceled
                                </div>
                            @elseif ($order->status == 4)
                                <div class="col-md-12">
                                    Delivered
                                </div>
                            @elseif ($order->status == 5)
                                <div class="col-md-12">
                                    Completed
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card mb-4 order-details">
                        <div class="card-header">
                            <div class="row mb-3">
                                <div class="col-3">
                                    <img src="{{ $order->service->uploads->getImageOptimizedFullName(150) }}" alt=""
                                         class="thumbnail border w-100">
                                </div>
                                <div class="col-9">
                                    <div class="fs-18 fw-700">{{ $order->service->name }}</div>
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
                                <span>{{ $buyer->first_name . " " . $buyer->last_name }}</span>
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
                            <a class="btn btn-primary w-100 mt-2" href="{{route('create_chat_room',['conversation_id'=>$buyer->username])}}">Message Buyer</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade modal-lg" id="deliverModal" data-bs-backdrop="static" data-bs-keyboard="false"
                 tabindex="-1" aria-labelledby="deliverModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="delivery-form" class="needs-validation" action="{{ route("seller.service.order.deliver") }}"
                            method="post" enctype="multipart/form-data" novalidate>
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="deliverModalLabel">Deliver Service</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        @csrf
                                        <div class="card col-md-12 mb-4">
                                            <div class="card-body">
                                                <input type="hidden" name="order_id" id="order_id" value="{{ $order->id }}">
                                                <div class="mb-3">
                                                    <label for="message" class="form-label required">Message</label>
                                                    <textarea name="message" id="message" rows="2" class="form-control" placeholder="Leave a note for the buyer (Ex. Thanks for your order)" required>{{ old('message') }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card mb-4">
                                            <div class="card-header card-header-content-between">
                                                <label class="card-header-title mb-0 required">Attach Files</label>
                                            </div>
                                            <div class="card-body required">
                                                <div class="text-primary mb-2">Select the files(.3dm, .stl, .zip, .rar) to deliver to the buyer. Max 500mb.</div>
                                                <input type="hidden" class="attach" id="attach" name="attach" value="">
                                                <div id="attach_container">
                                                    <div class="dropzone invalid" id="attach-dropzone"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card mb-4">
                                            <div class="card-header card-header-content-between">
                                                <label class="card-header-title mb-0">Featured Image</label>
                                            </div>
                                            <div class="card-body">
                                                <div class="text-primary mb-2">Select a image (.jpeg,.jpg,.png) that will show on the the buyer's review if they allow it.</div>
                                                <input type="hidden" class="attach" id="attach_image" name="attach_image" value="">
                                                <div id="attach_container">
                                                    <div class="dropzone invalid" id="attach-dropzone-image"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Submit Delivery</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
    @section('js')
            <script src="{{ asset('dropzone/js/dropzone.js') }}"></script>
            
            @if ($remainingTime > 0 && $remainingTime <= 48 * 60 * 60)
            <script>
            document.getElementById("extendDeliveryBtn").addEventListener("click", function() {
            document.getElementById("extendDeliveryForm").style.display = "block";
            });

            $('#requestExtensionBtn').click(function() {
            // Perform your submission logic here
            var selectedDays = $('#daysInput').val();
            console.log('Selected Days:', selectedDays);

            // Get the values from the hidden inputs
            var orderId = $('#orderIdInput').val();
            var buyerId = $('#buyerIdInput').val();

            // Create an object with the form data
            var formData = {
                order_id: orderId,
                buyer_id: buyerId,
                days: selectedDays,
                message: null
            };

            // Get the CSRF token value from the meta tag
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            // Set the CSRF token value in the headers
            $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': csrfToken
                }
            });

            // Make an AJAX request to submit the form data
            $.ajax({
                url: '/services/extend-service-delivery', // Replace with your actual route URL
                type: 'POST',
                dataType: 'json',
                data: formData,
                success: function(response) {
                // Handle the success response
                console.log(response.message);
                // You can perform any additional actions or show a success message here
                $('#requestMessage').text(response.message);
                $('#requestMessage').toggleClass('d-none', false);
                },
                error: function(xhr, status, error) {
                // Handle the error response
                console.log(error);
                // You can display an error message or perform other error handling here
                }
            });
            });
            </script>
            @endif

            <script>
            // Assuming $order->original_delivery_time format is like "2023-12-31 23:59:59"
            var dateTimeParts = "{{ $order->original_delivery_time }}".split(" ");
            var dateParts = dateTimeParts[0].split("-");
            var timeParts = dateTimeParts[1].split(":");

            var year = parseInt(dateParts[0]);
            var month = parseInt(dateParts[1]) - 1; // months are 0-based in JavaScript
            var day = parseInt(dateParts[2]);

            var hour = parseInt(timeParts[0]);
            var minute = parseInt(timeParts[1]);
            var second = parseInt(timeParts[2]);

            var countDownDate = new Date(year, month, day, hour, minute, second).getTime();

            function padLeadingZeros(num, size) {
                var s = num.toString();
                while (s.length < size) s = "0" + s;
                return s;
            }

            function calculateCountdown() {
                // Get today's date and time
                var now = new Date().getTime();

                // Find the distance between now and the count down date
                var distance = countDownDate - now;

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

            // Call function immediately
            calculateCountdown();

            // Then set interval
            var x = setInterval(calculateCountdown, 1000);


            Dropzone.autoDiscover = false;
            var uploadedFileData = [];
            $(document).ready(function () {
                $("#attach-dropzone").dropzone({
                    method: 'post',
                    url: "{{ route('seller.file.store') }}",
                    dictDefaultMessage: "Select File",
                    paramName: "file",
                    maxFilesize: 500 * 1024 * 1024,
                    clickable: true,
                    addRemoveLinks: true,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    },
                    success: function (file, response) {
                        var attachInput = $('#attach');
                        var inputDiv = $("#attach-dropzone");
                        var lastFiles = attachInput.val() ? attachInput.val().split(',') : [];
                        lastFiles.push(response.id);

                        attachInput.val(lastFiles.join(','));
                        uploadedFileData.push(response);
                        inputDiv.removeClass("invalid").removeClass("valid").addClass("valid");
                    },
                    removedfile: function (file) {
                        var answerInput = $('#attach');
                        var inputDiv = $("#attach-dropzone");
                        for (var i = 0; i < uploadedFileData.length; ++i) {
                        if (!uploadedFileData[i]) {
                            continue;
                        }
                        if (uploadedFileData[i].file_original_name + "." + uploadedFileData[i].extension == file.name) {
                            $.ajax({
                            url: `/seller/file/destroy/${uploadedFileData[i].id}`,
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            },
                            success: function (result) {
                                var lastValue = answerInput.val().split(',');
                                var removed = lastValue.filter((item) => item != uploadedFileData[i].id);
                                answerInput.val(removed);
                                $(file.previewElement).remove();
                                uploadedFileData.splice(i, 1)

                                if (removed.length == 0) {
                                inputDiv.removeClass("invalid").removeClass("valid").addClass("invalid");
                                }
                            },
                            error: function (error) {
                                return false;
                            }
                            });
                            break;
                        }
                        }
                    }
                });

                $("#attach-dropzone-image").dropzone({
                    method: 'post',
                    url: "{{ route('seller.file.image') }}",
                    dictDefaultMessage: "Select File",
                    paramName: "file",
                    maxFilesize: 5,
                    maxFiles: 1,
                    acceptedFiles: ".jpeg,.jpg,.png",
                    clickable: true,
                    addRemoveLinks: true,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    },
                    success: function (file, response) {
                        var attachInput = $('#attach_image');
                        var inputDiv = $("#attach-dropzone-image");
                        var lastFiles = attachInput.val() ? attachInput.val().split(',') : [];
                        lastFiles.push(response.id);

                        attachInput.val(lastFiles.join(','));
                        uploadedFileData.push(response);
                        inputDiv.removeClass("invalid").removeClass("valid").addClass("valid");
                    },
                    removedfile: function (file) {
                        var answerInput = $('#attach_image');
                        var inputDiv = $("#attach-dropzone-image");
                        for (var i = 0; i < uploadedFileData.length; ++i) {
                        if (!uploadedFileData[i]) {
                            continue;
                        }
                        if (uploadedFileData[i].file_original_name + "." + uploadedFileData[i].extension == file.name) {
                            $.ajax({
                            url: `/seller/file/destroy/${uploadedFileData[i].id}`,
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            },
                            success: function (result) {
                                var lastValue = answerInput.val().split(',');
                                var removed = lastValue.filter((item) => item != uploadedFileData[i].id);
                                answerInput.val(removed);
                                $(file.previewElement).remove();
                                uploadedFileData.splice(i, 1)

                                if (removed.length == 0) {
                                inputDiv.removeClass("invalid").removeClass("valid").addClass("invalid");
                                }
                            },
                            error: function (error) {
                                return false;
                            }
                            });
                            break;
                        }
                        }
                    }
                });

                $('#delivery-form').submit(function (event) {
                if ($(this).find('.required .invalid').length) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                $(this).addClass('was-validated');
                })
            });

            const deliverModal = document.getElementById('deliverModal')
            const messageInput = document.getElementById('message')

            deliverModal.addEventListener('shown.bs.modal', () => {
                messageInput.focus()
            })
            </script>
    @endsection

</x-app-layout>
