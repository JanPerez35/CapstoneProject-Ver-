<x-layout title="Gestión de Mercado">
    <x-navbar></x-navbar>
    @vite('resources/js/marketplace_reports.js')
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
                            placeholder="Buscar usuario o vendedor..."
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
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <input
                            type="date"
                            id="filterDate"
                            class="form-control border-2 border-dark"
                        >
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
            $reports = $reports ?? [
                [
                    'report_id' => 'report-001',
                    'post_id' => 'post-101',
                    'seller_id' => 'user-301',
                    'reported_by' => 'María González',
                    'seller' => 'Natalia Ruth',
                    'reason' => 'Fraude o estafa',
                    'reported_date' => '03/16/2026',
                    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur vel urna at elit varius tristique. Integer luctus, nisi at fermentum feugiat, sapien justo tincidunt erat, sed facilisis libero nulla non augue. Suspendisse potenti. Phasellus non mi vel augue bibendum tincidunt. Vivamus eget lorem nec nulla facilisis malesuada. Duis nec velit sed sapien tempor fringilla. Proin id arcu vitae purus ullamcorper varius eget sed justo.',
                ],
                [
                    'report_id' => 'report-002',
                    'post_id' => 'post-102',
                    'seller_id' => 'user-302',
                    'reported_by' => 'Carlos Rodríguez',
                    'seller' => 'Maria Ruth',
                    'reason' => 'Información falsa',
                    'reported_date' => '03/17/2026',
                    'description' => 'Publicación sospechosa con precio irrazonable. El vendedor no tiene historial y las fotos parecen ser de internet.',
                ],
            ];
        @endphp

        <!--Reports table-->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-fit-wrapper">
                <table class="table align-middle mb-0 reports-table" id="reportsTable">
                    <thead class="table-light">
                    <tr>
                        <th>Reportado por</th>
                        <th>Vendedor</th>
                        <th>Razón</th>
                        <th>Fecha Reportada (mm/dd/yyyy)</th>
                        <th>Descripción</th>

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
                    @forelse ($reports as $report)
                        <tr
                            data-report-id="{{ $report['report_id'] }}"
                            data-post-id="{{ $report['post_id'] }}"
                            data-seller-id="{{ $report['seller_id'] }}"
                        >
                            <td>{{ $report['reported_by'] }}</td>
                            <td>{{ $report['seller'] }}</td>
                            <td>{{ $report['reason'] }}</td>
                            <td>{{ $report['reported_date'] }}</td>
                            <td class="report-description-cell">
                                {{ $report['description'] }}
                            </td>
                            <td class="text-center action-col">
                                <input class="form-check-input action-radio action-view" type="radio" name="action_{{$report['report_id']}}">
                            </td>
                            <td class="text-center action-col">
                                <input class="form-check-input action-radio action-resolve" type="radio" name="action_{{$report['report_id']}}">
                            </td>
                            <td class="text-center action-col">
                                <input class="form-check-input action-radio action-delete-post" type="radio" name="action_{{$report['report_id']}}">
                            </td>
                            <td class="text-center action-col">
                                <input class="form-check-input action-radio action-block-user" type="radio" name="action_{{$report['report_id']}}">
                            </td>
                        </tr>
                    @empty
                    @endforelse
                    </tbody>
                </table>

                <div id="reportsEmptyState" class="reports-empty-state d-none">
                    <div class="card border-0 shadow-sm rounded-0">
                        <div class="card-body py-5 text-center">
                            <i class="bi bi-flag fs-1 text-muted"></i>
                            <h4 class="fw-bold mt-3">No se encuentran reportes.</h4>
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
            <div id="resolveToast" class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="width: auto; max-width: fit-content;">
                <div class="d-flex align-items-center">
                    <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">Querella resuelto correctamente.</div>
                    <button type="button" class="btn-close p-0 ms-1 me-2" data-bs-dismiss="toast" aria-label="Cerrar" style="background-color: transparent; border: none; transform: scale(0.8);"></button>
                </div>
            </div>

            <div id="deleteToast" class="toast align-items-center shadow-sm border border-danger-subtle bg-danger-subtle text-danger-emphasis rounded-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="width: auto; max-width: fit-content;">
                <div class="d-flex align-items-center">
                    <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">Publicación eliminada y querella resuelto correctamente.</div>
                    <button type="button" class="btn-close p-0 ms-1 me-2" data-bs-dismiss="toast" aria-label="Cerrar" style="background-color: transparent; border: none; transform: scale(0.8);"></button>
                </div>
            </div>

            <div id="banToast" class="toast align-items-center shadow-sm border border-danger-subtle bg-danger-subtle text-danger-emphasis rounded-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="width: auto; max-width: fit-content;">
                <div class="d-flex align-items-center">
                    <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">Usuario bloqueado y querella resuelto correctamente.</div>
                    <button type="button" class="btn-close p-0 ms-1 me-2" data-bs-dismiss="toast" aria-label="Cerrar" style="background-color: transparent; border: none; transform: scale(0.8);"></button>
                </div>
            </div>

            <div id="viewToast" class="toast align-items-center shadow-sm border border-secondary-subtle bg-light text-dark rounded-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="width: auto; max-width: fit-content;">
                <div class="d-flex align-items-center">
                    <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">Puente preparado para ver la publicación reportada.</div>
                    <button type="button" class="btn-close p-0 ms-1 me-2" data-bs-dismiss="toast" aria-label="Cerrar" style="background-color: transparent; border: none; transform: scale(0.8);"></button>
                </div>
            </div>
        </div>

    </div>

    <style>
        .table-fit-wrapper { padding: 0; }

        .reports-table {
            width: 100%;
            table-layout: fixed;
            margin-bottom: 0;
        }

        .reports-table thead th,
        .reports-table tbody td {
            vertical-align: middle;
            padding: 0.95rem 0.8rem;
            font-size: 0.95rem;
            line-height: 1.35;
            white-space: normal;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .reports-table thead th {
            background: #f8f9fa;
            font-size: 0.9rem;
            font-weight: 700;
        }
         /*
         Order:
         Reported by
         Seller
         Reason
         Date Reported
         Description
         */

        .reports-table th:nth-child(1), .reports-table td:nth-child(1) { width: 14%; }
        .reports-table th:nth-child(2), .reports-table td:nth-child(2) { width: 13%; }
        .reports-table th:nth-child(3), .reports-table td:nth-child(3) { width: 14%; }
        .reports-table th:nth-child(4), .reports-table td:nth-child(4) { width: 15%; }
        .reports-table th:nth-child(5), .reports-table td:nth-child(5) { width: 24%; }
        .reports-table th:nth-child(6), .reports-table td:nth-child(6),
        .reports-table th:nth-child(7), .reports-table td:nth-child(7),
        .reports-table th:nth-child(8), .reports-table td:nth-child(8),
        .reports-table th:nth-child(9), .reports-table td:nth-child(9) { width: 5%; }

        .report-description-cell {
            line-height: 1.45;
        }

        .action-col {
            min-width: 0;
            text-align: center;
        }

        .action-header-icon {
            padding-left: 0.45rem;
            padding-right: 0.45rem;
        }

        .action-radio {
            transform: scale(1.45);
            cursor: pointer;
            margin: 0;
            accent-color: #198754;
            filter: drop-shadow(0 2px 3px rgba(0,0,0,0.25));
            transition: transform 0.2s ease, opacity 0.2s ease, filter 0.2s ease;
        }

        .action-radio:hover{
            transform: scale(1.55);
        }

        .action-radio.active-radio {
            opacity: 1;
            filter: drop-shadow(0 3px 6px rgba(0,0,0,0.3));
        }

        .reports-empty-state {
            border-top: 1px solid #dee2e6;
        }


        .modal-header-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 1.1rem 1.1rem 0.25rem 1.1rem;
        }

        .modal-close-top {
            margin: 0.1rem 0 0 0;
            flex-shrink: 0;
        }

        @media (max-width: 1199.98px) {
            .reports-table thead th,
            .reports-table tbody td {
                padding: 0.75rem 0.5rem;
                font-size: 0.84rem;
            }

            .reports-table thead th {
                font-size: 0.8rem;
            }

            .action-radio {
                transform: scale(1.2);
            }

        }

        @media (max-width: 991.98px) {
            .table-fit-wrapper {
                overflow-x: auto;
            }

            .reports-table {
                min-width: 900px;
            }

            .reports-table thead th,
            .reports-table tbody td {
                padding: 0.7rem 0.45rem;
                font-size: 0.8rem;
            }

            .reports-table thead th {
                font-size: 0.76rem;
            }

            .action-radio {
                transform: scale(1.1);
            }
        }


    </style>
</x-layout>
