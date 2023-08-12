<?php

use Illuminate\Support\Str;

/** @var \App\Models\Post $post */
/** @var \App\Models\Post $next */
/** @var \App\Models\Post $prev */
?>

<x-app-layout :meta-title="$post->meta_title ?: $post->title" :meta-description="$post->meta_description">
    <div class="flex">
        <!-- Post Section -->
        <section class="w-full md:w-2/3 px-3">
            <article class="bg-white flex flex-col shadow my-4">
                <!-- Article Image -->
                <a href="javascript:void(0)" role="button">
                    <img src="{{$post->getThumbnail()}}" class="hover:opacity-75" alt="">
                </a>
                <div class="bg-white flex flex-col justify-start p-6">
                    <div class="flex gap-4">
                        @foreach($post->categories as $category)
                            <a href="{{route('by-category', $category)}}"
                               class="text-blue-700 text-sm font-bold uppercase pb-4">
                                {{$category->title}}
                            </a>
                        @endforeach
                    </div>
                    <h1 class="text-3xl font-bold hover:text-gray-700 pb-4">
                        {{ $post->title }}
                    </h1>
                    <p href="#" class="text-sm pb-8">
                        By <a href="#" class="font-semibold hover:text-gray-800">{{ $post->user->name }}</a>, Published on {{$post->getFormattedDate()}} | {{ $post->human_read_time }}
                    </p>
                    <div class="pb-4">
                        {!! $post->body !!}
                    </div>
                    <!-- Votes Section -->
                    <livewire:upvote-downvote :post="$post"/>
                </div>
            </article>

            <div class="w-full flex">
                <div class="flex w-1/2 mr-3">
                    @if($prev)
                        <a href="{{route('view', $prev)}}"
                           class="block w-full bg-white shadow hover:shadow-md text-left p-6">
                            <p class="text-lg text-blue-800 font-bold flex items-center">
                                <i class="fas fa-arrow-left pr-1"></i>
                                Previous
                            </p>
                            <p class="pt-2">{{Str::words($prev->title, 5)}}</p>
                        </a>
                    @endif
                </div>
                <div class="w-1/2">
                    @if($next)
                        <a href="{{route('view', $next)}}"
                           class="block w-full bg-white shadow hover:shadow-md text-right p-6">
                            <p class="text-lg text-blue-800 font-bold flex items-center justify-end">
                                Next
                                <i class="fas fa-arrow-right pl-1"></i>
                            </p>
                            <p class="pt-2">{{Str::words($next->title, 5)}}</p>
                        </a>
                    @endif
                </div>
            </div>

            <livewire:comments :post="$post"/>
        </section>

        <x-sidebar/>
    </div>
</x-app-layout>
