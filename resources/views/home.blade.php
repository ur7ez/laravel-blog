<?php
/** @var $posts \Illuminate\Pagination\LengthAwarePaginator */
?>

<x-app-layout meta-title="URCode Blog"
              meta-description="URCode personal blog about coding tutorials">
    <div class="container max-w-4xl mx-auto py-6">

        <!-- Posts Section -->
        <section class="w-full md:w-2/3 px-3">
            <div class="flex flex-col items-center">
                @foreach($posts as $post)
                    <x-post-item :post="$post"/>
                @endforeach
            </div>
            {{$posts->links()}}
        </section>

        <!-- Sidebar Section -->
        <x-sidebar/>

    </div>
</x-app-layout>
