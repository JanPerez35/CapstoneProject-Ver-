<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PostController extends Controller
{  
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|min:5|max:100',
            'description' => 'nullable|max:500',
            'cost' => 'required|numeric',
            'category' => 'required',
            'condition' => 'required',
            'images' => 'nullable|array|max:3',
            'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $paths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $paths[] = $image->store('posts', 'public');
            }
        }

        Post::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'cost' => $request->cost,
            'category' => $request->category,
            'condition' => $request->condition,
            'status' => 'Disponible',
            'photo_1_url' => $paths[0] ?? null,
            'photo_2_url' => $paths[1] ?? null,
            'photo_3_url' => $paths[2] ?? null,
        ]);

        return response()->json(['success' => true]);
    }
    public function destroy(Post $post)
    {
        if ($post->user_id !== auth()->id() && !in_array(auth()->user()->role, ['market_admin', 'super_admin'])) {
            abort(403);
        }
        $images = [
                $post->photo_1_url,
                $post->photo_2_url,
                $post->photo_3_url
        ];

        foreach ($images as $image) {
            if ($image) {
                Storage::disk('public')->delete($image);
            }
        }

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post eliminado exitosamente'
            ]);
    }

    public function index()
    {
        $posts = Post::latest()->get();
        return view('kinemarket', compact('posts'));
    }

    public function show($id)
    {
        return \App\Models\Post::with('user')->findOrFail($id);
    }
}