<x-app-layout page-title="Courses | Jewelry CG">
<section class="border-bottom pt-9 pb-5 mb-6">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h1 class="fw-600 h4">Courses</h1>
            </div>
            <div class="col-lg-12">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="/">Home</a>
                        
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="/courses">"Courses"</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>


    <section class="bg-white pb-0">
        <div class="container">
            <div class="section-page-content col-xl-11 mx-auto">
                <div class="row row-cols-lg-4 row-cols-md-3 row-cols-2">
                    @foreach ($arrCourses as $course)
                        @if ($course->status == 1)
                        @php
                            $course->setPriceToFloat()
                        @endphp
                        <div class="col mb-3">
                            <div class="blog-post-list-container">
                                <a href="{{ route('courses.show', $course->slug) }}" class="text-reset d-block">
                                    @if($course->uploads->file_name == 'none.png')
                                        <img src="{{ asset('assets/img/placeholder.jpg') }}" alt="{{ $course->name }}" class="border lazyloaded rounded w-100">
                                    @else
                                        <img src="{{$course->uploads->getImageOptimizedFullName(360,600)}}" alt="{{ $course->name }}" class="border lazyloaded rounded w-100">
                                    @endif
                                </a>
                                <div class="p-2 pt-3">
                                    <h2 class="fs-18 fw-600 mb-2">
                                        <a href="{{ route('courses.show', $course->slug) }}" class="text-reset article-list-title">
                                            {{ $course->name }}
                                        </a>
                                    </h2>
                                    <div class="mb-2 opacity-50 article-list-category">
                                        <span>$ {{ $course->price }} </span>
                                    </div>
                                
                                    <div class="mb-2 opacity-50 article-list-category">
                                        Published in: 
                                        <a href="{{ route('courses.category', $course->category->slug) }}">
                                            {{$course->category_name}}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
    
