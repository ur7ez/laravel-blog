<?php

use App\Models\Post;

/** @var Post[] $posts */
/** @var int $postsTotal */
?>

<x-app-layout meta-title='Search results'
              meta-description='Posts filtered by search phrase'>
    <div class="container mx-auto flex flex-wrap py-4">
        <!-- Posts Section -->
        <section class="w-full md:w-2/3 px-3">
            <div class="flex flex-col">
                @foreach($posts as $post)
                    <div class="">
                        <a href="{{route('view', $post)}}" title="click to read more">
                            <h2 class="text-blue-500 font-bold text-lg sm:text-xl mb-2">
                                {!! $post->highlightInTitle(request()->get('q')) !!}
                            </h2>
                        </a>
                        <div class="">
                            {{$post->shortBody()}}
                        </div>
                    </div>
                    @if ($postsTotal > 1)
                        <hr class="my-4">
                    @endif
                @endforeach
            </div>
            {{ $posts->links() }}
        </section>
        <!-- Sidebar Section -->
        <x-sidebar/>
    </div>
</x-app-layout>
