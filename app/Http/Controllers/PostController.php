<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostView;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function home(): View
    {
        // Latest post
        $latestPost = Post::where('active', '=', 1)
            ->whereDate('published_at', '<=', Carbon::now())
            ->orderBy('published_at', 'desc')
            ->limit(1)
            ->first();

        // Show the most popular X posts based on upvotes
        $popularPosts = Post::query()
            ->leftJoin('upvote_downvotes', 'posts.id', '=', 'upvote_downvotes.post_id')
            ->select('posts.*', DB::raw('COUNT(upvote_downvotes.id) as upvote_count'))
            ->where(function ($query) {
                $query
                    ->whereNull('upvote_downvotes.is_upvote')
                    ->orWhere('upvote_downvotes.is_upvote', '=', 1);
            })
            ->where('active', '=', 1)
            ->whereDate('published_at', '<=', Carbon::now())
            ->orderByDesc('upvote_count')
            ->groupBy('posts.id')
            ->limit(5)
            ->get();

        // Get recommended posts:
        $user = auth()->user();
        if ($user) {
            // If authorized - show recommended posts based on user upvotes
            $leftJoin = '(SELECT cp.category_id, cp.post_id
                FROM upvote_downvotes
                JOIN category_post cp ON upvote_downvotes.post_id = cp.post_id
                WHERE upvote_downvotes.is_upvote = 1
                    AND upvote_downvotes.user_id = ?) AS t1';

            $recommendedPosts = Post::query()
                ->leftJoin('category_post as cp', 'posts.id', '=', 'cp.post_id')
                ->leftJoin(DB::raw($leftJoin), function ($join) {
                    $join->on('t1.category_id', '=', 'cp.category_id')
                        ->on('t1.post_id', '<>', 'cp.post_id');
                })
                ->setBindings([$user->id])
                ->select('posts.*')
                ->where('active', '=', 1)
                ->whereDate('published_at', '<=', Carbon::now())
                ->where('posts.id', '<>', DB::raw('t1.post_id'))
                ->limit(3)
                ->get();
        } else {
            // Not authorized - popular posts based on views
            $recommendedPosts = Post::query()
                ->leftJoin('post_views', 'posts.id', '=', 'post_views.post_id')
                ->select('posts.*', DB::raw('COUNT(post_views.id) as view_count'))
                ->where('active', '=', 1)
                ->whereDate('published_at', '<=', Carbon::now())
                ->orderByDesc('view_count')
                ->groupBy('posts.id')
                ->limit(3)
                ->get();
        }

        // Show recent categories with their latest posts
        $categories = Category::query()
            ->whereHas('posts', function ($query) {
                $query
                    ->where('active', '=', 1)
                    ->whereDate('published_at', '<=', Carbon::now());
            })
            ->select('categories.*')
            ->selectRaw('MAX(posts.published_at) as max_date')
            ->leftJoin('category_post', 'categories.id', '=', 'category_post.category_id')
            ->leftJoin('posts', 'posts.id', '=', 'category_post.post_id')
            ->orderByDesc('max_date')
            ->groupBy([
                'categories.id',
                'categories.title',
                'categories.slug',
                'categories.created_at',
                'categories.updated_at',
            ])
            ->limit(5)
            ->get();

        return view('home', compact(
            'latestPost',
            'popularPosts',
            'recommendedPosts',
            'categories'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     * @param Post $post
     * @param Request $request
     * @return Application|Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function show(Post $post, Request $request)
    {
        if (!$post->active || $post->published_at > Carbon::now()) {
            throw new NotFoundHttpException();
        }

        $next = Post::where('active', true)
            ->whereDate('published_at', '<=', Carbon::now())
            ->whereDate('published_at', '<', $post->published_at)
            ->orderBy('published_at', 'desc')
            ->limit(1)
            ->first();

        $prev = Post::where('active', true)
            ->whereDate('published_at', '<=', Carbon::now())
            ->whereDate('published_at', '>', $post->published_at)
            ->orderBy('published_at', 'asc')
            ->limit(1)
            ->first();

        $user = $request->user();
        PostView::create([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'post_id' => $post->id,
            'user_id' => $user?->id,
        ]);

        return view('post.view', compact('post', 'prev', 'next'));
    }

    /**
     * @param Category $category
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function byCategory(Category $category)
    {
        $categoryId = $category->id;
        $posts = Post::with('categories')
            ->join('category_post', 'posts.id', '=', 'category_post.post_id')
            ->select('posts.*')
            ->where('category_post.category_id', $categoryId)
            ->where('active', '=', 1)
            ->whereDate('published_at', '<=', Carbon::now())
            ->orderBy('published_at', 'desc')
            ->paginate(10);
        return view('post.index', compact('posts', 'category'));
    }

    public function search(Request $request)
    {
        $q = preg_quote(trim($request->get('q')), '/');

        $posts = Post::query()
            ->where('active', '=', 1)
            ->whereDate('published_at', '<=', Carbon::now())
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%$q%")
                    ->orWhere('body', 'like', "%$q%");
            })
            ->orderBy('published_at', 'desc')
            ->paginate(10)
            ->withQueryString();
        $postsTotal = $posts->total();

        return view('post.search', compact('posts', 'postsTotal'));
    }
}
