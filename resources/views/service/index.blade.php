<x-app-layout page-title="Services | Jewelry CG">
  <x-slot name="header">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ __('Dashboard') }}
      </h2>
  </x-slot>
<section class="border-bottom pt-9 pb-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h1 class="fw-600 h4">Services</h1>
            </div>
            <div class="col-lg-6 mx-auto text-center">
                <p>Unleash your creative potential by connecting with top-notch professionals skilled in CAD design, marketing, web development, and more.</p>
            </div>
        </div>
    </div>
</section>


  <div class="py-8">
      <div class="container">
          <div class="row">
              <div class="col-lg-12">
                <div class="row row-cols-xxl-4 row-cols-xl-4 row-cols-lg-4 row-cols-md-2 row-cols-1">
                  @foreach ($services as $service)
                  	@php
						$user = Auth::user();
						if (Auth::user()->isBlocked($service->postauthor->id) == true) {
							continue;
						}
                  	@endphp
                  <div class="col mb-2 mb-lg-0">
                    <div class="card">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-12 mb-2">
                            <a href="/services/{{$service->slug}}" class="">
                              <img src="{{ $service->uploads->getImageOptimizedFullName(800,600) }}" class="rounded w-100 border" alt="{{ $service->name }}">
                            </a>
                          </div>
                          <div class="col-12 text-left">
                            <div class="fs-20 fw-500 mb-2">
                              <a href="/services/{{$service->slug}}" class="mt-2 text-black">{{ $service->name }}</a>
                            </div>
                            @foreach ($service->categories as $item)
                            <div class="fs-14 mb-2 fw-700">{{ $item->category->category_name }}</div>
                            @endforeach

                            @if ($service->count > 0)
                              <span><i class="bi bi-star-fill fs-18 text-blue"></i> {{ $service->rating ?: "0.0" }}</span>
                              <span class="text-secondary">({{$service->count}})</span>
                            @endif
                            
                          </div>
                        </div>
                      </div>
                      <div class="card-footer border-top bg-white">
                      <div class="row align-items-center">
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                              <div class="mr-5px">
                                <img class="w-40px rounded border" src="{{ $service->postauthor->uploads->getImageOptimizedFullName(200,200) }}" alt="{{ $service->postauthor->first_name }}">
                              </div>
                              <a href="u/{{ $service->postauthor->username }}" class="text-black fs-14 fw-600">{{ $service->postauthor->username }}</a>
                            </div>
                        </div>
                        <div class="col-6">
                          <div class="text-right">Starting at <span class="fw-700 fs-18 text-primary">{{ count($service->packages) ? "$".($service->packages[0]->price / 100) : "..." }}</span></div>
                        </div>
                      </div>
                      </div>
                    </div>
                  </div>
                  @endforeach
                </div>
              </div>
          </div>
      </div>
  </div>
</x-app-layout>
