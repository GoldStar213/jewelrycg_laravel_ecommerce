<x-app-layout page-title="{{ $post->meta_title?$post->meta_title:$post->name }} | Jewelry CG" page-description="{{$post->meta_description}}">
    <section class="bg-white py-9">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mx-auto">
                    <div class="blog-post-single-container">
                        <div class="border-bottom mb-2">
                            <h1 class="text-black fw-700 article-single-title">{{ $post->name }}</h1>
                            <div class="mb-2 article-single-category">
                                    Published in 
                                    @foreach($post->categories as $key => $category_info)
                                    <a href="#" class="text-primary fw-600">{{$category_info->category->category_name}}</a>
                                    @if($key < count($post->categories) - 1) , @endif
                                    @endforeach
                            </div>
                        </div>
                        <div class="mb-4 article-single-post overflow-hidden">
                            {!! $post->post !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
