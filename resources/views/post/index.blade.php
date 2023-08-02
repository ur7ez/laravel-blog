<?php
/** @var $posts \Illuminate\Pagination\LengthAwarePaginator */
/** @var $category App\Models\Category */
?>

<x-app-layout :meta-title="'URCode\'s Blog | Posts by category ' . $category->title" meta-description="By category description">
    <!-- Posts Section -->
    <section class="w-full md:w-2/3 flex flex-col items-center px-3">
        @foreach($posts as $post)
            <x-post-item :post="$post"></x-post-item>
        @endforeach
        {{$posts->onEachSide(1)->links()}}
    </section>
    <x-sidebar></x-sidebar>
</x-app-layout>
