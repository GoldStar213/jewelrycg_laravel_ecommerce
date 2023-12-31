<x-app-layout page-title="Course Orders | Jewelry CG">
  <div class="container">
      <div class="col-lg-8 col-md-10 pt-9 pb-8 mx-auto checkout-wrap">
          <h1 class="fw-800">Order history</h1>
          <p class="pb-4">Check the status of recent orders, view order details, and fufilment status.</p>
          
          <ul class="nav nav-pills mb-5">
              <li class="nav-item">
                  <a class="nav-link {{ \Route::currentRouteName() == 'orders.index' ? 'active' :'' }}" href="{{ route('orders.index') }}">Product Orders</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link {{ \Route::currentRouteName() == 'services.orders' ? 'active' :'' }}" href="{{ route('services.orders') }}">Service Orders</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link {{ \Route::currentRouteName() == 'courses.orders' ? 'active' :'' }}" href="{{ route('courses.orders') }}">Course Orders</a>
              </li>
          </ul>
  
          @foreach ($orders as $order)
          @if ($order->status_payment == 2)
          <div class="card">
              <div class="card-body">
                  <div class="row">
                      <div class="col-lg-2 col-6 mb-2">
                          <div class="fw-600">Order number</div>
                          <div>#{{$order->order_id}}</div>
                      </div>
                      <div class="col-lg-2 col-6 mb-2">
                          <div class="fw-600">Date placed</div>
                          <span>{{ date('F d, Y', strtotime($order->created_at)) }}</span>
                      </div>
                      <div class="col-lg-2 col-6 mb-2">
                          <div class="fw-600">Course</div>
                          <span>{{$order->course->name}}</span>
                      </div>
                      <div class="col-lg-2 col-6 mb-2">
                          <div class="fw-600">Total amount</div>
                          <span>${{number_format($order->price/100, 2)}}</span>
                      </div>
                      <div class="col-lg-2 col-6 ml-auto">
                          <a href="{{route('courses.order_detail', $order->order_id)}}" class="btn btn-primary">View Order</a>
                      </div>
                  </div>
              </div>
              <div class="card-body">

              </div>
          </div>
          @endif
          @endforeach
          {{$orders->links()}}
      </div>
  </div>
  </x-app-layout>
