<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $listings = collect([
            (object) [
                'id' => 1,
                'title' => 'Balón de Baloncesto Spalding',
                'description' => 'Balón en buen estado, poco uso.',
                'category' => 'Baloncesto',
                'status' => 'Disponible',
                'cost' => 25.00,
                'photo_url' => 'https://via.placeholder.com/600x350?text=Baloncesto',
            ],
            (object) [
                'id' => 2,
                'title' => 'Raqueta de Tenis Wilson',
                'description' => 'Raqueta ligera ideal para entrenamiento.',
                'category' => 'Tenis',
                'status' => 'Disponible',
                'cost' => 45.00,
                'photo_url' => 'https://via.placeholder.com/600x350?text=Tenis',
            ],
            (object) [
                'id' => 3,
                'title' => 'Rodilleras Voleibol',
                'description' => 'Par de rodilleras, tamaño mediano.',
                'category' => 'Voleibol',
                'status' => 'Vendido',
                'cost' => 15.00,
                'photo_url' => 'https://via.placeholder.com/600x350?text=Voleibol',
            ],
        ]);

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $listings = $listings->filter(function ($item) use ($search) {
                return str_contains(strtolower($item->title), $search)
                    || str_contains(strtolower($item->description), $search);
            });
        }

        if ($request->filled('category')) {
            $listings = $listings->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $listings = $listings->where('status', $request->status);
        }

        return view('marketplace.index', [
            'listings' => $listings->values(),
        ]);
    }

    public function create()
    {
        return view('marketplace.create');
    }

    public function store(Request $request)
    {
        return redirect()
            ->route('marketplace.index')
            ->with('success', 'Publicación simulada creada correctamente.');
    }
}