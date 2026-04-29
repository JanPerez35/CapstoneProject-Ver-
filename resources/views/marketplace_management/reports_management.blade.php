<x-layout title="Gestión de Mercado">
    <x-navbar></x-navbar>
    @vite('resources/js/marketplace_reports.js')
    @vite('resources/js/marketplace_validation.js')
    <div class="container pt-2 pb-4">

        <!--This is the header-->
        <div class="mb-4">
            <h1 class="fw-bold">Gestión de Mercado</h1>
            <p>
                Aquí puedes administrar los reportes del mercado.
            </p>
        </div>

        <!--Filter and searches-->
            <div class="mb-4">
                <div class="row g-3 mb-3 align-items-stretch">
                    <div class="col-lg-10">
                        <div class="input-group search-group h-100">
                            <span class="input-group-text bg-white border-0">
                                <i class="bi bi-search"></i>
                            </span>

                        <input
                            type="text"
                            id="filterSearchBy"
                            class="form-control border-0"
                            placeholder="Buscar por usuario reportante o vendedor..."
                        >
                        </div>
                    </div>

                    <div class="col-md-2 d-grid">
                        <button type="button" class="btn btn-success" id="searchReportsBtn" disabled>
                            Buscar
                        </button>
                    </div>


                    <div class="col-md-6 col-lg-4">
                        <select id="filterReason" class="form-select border-2 border-dark">
                            <option value="">Todas las Razones</option>
                            <option value="Fraude o estafa">Fraude o estafa</option>
                            <option value="Información falsa">Información falsa</option>
                            <option value="Lenguaje ofensivo">Lenguaje ofensivo</option>
                            <option value="Contenido inapropiado">Contenido inapropiado</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>

                    <div class="col-md-6 col-lg-4 position-relative">
                        <input
                            type="date"
                            id="filterDate"
                            class="form-control border-2 border-dark pe-5"
                        >

                        <!-- custom icon -->
                        <span class="date-icon">
                             <i class="bi bi-calendar3"></i>
                        </span>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-secondary" id="clearReportsFilters">
                            Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>

        <!--Legend-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body py-3 px-4">
                <h5 class="fw-bold mb-3">Leyenda</h5>

                <div class="row g-3">
                    <div class="col-sm-6 col-lg-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-eye fs-5 text-secondary"></i>
                            <span>Ver Publicación</span>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle fs-5 text-success"></i>
                            <span>Resolver Querella</span>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-trash fs-5 text-danger"></i>
                            <span>Eliminar Publicación</span>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-ban fs-5 text-danger"></i>
                            <span>Bloquear Usuario</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

       <!--Potential Backend Connection-->
        @php
            $reports = $reports
        @endphp

        <!--Reports table-->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4 border-bottom">
                <h2 class="fw-bold mb-1">Querellas del Kinemercado</h2>
                <p class="text-muted mb-0">
                    Revisión y administración de querellas realizados sobre publicaciones y vendedores.
                </p>
            </div>
            <div class="table-fit-wrapper">
                <table class="table align-middle mb-0 reports-table" id="reportsTable">
                    <thead class="table-light">
                    <tr>
                        <th>Reportado por</th>
                        <th>Vendedor</th>
                        <th>Razón</th>
                        <th>Fecha Reportada (mm/dd/yyyy)</th>
                        <th>Descripción de la Querella</th>

                        <th class="text-center action-header-icon" title="Ver publicación" aria-label="Ver publicación">
                            <i class="bi bi-eye fs-5 text-secondary"></i>
                        </th>
                        <th class="text-center action-header-icon" title="Resolver querella" aria-label="Resolver querella">
                            <i class="bi bi-check-circle fs-5 text-success"></i>
                        </th>
                        <th class="text-center action-header-icon" title="Eliminar publicación" aria-label="Eliminar publicación">
                            <i class="bi bi-trash fs-5 text-danger"></i>
                        </th>
                        <th class="text-center action-header-icon" title="Bloquear usuario" aria-label="Bloquear usuario">
                            <i class="bi bi-ban fs-5 text-danger"></i>
                        </th>
                    </tr>
                    </thead>

                    <tbody>

                    </tbody>
                </table>

                <div id="reportsEmptyState" class="reports-empty-state d-none">
                    <div class="card border-0 shadow-sm rounded-0">
                        <div class="card-body py-5 text-center">
                            <i class="bi bi-flag fs-1 text-muted"></i>
                            <h4 class="fw-bold mt-3">No se encontraron querellas.</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <nav class="mt-4" aria-label="Paginación de querellas">
            <ul class="pagination justify-content-center" id="querellasPagination"></ul>
        </nav>

        <!--Modal to resolve report-->
        <div class="modal fade" id="resolveQuerellaModal" tabindex="-1" aria-labelledby="resolveQuerellaModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header modal-header-top border-0">
                        <div class="pe-3">
                            <h4 class="modal-title fw-bold mb-1" id="resolveQuerellaModalLabel">Resolver querella</h4>
                            <p class="text-dark mb-0">¿Estás seguro de que deseas marcar este querella como resuelto?</p>
                        </div>
                        <button type="button" class="btn-close modal-close-top" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-footer border-0 pt-1">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" id="confirmResolveQuerella">Resolver</button>
                    </div>
                </div>
            </div>
        </div>

        <!--Modal eliminate post -->
        <div class="modal fade" id="deletePostModal" tabindex="-1" aria-labelledby="deletePostModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header modal-header-top border-0">
                        <div class="pe-3">
                            <h4 class="modal-title fw-bold mb-1" id="deletePostModalLabel">Eliminar publicación</h4>
                            <p class="text-dark mb-0">¿Estás seguro de que deseas eliminar esta publicación?</p>
                        </div>
                        <button type="button" class="btn-close modal-close-top" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-footer border-0 pt-1">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="confirmDeletePost">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>

        <!--Modal to ban user-->
        <div class="modal fade" id="bloquearUserModal" tabindex="-1" aria-labelledby="bloquearUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header modal-header-top border-0">
                        <div class="pe-3">
                            <h4 class="modal-title fw-bold mb-1" id="bloquearUserModalLabel">Bloquear usuario</h4>
                            <p class="text-dark mb-0">¿Estás seguro de que deseas bloquear este usuario?<br> Sus publicaciones dejarán de estar visibles y esta querella se marcará como resuelto.</p>
                        </div>
                        <button type="button" class="btn-close modal-close-top" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-footer border-0 pt-1">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="confirmBloquearUser">Bloquear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toasts Notifications -->
        <div class="toast-container position-fixed bottom-0 start-0 p-3">
            <div id="resolveToast" class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2 market-toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex align-items-center">
                    <div class="toast-body fw-semibold rounded-0 pe-1 market-toast-body">Querella resuelto correctamente.</div>
                    <button type="button" class="btn-close p-0 ms-1 me-2 market-toast-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>

            <div id="deleteToast" class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2 market-toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex align-items-center">
                    <div class="toast-body fw-semibold rounded-0 pe-1 market-toast-body" >Publicación eliminada y querella resuelto correctamente.</div>
                    <button type="button" class="btn-close p-0 ms-1 me-2 market-toast-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>

            <div id="banToast" class="toast align-items-center shadow-sm border border-danger-subtle bg-danger-subtle text-danger-emphasis rounded-0 mb-2 market-toast" role="alert" aria-live="assertive" aria-atomic="true" >
                <div class="d-flex align-items-center">
                    <div class="toast-body fw-semibold rounded-0 pe-1 market-toast-body">Usuario bloqueado y querella resuelto correctamente.</div>
                    <button type="button" class="btn-close p-0 ms-1 me-2 market-toast-close" data-bs-dismiss="toast" aria-label="Cerrar" ></button>
                </div>
            </div>

        </div>

    </div>
    <div class="modal fade" id="postDetailsModal" tabindex="-1" aria-labelledby="postDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow overflow-hidden">
                    <div class="modal-header border-0 pt-4 px-4 pb-2 align-items-start position-relative">
                        <div class="pe-5">
                            <h4 class="modal-title fw-bold mb-1" id="postDetailsModalLabel">Detalle de la publicación</h4>
                            <p class="text-muted mb-0">Detalles de la Publicación</p>
                        </div>
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>


                    <div class="modal-body px-4 pt-2 pb-4 post-details-body">
                        <div id="postImagesCarousel" class="carousel slide mb-4">
                            <div class="carousel-indicators" id="postImagesCarouselIndicators"></div>


                            <div class="carousel-inner rounded-4 overflow-hidden post-carousel-inner" id="postImagesCarouselInner"></div>


                            <button class="carousel-control-prev" type="button" data-bs-target="#postImagesCarousel" data-bs-slide="prev" id="postImagesCarouselPrev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>


                            <button class="carousel-control-next" type="button" data-bs-target="#postImagesCarousel" data-bs-slide="next" id="postImagesCarouselNext">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Siguiente</span>
                            </button>
                        </div>
                        <p class="mb-3 text-muted d-none" id="postDetailsDescription"></p>
                        <hr>


                        <div class="row gy-3 pb-2">
                            <div class="col-6 text-muted">Precio:</div>
                            <div class="col-6 text-end fw-bold text-success" id="postDetailsPrice">$0.00</div>


                            <div class="col-6 text-muted">Estado:</div>
                            <div class="col-6 text-end">
                               <span class="label-badge badge-available" id="postDetailsStatus">
                                Disponible
                                </span>
                            </div>


                            <div class="col-6 text-muted">Condición:</div>
                            <div class="col-6 text-end">
                               <span class="label-badge badge-available" id="postDetailsCondition">
                                 Sin especificar
                                </span>
                            </div>


                            <div class="col-6 text-muted">Vendedor:</div>
                            <div class="col-6 text-end fw-bold" id="postDetailsSeller">Usuario</div>


                            <div class="col-6 text-muted">Calificación del Vendedor:</div>
                            <div class="col-6 text-end" id="postDetailsSellerRating">
                                <i class="bi bi-star-fill text-warning me-1"></i>
                                0.0 <span class="text-muted">(0 reseñas)</span>
                            </div>


                            <div class="col-6 text-muted">Categoría:</div>
                            <div class="col-6 text-end">
                              <span class="label-badge badge-available" id="postDetailsCategory">
                                 Sin categoría
                              </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

</x-layout>
