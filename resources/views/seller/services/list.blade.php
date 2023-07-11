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
                            <a class="btn btn-primary" href="{{route('seller.services.create')}}">Create A New Service</a>
                        </div>
                        <div class="card-body">
                            @foreach ($services as $service)
                                @if ($service->status != 5)
                                    <div class="card mb-4">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $service->name }}</h5>
                                            <p class="card-text">
                                                Status: 
                                                @if ($service->status == 0)
                                                    Draft
                                                @elseif ($service->status == 1)
                                                    Active
                                                @elseif ($service->status == 2)
                                                    Pending
                                                @elseif ($service->status == 3)
                                                    Paused
                                                @elseif ($service->status == 4)
                                                    Requires Changes
                                                @endif
                                            </p>
                                            <p class="card-text">
                                                Categories:
                                                @foreach($service->categories as $category_info)
                                                    <span>{{$category_info->category->category_name}}</span> 
                                                @endforeach
                                            </p>
                                            <a href="{{ route('seller.services.edit', $service->id) }}" class="btn btn-dark">Edit</a>
                                            <a href="#" class="btn btn-dark">Pause</a>
                                            <a onclick="return confirm('Are you sure you want to delete this service?')" href="{{route('seller.services.delete', $service->id)}}" class="btn btn-danger">Delete</a>
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
