@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">Crear Publicación</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('marketplace.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Título</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Categoría</label>
                            <select name="category" class="form-select">
                                <option value="Baloncesto">Baloncesto</option>
                                <option value="Tenis">Tenis</option>
                                <option value="Voleibol">Voleibol</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Costo</label>
                            <input type="number" step="0.01" name="cost" class="form-control" value="{{ old('cost') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select name="status" class="form-select">
                                <option value="Disponible">Disponible</option>
                                <option value="Vendido">Vendido</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('marketplace.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success">Guardar Publicación</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection