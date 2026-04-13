<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Services\PostService;
use Illuminate\Http\Request;

class PostController extends Controller
{
    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    // GET /api/posts — ambil semua post milik user
    public function index(Request $request)
    {
        $posts = $this->postService->index($request->user());
        return PostResource::collection($posts);
    }

    // GET /api/posts/{id} — ambil 1 post
    public function show(Request $request, $id)
    {
        $post = $this->postService->show($request->user(), $id);
        return new PostResource($post);
    }

    // POST /api/posts — tambah post baru
    public function store(StorePostRequest $request)
    {
        $post = $this->postService->store($request->user(), $request->validated());
        return new PostResource($post);
    }

    // PUT /api/posts/{id} — update post
    public function update(UpdatePostRequest $request, $id)
    {
        $post = $this->postService->update($request->user(), $id, $request->validated());
        return new PostResource($post);
    }

    // DELETE /api/posts/{id} — hapus post
    public function destroy(Request $request, $id)
    {
        $this->postService->destroy($request->user(), $id);
        return response()->json([
            'status'  => 'success',
            'message' => 'Post berhasil dihapus.',
        ]);
    }
}