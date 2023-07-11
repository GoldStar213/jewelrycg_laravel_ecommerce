<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    <meta name="_token" content="{{ csrf_token() }}" />
    <div class="py-9">
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <x-dashboard-side-bar />
                </div>
                <div class="col-xl-4 col-lg-6 col-md-8 mr-auto">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('seller.payment.details.post') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="card mb-4">
                            <div class="card-header">Payment Details</div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <label for="business_name">Payment Method:</label>
                                    <select class="form-select" id="payment" name="payment_method_id">
                                        <option>-- Select --</option>
                                        @foreach ($payment_methods as $method)
                                            <option value="{{ $method->id }}" {{ $payment_details && $payment_details->payment_method_id == $method->id ? 'selected' : '' }}>{{ $method->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-2" id="question_1_container" style="display: none;">
                                    <label for="question_1" id="label_1"></label>
                                    <input class="form-control" placeholder="" type="text" name="question_1" value="{{ $payment_details ? $payment_details->question_1 : '' }}" />
                                </div>
                                <div class="mb-2" id="question_2_container" style="display: none;">
                                    <label for="question_2" id="label_2"></label>
                                    <input class="form-control" placeholder="" type="text" name="question_2" value="{{ $payment_details ? $payment_details->question_2 : '' }}" />
                                </div>
                                <div class="mb-2" id="question_3_container" style="display: none;">
                                    <label for="question_3" id="label_3"></label>
                                    <input class="form-control" placeholder="" type="text" name="question_3" value="{{ $payment_details ? $payment_details->question_3 : '' }}" />
                                </div>
                                <div class="mb-2" id="question_4_container" style="display: none;">
                                    <label for="question_4" id="label_4"></label>
                                    <input class="form-control" placeholder="" type="text" name="question_4" value="{{ $payment_details ? $payment_details->question_4 : '' }}" />
                                </div>

                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div><!-- end .row -->
        </div>
    </div>

    @section('js')
        <script>
            var payment_methods = <?php echo json_encode($payment_methods); ?>;
            var payment_details = <?php echo json_encode($payment_details); ?>;

            $(document).ready(function() {
                $("#payment").change(function() {
                    var val = $(this).val();

                    // Hide all question inputs by default
                    $("#question_1_container").hide();
                    $("#question_2_container").hide();
                    $("#question_3_container").hide();
                    $("#question_4_container").hide();

                    if (val !== "-- Select --") {
                        // Show the corresponding question input based on the selected payment method
                        var method = payment_methods.find(function(method) {
                            return method.id == val;
                        });

                        if (method.question_1) {
                            $("#question_1_container").show();
                            $("#label_1").text(method.question_1);
                        }

                        if (method.question_2) {
                            $("#question_2_container").show();
                            $("#label_2").text(method.question_2);
                        }

                        if (method.question_3) {
                            $("#question_3_container").show();
                            $("#label_3").text(method.question_3);
                        }

                        if (method.question_4) {
                            $("#question_4_container").show();
                            $("#label_4").text(method.question_4);
                        }
                    }
                });

                // Preselect the payment method and trigger the change event to show the corresponding question inputs
                if (payment_details) {
                    $("#payment").val(payment_details.payment_method_id).trigger("change");
                }
            });
        </script>
    @endsection
</x-app-layout>
