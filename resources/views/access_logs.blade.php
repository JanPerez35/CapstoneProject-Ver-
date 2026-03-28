<x-layout>
    <x-navbar></x-navbar>


    <div class="container-fluid py-4 px-4">


        <div class="mb-4">
            <h1 class="fw-bold">Bienvenido al Panel de Administración</h1>
            <p>Monitorear acceso al sistema y costos de instalaciones</p>
        </div>


        <div class="d-flex justify-content-end gap-3 mb-4">
            <button class="btn btn-success px-4 py-2">
                <i class="bi bi-box-arrow-in-down me-2"></i>Exportar a CSV
            </button>
            <button class="btn btn-success px-4 py-2">
                <i class="bi bi-box-arrow-in-down me-2"></i>Exportar a PDF
            </button>
        </div>


        <!-- Filters and searches -->
        <div class="row g-3 align-items-center mb-4">
            <div class="col-lg-6">
                <div class="input-group">
                   <span class="input-group-text bg-white border-end-0 rounded-start-4">
                       <i class="bi bi-search text-muted"></i>
                   </span>
                    <input
                        type="text"
                        class="form-control border-start-0 rounded-end-4 py-3"
                        placeholder="Buscar por usuario, IP o detalles..."
                    >
                </div>
            </div>


            <div class="col-lg-3">
                <select class="form-select rounded-4 py-3 fw-semibold">
                    <option selected>Todos los Roles</option>
                    <option>Usuario</option>
                    <option>Administrador de Mercado</option>
                    <option>Administrador de Inventario</option>
                    <option>Administrador de Facilidata</option>
                    <option>Super Administrador</option>
                </select>
            </div>


            <div class="col-lg-3">
                <select class="form-select rounded-4 py-3 fw-semibold">
                    <option selected>Todos los Eventos</option>
                    <option>Inicio de Sesión</option>
                    <option>Cierre de Sesión</option>
                    <option>Error de Acceso</option>
                    <option>Acceso Admin</option>
                    <option>Ver Mercado</option>
                    <option>Ver Inventario</option>
                    <option>Solicitud de Préstamo</option>
                    <option>Publicación Creada</option>
                </select>
            </div>
        </div>


        <!-- Access log table -->
        <div class="card border rounded-4 shadow-sm overflow-hidden">
            <div class="card-body p-4 border-bottom">
                <h3 class="fw-bold mb-2">Registros de Acceso</h3>
                <p class="text-muted mb-0 fs-5">Monitoreo en tiempo real del acceso al sistema</p>
            </div>


            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3 fw-bold">Marca de Tiempo</th>
                        <th class="px-4 py-3 fw-bold">Usuario</th>
                        <th class="px-4 py-3 fw-bold">Rol</th>
                        <th class="px-4 py-3 fw-bold">Evento</th>
                        <th class="px-4 py-3 fw-bold">Dirección IP</th>
                        <th class="px-4 py-3 fw-bold">Comentario</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- Call for data later -->
                    </tbody>
                </table>
            </div>
        </div>


    </div>
</x-layout>

