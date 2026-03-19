@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1">KineMercado</h1>
            <p class="text-muted mb-0">Compra y vende equipo deportivo y gestiona conversaciones</p>
        </div>
        <a href="{{ route('marketplace.create') }}" class="btn btn-success">
            + Crear Publicación
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('marketplace.index') }}" class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Buscar publicaciones..."
                        value="{{ request('search') }}"
                    >
                </div>

                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">Todas las Categorías</option>
                        <option value="Baloncesto" {{ request('category') == 'Baloncesto' ? 'selected' : '' }}>Baloncesto</option>
                        <option value="Tenis" {{ request('category') == 'Tenis' ? 'selected' : '' }}>Tenis</option>
                        <option value="Voleibol" {{ request('category') == 'Voleibol' ? 'selected' : '' }}>Voleibol</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Todos los Estados</option>
                        <option value="Disponible" {{ request('status') == 'Disponible' ? 'selected' : '' }}>Disponible</option>
                        <option value="Vendido" {{ request('status') == 'Vendido' ? 'selected' : '' }}>Vendido</option>
                    </select>
                </div>

                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-outline-secondary">Filtrar</button>
                </div>
            </div>
        </div>
    </form>

    {{-- Success message --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Listings --}}
    <div class="row g-4">
        @forelse($listings as $listing)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0">
                    <img
                        src="{{ $listing->photo_url ?? 'https://via.placeholder.com/600x350?text=No+Image' }}"
                        class="card-img-top"
                        alt="{{ $listing->title }}"
                        style="height: 220px; object-fit: cover;"
                    >

                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">{{ $listing->title }}</h5>
                            @if($listing->status === 'Vendido')
                                <span class="badge bg-danger">VENDIDO</span>
                            @else
                                <span class="badge bg-success">DISPONIBLE</span>
                            @endif
                        </div>

                        <p class="text-muted small mb-2">{{ $listing->category }}</p>
                        <p class="card-text">{{ \Illuminate\Support\Str::limit($listing->description, 100) }}</p>

                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="fw-bold">${{ number_format($listing->cost, 2) }}</span>
                            <a href="#" class="btn btn-outline-primary btn-sm">Ver más</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info mb-0">
                    No hay publicaciones disponibles.
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination disabled while using mock data --}}

</div>
@endsection