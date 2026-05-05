@php
    $isChild = ($rowType ?? '') === 'child';
    $isRelatedArea = $item->sub_event_type === 'related_area';
    $isCustomDay = $item->sub_event_type === 'custom_day';
    $canUseParentActions = !$isChild || $isRelatedArea;
@endphp

<tr
    class="{{ $isChild ? 'sub-event-row sub-event-' . ($item->sub_event_type ?? 'default') : 'parent-event-row' }}"
    data-group-key="{{ $groupKey }}"
    data-entry-id="{{ $item->id }}"
    data-sub-event-type="{{ $item->sub_event_type }}"
    data-date="{{ \Carbon\Carbon::parse($item->event_date)->format('Y-m-d') }}"
    data-end-date="{{ \Carbon\Carbon::parse($item->end_date ?? $item->event_date)->format('Y-m-d') }}"
    data-responsible="{{ $item->responsible }}"
    data-description="{{ $item->event_description }}"
    data-start-time="{{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }}"
    data-end-time="{{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }}"
    data-period-type="{{ $item->period_type }}"
    data-rate-mode="{{ $item->rate_mode }}"
    data-services='@json($item->services ?? [])'
    data-classroom="{{ $item->facilityCost->classroom_name }}"
    data-month="{{ \Carbon\Carbon::parse($item->event_date)->format('n') }}"
    data-year="{{ \Carbon\Carbon::parse($item->event_date)->format('Y') }}"
>
    <td>
        @if($isChild)
            <span class="sub-event-arrow">↳</span>

            @if($item->sub_event_type === 'related_area')
                <span class="badge bg-info-subtle text-info-emphasis me-1">
                    Área relacionada
                </span>
            @elseif($item->sub_event_type === 'custom_day')
                <span class="badge bg-warning-subtle text-warning-emphasis me-1">
                    Modificación
                </span>
            @else
                <span class="badge bg-secondary-subtle text-secondary-emphasis me-1">
                    Sub-evento
                </span>
            @endif
        @else
            <span class="badge bg-success-subtle text-success-emphasis me-1">
                Principal
            </span>
        @endif

        {{ \Carbon\Carbon::parse($item->event_date)->format('m/d/Y') }}
    </td>

    <td>{{ \Carbon\Carbon::parse($item->end_date ?? $item->event_date)->format('m/d/Y') }}</td>
    <td>{{ $item->responsible }}</td>
    <td>{{ $item->facilityCost->classroom_name }}</td>
    <td>{{ $item->event_description }}</td>

    <td>
        {{ \Carbon\Carbon::parse($item->start_time)->format('h:i A') }}
        -
        {{ \Carbon\Carbon::parse($item->end_time)->format('h:i A') }}
    </td>

    <td>
        @if ($item->period_type === 'workday')
            Laborable
        @elseif ($item->period_type === 'non_workday_saturday')
            No laborable sábado
        @elseif ($item->period_type === 'non_workday_sunday_holiday')
            No laborable domingo o festivo
        @else
            {{ $item->period_type }}
        @endif
    </td>

    <td>
        @if ($item->rate_mode === 'daily')
            Diario
        @elseif ($item->rate_mode === 'weekly')
            Semanal
        @elseif ($item->rate_mode === 'monthly')
            Mensual
        @else
            {{ $item->rate_mode }}
        @endif
    </td>

    <td>
        @foreach (($item->services ?? []) as $service)
            @php
                $badgeClass = match($service) {
                    'electricity' => 'badge-electricidad',
                    'water' => 'badge-agua',
                    'utilities' => 'badge-available',
                    default => 'badge-available',
                };
            @endphp

            <span class="label-badge {{ $badgeClass }} me-2 mb-1">
                @if ($service === 'utilities')
                    Utilidades
                @elseif ($service === 'electricity')
                    Electricidad
                @elseif ($service === 'water')
                    Agua
                @else
                    {{ ucfirst($service) }}
                @endif
            </span>
        @endforeach
    </td>

    <td class="text-end fw-semibold">${{ number_format($item->calculated_cost, 2) }}</td>

    <td class="text-center action-col">
        <div class="d-flex justify-content-center gap-2 flex-nowrap">

            @if($canUseParentActions)
                <button type="button"
                        class="btn btn-sm btn-outline-primary edit-cost-row-btn"
                        data-entry-id="{{ $item->id }}"
                        data-bs-toggle="modal"
                        data-bs-target="#editEventModal">
                    <i class="bi bi-pencil me-1"></i> Editar
                </button>

                <button type="button"
                        class="btn btn-sm btn-outline-warning customize-days-btn"
                        data-entry-id="{{ $item->id }}"
                        data-bs-toggle="modal"
                        data-bs-target="#customizeDaysModal">
                    <i class="bi bi-calendar-event me-1"></i> Modificar Días
                </button>

                <button type="button"
                        class="btn btn-sm btn-outline-success create-related-btn"
                        data-entry-id="{{ $item->id }}"
                        data-bs-toggle="modal"
                        data-bs-target="#createRelatedModal">
                    <i class="bi bi-diagram-3 me-1"></i> Crear Evento Relacionado
                </button>
            @endif

            @if($isCustomDay)
                <button type="button"
                        class="btn btn-sm btn-outline-primary edit-sub-event-btn"
                        data-entry-id="{{ $item->id }}"
                        data-bs-toggle="modal"
                        data-bs-target="#createRelatedModal">
                    <i class="bi bi-pencil me-1"></i> Editar Modificación
                </button>
            @endif

            <button type="button"
                    class="btn btn-sm btn-outline-danger delete-cost-row-btn"
                    data-entry-id="{{ $item->id }}"
                    data-delete-url="{{ route('facility.events.destroy', $item->id) }}"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteCostEntryModal">
                <i class="bi bi-trash me-1"></i> Eliminar
            </button>

        </div>
    </td>
</tr>