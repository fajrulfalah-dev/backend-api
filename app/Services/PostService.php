<?php

namespace App\Services;

use App\Models\Post;

class PostService
{
    // Ambil semua post milik user yang login
    public function index($user)
    {
        return Post::where('user_id', $user->id)->latest()->get();
    }

    // Ambil 1 post berdasarkan id
    public function show($user, $id)
    {
        return Post::where('user_id', $user->id)->findOrFail($id);
    }

    // Tambah post baru
    public function store($user, array $data)
    {
        return Post::create([
            'user_id' => $user->id,
            'title'   => $data['title'],
            'body'    => $data['body'],
        ]);
    }

    // Update post
    public function update($user, $id, array $data)
    {
        $post = Post::where('user_id', $user->id)->findOrFail($id);
        $post->update($data);
        return $post;
    }

    // Hapus post
    public function destroy($user, $id)
    {
        $post = Post::where('user_id', $user->id)->findOrFail($id);
        $post->delete();
    }
}