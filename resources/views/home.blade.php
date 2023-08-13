<?php

use App\Models\Category;
use \App\Models\Post

/** @var $latestPost Post */
/** @var $popularPosts Post[] */
/** @var $recommendedPosts Post[] */
/** @var $categories Category[] */
?>

<x-app-layout meta-title=""
              meta-description="URCode personal blog about coding tutorials">
    <div class="container max-w-4xl mx-auto">

        <div class="grid grid-col-1 md:grid-cols-3 gap-x-8 mb-8">
            <!-- Latest Post -->
            <div class="col-span-2">
                <h2 class="text-lg sm:text-xl font-bold text-blue-500 uppercase pb-1 border-b-2 border-blue-500 mb-3">
                    Latest Post
                </h2>
                @if ($latestPost)
                    <x-post-item :post="$latestPost"/>
                @endif
            </div>

            <!-- Popular Posts -->
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-blue-500 uppercase pb-1 border-b-2 border-blue-500 mb-3">
                    Popular Posts
                </h2>
                @foreach($popularPosts as $post)
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        <a href="{{route('view', $post)}}" class="pt-1">
                            <img src="{{ $post->getThumbnail() }}" alt="{{$post->title}}">
                        </a>
                        <div class="col-span-3">
                            <a href="{{route('view', $post)}}" title="{{$post->title}}">
                                <h3 class="text-sm uppercase whitespace-nowrap truncate">{{$post->title}}</h3>
                            </a>
                            <div class="flex gap-4 mb-2">
                                @foreach($post->categories as $category)
                                    <a href="{{route('by-category', $category)}}"
                                       class="bg-blue-500 text-white p-1 rounded text-xs font-bold uppercase">
                                        {{$category->title}}
                                    </a>
                                @endforeach
                            </div>
                            <div class="text-xs">
                                {{$post->shortBody(10)}}
                            </div>
                            <a href="{{route('view', $post)}}" class="text-xs uppercase text-gray-800 hover:text-black">
                                Continue Reading <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recommended Posts -->
        <div class="mb-8">
            <h2 class="text-lg sm:text-xl font-bold text-blue-500 uppercase pb-1 border-b-2 border-blue-500 mb-3">
                Recommended Posts
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @foreach($recommendedPosts as $post)
                    <x-post-item :post="$post" :show-author="false"/>
                @endforeach
            </div>
        </div>

        <!-- Latest Categories -->
        @foreach($categories as $category)
            <div>
                <h2 class="uppercase text-blue-500 text-lg sm:text-xl font-bold pb-1 border-b-2 border-blue-500 mb-3">
                    Category
                    <a href="{{route('by-category', $category)}}" class="">
                        "{{$category->title}}" <i class="fas fa-arrow-right"></i>
                    </a>
                </h2>

                <div class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @foreach($category->publishedPosts()->get() as $post)
                            <x-post-item :post="$post" :show-author="false"/>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach

    </div>
</x-app-layout>
