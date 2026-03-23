<x-layout>
    <x-navbar>
    </x-navbar>

    <div class= "container py-4">

        <div class="mb-4">
            <h1 class="fw-bold" >Buscar usuarios</h1>
            <p> Aqui podras buscar algun  usuario especifico por rol.
            </p>

        </div>
        <div class="row mb-4 g-3">
            <div class="col-md-8">
                <div class="input-group search-group">
        <span class="input-group-text bg-white border-0">
            <i class="bi bi-search"></i>
        </span>

                    <input
                        type="text"
                        class="form-control border-0"
                        placeholder="Buscar usuarios..."
                    >
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-select border-2 border-dark">
                    <option>Roles</option>
                    <option>Usuario</option>
                    <option>Administrador Super</option>
                    <option>Administrador de Inventario</option>
                    <option>Administrador de Facilidades</option>
                    <option>Administrador de Mercado</option>
                </select>
            </div>
        </div>




    </div>



</x-layout>
