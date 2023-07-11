<x-app-layout>
  <x-slot name="header">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ __('Dashboard') }}
      </h2>
  </x-slot>
  <div class="py-9">
      <div class="container">
      <!--
          <div class="seller-dash-nav mb-4">
              <ul class="nav nav-pills">
                  <li class="nav-item">
                      <a class="nav-link {{ \Route::currentRouteName() == 'seller.dashboard' ? 'active' :'' }}" href="{{ route('seller.dashboard') }}">Seller Dashboard</a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link {{ \Route::currentRouteName() == 'dashboard' ? 'active' :'' }}" href="{{ route('dashboard') }}">User Dashboard</a>
                  </li>
              </ul>
          </div>
          -->
          <div class="row">
              <div class="col-lg-3">
                  <x-dashboard-side-bar />
              </div>
              <div class="col-lg-9">
                  @if (session('success'))
                    <div class="alert alert-success">
                        {{session('success')}}
                    </div>
                  @endif
                  @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                  @endif
                  <div class="seller-stats">
                      <div class="seller-stats-card-body">
                          <div class="row">
                              <div class="col-md-3 mb-4">
                                  <div class="card m-0">
                                      <div class="card-header blance-title">Available To Withdraw</div>
                                      <div class="card-body">
                                          <p class="fw-bold">$ {{ number_format($withdrawable/100, 2, ".", ",") }}</p>
                                      </div>
                                  </div>
                              </div>
                              <div class="col-md-3 mb-4">
                                  <div class="card m-0 h-100">
                                      <div class="card-header blance-title">Wallet</div>
                                      <div class="card-body">
                                          <p class="fw-bold">$ {{ number_format($seller->wallet/100, 2, ".", ",") }}</p>
                                      </div>
                                  </div>
                              </div>
                              <div class="col-md-3 mb-4">
                                  <div class="card m-0 h-100">
                                      <div class="card-header blance-title">Total Earned</div>
                                      <div class="card-body">
                                          <p class="fw-bold">$ {{ number_format($totalEarned/100, 2, ".", ",") }}</p>
                                      </div>
                                  </div>
                              </div>
                              <div class="col-md-3"></div>
                          </div>
                          <form action="{{ route('seller.withdraw.post') }}" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-12">
                                    @csrf
                                    <div class="card col-md-12 mb-4">
                                        <!-- Header -->
                                        <div class="card-header">
                                            <h4 class="card-header-title mb-0">Withdraw Money</h4>
                                        </div>
                                        <!-- End Header -->
                                        <div class="card-body">
                                            @if ($payment_details)
                                            <div class="mb-4">
                                                <label for="amount" class="w-100 mb-2">Amount:</label>
                                                <input type="number" name="amount" id="amount" step="0.01" min="0.01" value="{{ old('amount') }}" class="form-control" required>    
                                                @if ($payment_methods && $payment_methods->count() > 0)
                                                <input type="hidden" name="method" value="{{ $payment_methods->first()->name }}">
                                                <input type="hidden" name="question_1" value="{{ $payment_details->question_1 }}">
                                                <input type="hidden" name="question_2" value="{{ $payment_details->question_2 }}">
                                                <input type="hidden" name="question_3" value="{{ $payment_details->question_3 }}">
                                                <input type="hidden" name="question_4" value="{{ $payment_details->question_4 }}">
                                                @else
                                                <p>No payment method found.</p>
                                                @endif

                                            </div>
                                   
                                            @if ($payment_methods && $payment_methods->count() > 0)
                                                <div class="fw-600 mb-2">Payment Details</div>
                                                <div class="mb-2">
                                                    <div class="fw-600 mb-2 w-100">Payment Method Name</div>
                                                    {{ $payment_methods->first()->name }}
                                                </div>
                                                <div class="mb-2">
                                                    <div class="fw-600 mb-2 w-100">{{ $payment_methods->first()->question_1 }}</div> 
                                                    {{ $payment_details->question_1 }}
                                                </div>
                                                <div class="mb-2">
                                                    <div class="fw-600 mb-2 w-100">{{ $payment_methods->first()->question_2 }}</div> 
                                                    {{ $payment_details->question_2 }}
                                                </div>
                                                <div class="mb-2">
                                                    <div class="fw-600 mb-2 w-100">{{ $payment_methods->first()->question_3 }}</div> 
                                                    {{ $payment_details->question_3 }}
                                                </div>
                                                <div class="mb-2">
                                                    <div class="fw-600 mb-2 w-100">{{ $payment_methods->first()->question_4 }}</div> 
                                                    {{ $payment_details->question_4 }}
                                                </div>
                                            @else
                                                <p>No payment method found.</p>
                                            @endif

                                            <button type="submit" class="btn btn-primary mt-4">Withdraw</button>
                                            @else
                                                <div class="alert alert-info" role="alert">Please add your payment details to withdraw earnings.</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                          </form>
                      </div>
                  </div>
              </div> <!-- end .col-9 -->
          </div> <!-- .row -->
      </div>
  </div>
</x-app-layout>
