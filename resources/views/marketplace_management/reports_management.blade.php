<x-layout title="Gestión de Mercado">
    <x-navbar></x-navbar>

    {{--Javascript modules used by the marketplace reports view.
        They handle report rendering, table actions, pagination, post details modal data,
        toast notifications, report action confirmations, report filter validation, and date picker behavior--}}
    @vite('resources/js/marketplace_reports.js')
    @vite('resources/js/marketplace_validation.js')

    {{--Main marketplace management page container.Holds the page header,
         filter controls, reports table, pagination, action modals, toast notifications, and post details modal--}}
    <div class="container pt-2 pb-4">

        {{--Page header. Identifies the current administrator section and explains that this page
            is used to manage marketplace reports/querellas.--}}
        <div class="mb-4">
            <h1 class="fw-bold">Gestión de Mercado</h1>
            <p>
                Aquí puedes administrar los informes del mercado.
            </p>
        </div>

        {{--Filters and search controls. These controls allow administrators to search reports by user, filter by reason,
           filter by date, and reset all active filters--}}
            <div class="mb-4">
                <div class="row g-3 mb-3 align-items-stretch">
                    {{--Search input that can filter reports/querellas by reporting user or seller name--}}
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

                    {{--Search button that is initially disabled and enabled by the JS
                        when the search field contains usable text--}}
                    <div class="col-md-2 d-grid">
                        <button type="button" class="btn btn-success" id="searchReportsBtn" disabled>
                            Buscar
                        </button>
                    </div>

                   {{--Report reason filter--}}
                    <div class="col-md-6 col-lg-4">
                        <select id="filterReason" class="form-select border-2 border-dark">
                            <option value="">Todas las Razones</option>
                            <option value="Fraude o estafa">Fraude o estafa</option>
                            <option value="Información falsa">Información falsa</option>
                            <option value="Lenguaje ofensivo">Lenguaje ofensivo</option>
                            <option value="Contenido inapropiado">Contenido inapropiado</option>
                            <option value="Calificación baja">Calificación baja</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>

                    {{--Date filter. Uses a text input plus calendar icon so the JS
                        may cintrol the date picker behavior and prevent unwanted manual keyborad input--}}
                    <div class="col-md-6 col-lg-4">
                        <div class="date-picker-wrapper h-100">
                            <input
                                type="text"
                                id="filterDate"
                                class="form-control border-dark border-2 py-2 date-picker-input"
                                placeholder="dd-mm-aaaa"
                                autocomplete="off"
                                inputmode="none"
                            >

                            {{--Calendar trigger button for opening the report date picker--}}
                            <button type="button"
                                    class="date-picker-icon"
                                    id="filterDateIcon"
                                    aria-label="Abrir calendario del filtro de querellas">
                                <i class="bi bi-calendar3"></i>
                            </button>
                        </div>
                    </div>

                    {{--Clear filters button. Resets the search input, reason dropdown, date picker, and table results--}}
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-secondary" id="clearReportsFilters">
                            Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>

       {{--Backend data conection. Keeps the reports/querellas collection available to the Blade view.
           The JS handles the dynamic rendering of the table body--}}
        @php
            $reports = $reports
        @endphp

        {{--Report card that wraps the report table and its header information inside a bordered card layout--}}
        <div class="card border-dark border-2 rounded-2 overflow-hidden">
            <div class="card-body p-4 border-bottom">

               {{--Report/Querella section header that includes the table title, shor description,
                   and the action icon description.--}}
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
                    <div>
                        <h2 class="fw-bold mb-1">Querellas del Kinemercado</h2>
                        <p class="text-muted mb-0">
                            Revisión y administración de querellas realizados sobre publicaciones y sus vendedores.
                        </p>
                    </div>

                    {{--Action legend that explains the purpose of every icon button that appears in each report row--}}
                    <div class="d-flex align-items-start gap-3 ms-lg-0 ms-auto align-self-end">

                        {{--Left side of the legend, used as the legend title--}}
                        <div class="fw-bold">
                            Leyenda:
                        </div>

                        {{--Right side of the legend, organized vertically for readability--}}
                        <div class="d-flex flex-column gap-1">

                            <span class="d-flex align-items-center gap-1">
                                <i class="bi bi-eye text-secondary"></i> Ver Publicación
                            </span>

                            <span class="d-flex align-items-center gap-1">
                                <i class="bi bi-check-circle text-success"></i> Resolver Querella
                            </span>

                            <span class="d-flex align-items-center gap-1">
                                <i class="bi bi-trash text-danger"></i> Eliminar Publicación
                            </span>

                            <span class="d-flex align-items-center gap-1">
                                <i class="bi bi-ban text-danger"></i> Bloquear Usuario
                            </span>

                        </div>

                    </div>
                    </div>
                </div>

            </div>

        {{--Report table wrapper. Keeps the table visually separated from the page and allows the JS-rendered table body
            and empty state to share the same bordered container--}}
        <div class="table-fit-wrapper border border-2 border-dark rounded-2 mt-3">
                <table class="table align-middle mb-0 reports-table" id="reportsTable">
                    <thead class="table-light">
                    <tr>
                        {{--Report information columns. Row data is injected dynamically into the body
                            by the contentes stored on the backend and called upon by the JS--}}
                        <th>Reportado por</th>
                        <th>Vendedor</th>
                        <th>Razón de la Querella</th>
                        <th>Fecha Reportada <br>(Día Mes Año)</th>
                        <th>Descripción de la Querella</th>

                        {{--View post action header. Tooltip the explains what the icon represents.
                            In this case the eyeball open the reported marketplace publication details--}}
                        <th class="text-center action-header-icon">
                            <span
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                data-bs-custom-class="custom-tooltip"
                                data-bs-title="Ver Publicación"
                            >
                                <i class="bi bi-eye fs-5 text-secondary"></i>
                            </span>
                        </th>

                        {{--Resolve report action header. Tooltip explains that the check icon marks the querella as resolved--}}
                        <th class="text-center action-header-icon">
                            <span
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                data-bs-custom-class="custom-tooltip"
                                data-bs-title="Resolver Querella"
                            >
                                <i class="bi bi-check-circle fs-5 text-success"></i>
                            </span>
                        </th>

                        {{--Delete publication/post action header.
                             Tooltip explains that the trash icon removes the reported publication
                             from the marketplace (kinemercado)--}}
                        <th class="text-center action-header-icon">
                            <span
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                data-bs-custom-class="custom-tooltip"
                                data-bs-title="Eliminar Publicación"
                            >
                                <i class="bi bi-trash fs-5 text-danger"></i>
                            </span>
                        </th>

                        {{--Block user action header.
                             Tooltip explains that the ban icon blocks the seller connected to the report
                             from the web application--}}
                        <th class="text-center action-header-icon">
                            <span
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                data-bs-custom-class="custom-tooltip"
                                data-bs-title="Bloquear Usuario"
                            >
                                <i class="bi bi-ban fs-5 text-danger"></i>
                            </span>
                         </th>
                      </tr>
                    </thead>

                    {{--Dynamic report table body where the inserted
                        JS report information, actions, and filter results are located--}}
                    <tbody></tbody>
                </table>

            {{--Empty sate that ramins hidden by default and is only shown when no reports
                match the current filters, no reports were sent, or all reports were solved--}}
            <div id="reportsEmptyState" class="reports-empty-state d-none">
                    <div class="card border-0 rounded-0">
                        <div class="card-body py-5 text-center">
                            <i class="bi bi-flag fs-1 text-muted"></i>
                            <h4 class="fw-bold mt-3">No se encontraron querellas.</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

       {{--Pagination container. Located at the buttom of the table.
           Updated when the amount of reports exceeds 18--}}
        <nav class="mt-4 d-flex justify-content-center align-items-center flex-wrap gap-3" aria-label="Paginación de querellas">
            <p class="text-muted small mb-0" id="querellasPaginationSummary"></p>
            <ul class="pagination mb-0" id="querellasPagination"></ul>
        </nav>

        {{--Resolve report confimration modal. It is opened before a report/querella is marked as resolved
            so that the administrator can confirm the action--}}
        <div class="modal fade" id="resolveQuerellaModal" tabindex="-1" aria-labelledby="resolveQuerellaModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header modal-header-top border-0 align-items-start">
                        <div class="pe-3">
                            <h4 class="modal-title fw-bold mb-1" id="resolveQuerellaModalLabel">Resolver querella</h4>
                            <p class="text-dark mb-0">¿Estás seguro de que deseas marcar este querella como resuelto?</p>
                        </div>
                        {{--Closes the resolve confirmation modal without changing the report status--}}
                        <button type="button" class="btn-close modal-close-top" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-footer border-0 pt-1">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>

                        {{--JS listens to this button to confirm and execute the resolve action--}}
                        <button type="button" class="btn btn-success" id="confirmResolveQuerella">Resolver</button>
                    </div>
                </div>
            </div>
        </div>

        {{--Delete publication confirmation modal. It is opened before a report/querella is marked as resolved
            so that the administrator can confirm the action--}}
        <div class="modal fade" id="deletePostModal" tabindex="-1" aria-labelledby="deletePostModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header modal-header-top border-0 align-items-start">
                        <div class="pe-3">
                            <h4 class="modal-title fw-bold mb-1" id="deletePostModalLabel">Eliminar publicación</h4>
                            <p class="text-dark mb-0">¿Estás seguro de que deseas eliminar esta publicación?</p>
                        </div>

                        {{--Closes the delete confirmation modal without deleting the post--}}
                        <button type="button" class="btn-close modal-close-top" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-footer border-0 pt-1">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>

                        {{--JS listens to this button to confirm and execute the post deletion action--}}
                        <button type="button" class="btn btn-danger" id="confirmDeletePost">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>

        {{--Block user confirmation modal. It is opened  before blocking the seller connected to the selected report/querella--}}
        <div class="modal fade" id="bloquearUserModal" tabindex="-1" aria-labelledby="bloquearUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header modal-header-top border-0 align-items-start">
                        <div class="pe-3">
                            <h4 class="modal-title fw-bold mb-1" id="bloquearUserModalLabel">Bloquear usuario</h4>
                            <p class="text-dark mb-0">¿Estás seguro de que deseas bloquear este usuario?<br> Sus publicaciones dejarán de estar visibles y esta querella se marcará como resuelto.</p>
                        </div>
                        {{--Closes the block user confirmation modal without applying restrictions--}}
                        <button type="button" class="btn-close modal-close-top" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-footer border-0 pt-1">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>

                        {{--JS listens to this button to confirm and execute the user block action--}}
                        <button type="button" class="btn btn-danger" id="confirmBloquearUser">Bloquear</button>
                    </div>
                </div>
            </div>
        </div>

    {{--Toast notifications for marketplace management feedback--}}
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3"
         style="z-index: 2000; margin-top: 18vh;">

        {{--Success toast shown after a report/querella is marked as resolved--}}
        <div id="resolveToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2 market-toast"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: fit-content; max-width: min(520px, calc(100vw - 2rem));">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1 market-toast-body">
                    Querella resuelta correctamente.
                </div>

                <button type="button"
                        class="btn-close p-0 ms-1 me-2 market-toast-close"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar">
                </button>
            </div>
        </div>

        {{--Success toast shown after a reported publication is deleted and the querella is resolved--}}
        <div id="deleteToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2 market-toast"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: fit-content; max-width: min(520px, calc(100vw - 2rem));">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1 market-toast-body">
                    Publicación eliminada y querella resuelta correctamente.
                </div>

                <button type="button"
                        class="btn-close p-0 ms-1 me-2 market-toast-close"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar">
                </button>
            </div>
        </div>

        {{--Success toast shown after a seller/user is blocked and the querella is resolved--}}
        <div id="banToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2 market-toast"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: fit-content; max-width: min(520px, calc(100vw - 2rem));">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1 market-toast-body">
                    Usuario bloqueado y querella resuelta correctamente.
                </div>

                <button type="button"
                        class="btn-close p-0 ms-1 me-2 market-toast-close"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar">
                </button>
            </div>
        </div>

    </div>

    {{--Post details modal. It opens when the administrator selects the view publication
        action from the report row.--}}
    <div class="modal fade" id="postDetailsModal" tabindex="-1" aria-labelledby="postDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow overflow-hidden">

                   {{--Modal header. Provides the title and close button. The JS handles the post content--}}
                    <div class="modal-header border-0 pt-4 px-4 pb-2 align-items-start position-relative">
                        <div class="pe-5">
                            <h4 class="modal-title fw-bold mb-1" id="postDetailsModalLabel">Detalle de la publicación</h4>
                            <p class="text-muted mb-0">Detalles de la Publicación</p>
                        </div>

                        {{--Closes the post details modal--}}
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    {{--Modal body. Contains the image carousel and publication metadata that are populated dynamically--}}
                    <div class="modal-body px-4 pt-2 pb-4 post-details-body">

                        {{--Post image carousel. JS injects the carousel indicators and image slides.
                            Previous/next controls allows the admin to review all the imgaes attached to the
                            specific post--}}
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

                        {{--Optional publication/post description. It is hidden by default
                            and shown only when the selected post includes a description--}}
                        <p class="mb-3 text-muted d-none" id="postDetailsDescription"></p>

                        <hr>

                        {{--Publication/post information summary, default values are placeholders and are replaced
                            by the JS contents since a specific post is tired to a rpeort/querella.
                            Opens when the administrator selects the action to view the post--}}
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
