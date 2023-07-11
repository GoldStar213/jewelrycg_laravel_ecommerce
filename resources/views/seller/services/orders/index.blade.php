<x-app-layout>
  <x-slot name="header">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ __('Dashboard') }}
      </h2>
  </x-slot>

  <div class="py-9">
      <div class="container">
          <div class="row">
                <div class="col-lg-3">
                    <x-dashboard-side-bar />
                </div>
                <div class="col-lg-9">
                    <div class="card">
                        <div class="card-header">
                            <div class="dropdown">
                                <button class="btn btn-lg btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ ucwords($tab) }}
                                </button>
                                <ul class="dropdown-menu p-2" aria-labelledby="dropdownMenuButton">
                                    <li><a class="dropdown-item rounded p-2 {{ $tab == "active" ? "active" : "" }}" href="?tab=active">Active</a></li>
                                    <li><a class="dropdown-item rounded p-2 {{ $tab == "late" ? "active" : "" }}" href="?tab=late">Late</a></li>
                                    <li><a class="dropdown-item rounded p-2 {{ $tab == "delivered" ? "active" : "" }}" href="?tab=delivered">Delivered</a></li>
                                    <li><a class="dropdown-item rounded p-2 {{ $tab == "completed" ? "active" : "" }}" href="?tab=completed">Completed</a></li>
                                    <li><a class="dropdown-item rounded p-2 {{ $tab == "canceled" ? "active" : "" }}" href="?tab=canceled">Cancelled</a></li>
                                </ul>
                            </div>

                        </div>
                        <div class="card-body">
                            @foreach ($orders as $order)
                            @if ($order->status_payment == 2)
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Buyer: {{ $order->user->first_name }} {{ $order->user->last_name }}</h5>
                                    <p class="card-text">Service Name - Package: {{ $order->service->name . " - " . ($order->package_name) }}</p>
                                    <p class="card-text">Price: ${{ number_format($order->package_price / 100, 2) }}</p>
                                    <p class="card-text">Status: 
                                        @if ($order->status == 0)
                                            Pending
                                        @elseif ($order->status == 1)
                                            In Progress
                                        @elseif ($order->status == 2)
                                            Revision
                                        @elseif ($order->status == 3)
                                            Canceled
                                        @else
                                            Delivered
                                        @endif
                                    </p>
                                    <p class="card-text">Delivery Date: {{ $order->status == 0 ? "-" : date('F d, Y h:i A', strtotime($order->original_delivery_time)) }}</p>
                                    <a href="{{ route('seller.service.order.detail', $order->order_id) }}" class="btn btn-dark">View</a>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                </div>
          </div>
      </div>
  </div>
</x-app-layout>

@section('js')
  <script>
  </script>
@endsection
