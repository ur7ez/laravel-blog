<?php
use App\Models\Comment;
use App\Models\Post;

/** @var Comment $comment */
/** @var Post $post */
?>

<div class="pt-4">
    <livewire:comment-create :post="$post"/>

    @foreach($comments as $comment)
        <livewire:comment-item :comment="$comment" wire:key="comment-{{ $comment->id }}"/>
    @endforeach

</div>
