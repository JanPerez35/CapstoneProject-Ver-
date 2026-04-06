<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{  
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|min:5|max:100',
            'description' => 'nullable|min:10|max:1000',
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
        // Seguridad: solo dueño elimina
        if ($post->user_id !== auth()->id()) {
            abort(403);
        }

        $post->delete();

        return back()->with('success', 'Post eliminado');
    }

    public function index()
    {
        $posts = Post::latest()->get();
        return view('kinemarket', compact('posts'));
    }
}