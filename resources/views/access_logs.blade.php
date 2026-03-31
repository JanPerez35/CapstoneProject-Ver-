<x-layout title="Registros de Acceso">
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
        <div class="row mb-4 g-3">
            <div class="col-md-6">
                <div class="input-group search-group">
                   <span class="input-group-text bg-white border-0">
                         <i class="bi bi-search"></i>
                   </span>
                    <input
                        type="text"
                        class="form-control border-0"
                        placeholder="Buscar por usuario, IP o detalles..."
                        >
                    </div>
                </div>
                <div class="col-lg-3">
                    <select class="form-select border-2 border-dark">
                        <option selected>Todos los Roles</option>
                        <option>Usuario</option>
                        <option>Administrador de Mercado</option>
                        <option>Administrador de Inventario</option>
                        <option>Administrador de Facilidad</option>
                        <option>Super Administrador</option>
                    </select>
                </div>
            <div class="col-lg-3">
                <select class="form-select border-2 border-dark">
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
                    <tr>
                        <td class="px-4 py-3">2026-03-30 20:15:42</td>
                        <td class="px-4 py-3">Melanie Rivera</td>
                        <td class="py-3 text-center align-middle">
                            <div class="d-flex justify-content-center align-items-center h-100">
                            <span class="badge px-3 py-2" style="background-color:#6FC21F; color:white;">Usuario</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">Inicio de Sesión</td>
                        <td class="px-4 py-3">2001:0db8:85a3:0000:0000:8a2e:0370:7334</td>
                        <td class="px-4 py-3">Acceso exitoso al sistema</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

    <!--Pagination placeholder-->
    <nav aria-label="Page navigation example" class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item disabled">
                <a class="page-link" href="#" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <li class="page-item active">
                <a class="page-link" href="#">1</a>
            </li>

            <li class="page-item">
                <a class="page-link" href="#">2</a>
            </li>

            <li class="page-item">
                <a class="page-link" href="#">3</a>
            </li>

            <li class="page-item">
                <a class="page-link" href="#" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
</x-layout>

