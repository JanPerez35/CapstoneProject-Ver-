{{--
    Facility Cost Event Row Partial

    Renders one table row for a facility cost report item. The row can represent:
    - a parent/main event
    - a related area sub-event
    - a custom-day modification sub-event

    Expected variables:
    - $item: FacilityCostReportItem instance being rendered.
    - $groupKey: Identifier used to group parent and child event rows.
    - $rowType: Optional value. When set to "child", the row is styled and treated as a sub-event.

    Main responsibilities:
    - determine whether the row is a parent or child event
    - display event labels such as Principal, Área relacionada, or Modificación
    - expose event data through data-* attributes for JavaScript modals/actions
    - display formatted dates, responsible person, classroom, description, schedule, period type, rate mode, services, and cost
    - enable or disable action radios depending on the event type

    Action rules:
    - Parent events can be edited, customized, used to create related areas, and deleted.
    - Related area sub-events can be edited, customized, used to create more related areas, and deleted.
    - Custom-day sub-events can be edited and deleted, but cannot be used to create another custom-day modification.
--}}
@php
    /*
     * Identify the type of row being rendered and determine which actions
     * should be available for this item.
     */
    $isChild = ($rowType ?? '') === 'child';
    $isRelatedArea = $item->sub_event_type === 'related_area';
    $isCustomDay = $item->sub_event_type === 'custom_day';

    $canEditEvent = !$isChild || $isRelatedArea || $isCustomDay;
    $canCustomizeDays = !$isChild || $isRelatedArea;
    $canCreateRelated = !$isChild || $isRelatedArea;
@endphp

<tr
    class="{{ $isChild ? 'sub-event-row sub-event-' . ($item->sub_event_type ?? 'default') : 'parent-event-row' }}"
    data-group-key="{{ $groupKey }}"
    data-entry-id="{{ $item->id }}"
    data-parent-entry-id="{{ $item->custom_parent_item_id ?? '' }}"
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
    data-classroom="{{ $item->facilityCost?->classroom_name ?? 'N/A' }}"
    data-month="{{ \Carbon\Carbon::parse($item->event_date)->format('n') }}"
    data-year="{{ \Carbon\Carbon::parse($item->event_date)->format('Y') }}"
>
    <td class="align-middle">
        <div class="position-relative d-inline-block">
            @if($isChild)
                <span class="sub-event-arrow position-absolute" style="top: -33px; left: 2px;">
                ↳
            </span>

                @if($item->sub_event_type === 'related_area')
                    <span class="label-badge bg-info-subtle text-info-emphasis position-absolute text-nowrap" style="top: -26px; left: 15px;">
                    Área relacionada
                </span>
                @elseif($item->sub_event_type === 'custom_day')
                    <span class="label-badge bg-warning-subtle text-warning-emphasis position-absolute text-nowrap" style="top: -26px; left: 15px;">
                    Modificación
                </span>
                @else
                    <span class="label-badge bg-secondary-subtle text-secondary-emphasis position-absolute text-nowrap" style="top: -26px; left: 15px;">
                    Sub-evento
                </span>
                @endif
            @else
                <span class="label-badge bg-success-subtle text-success-emphasis position-absolute text-nowrap" style="top: -26px; left: 0;">
                Principal
            </span>
            @endif

            <span>
            {{ \Illuminate\Support\Str::title(
               \Carbon\Carbon::parse($item->event_date)
                   ->locale('es')
                   ->translatedFormat('j F Y')
            ) }}
        </span>
        </div>
    </td>

   <td>
       {{ \Illuminate\Support\Str::title(
         \Carbon\Carbon::parse($item->end_date ?? $item->event_date)
        ->locale('es')
        ->translatedFormat('j F Y')
       ) }}
   </td>

    <td>{{ $item->responsible }}</td>
    <td>{{ $item->facilityCost?->classroom_name ?? 'N/A' }}</td>
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


            @php
                $services = $item->services ?? [];

                if (is_string($services)) {
                    $services = json_decode($services, true) ?: [];
                }
            @endphp

            @foreach ($services as $service)
            @php
                $badgeClass = match($service) {
                    'electricity' => 'badge-electricidad',
                    'water'       => 'badge-agua',
                    default       => 'badge-available',
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

    <td class="text-end fw-semibold" style="min-width: 140px;">
        ${{ number_format($item->calculated_cost, 2) }}
    </td>

    <td class="text-center action-radio-cell px-2" style="width: 58px; min-width: 58px;">
        {{--
            Action radio buttons:
            - edit-cost-row-btn opens the edit modal
            - customize-days-btn opens the custom-day modification modal
            - create-related-btn opens the related-area modal
            - delete-cost-row-btn opens/executes the delete flow

            Some buttons are disabled depending on whether the item is a parent event,
            related area, or custom-day modification.
        --}}
        <input
            class="form-check-input action-radio edit-cost-row-btn"
            type="radio"
            name="facility_action_{{ $item->id }}"
            data-entry-id="{{ $item->id }}"
            {{ !$canEditEvent ? 'disabled' : '' }}
        >
    </td>

    <td class="text-center action-radio-cell px-2" style="width: 58px; min-width: 58px;">
        <input
            class="form-check-input action-radio customize-days-btn"
            type="radio"
            name="facility_action_{{ $item->id }}"
            data-entry-id="{{ $item->id }}"
            {{ !$canCustomizeDays ? 'disabled' : '' }}
        >
    </td>

    <td class="text-center action-radio-cell px-2" style="width: 58px; min-width: 58px;">
        <input
            class="form-check-input action-radio create-related-btn"
            type="radio"
            name="facility_action_{{ $item->id }}"
            data-entry-id="{{ $item->id }}"
            {{ !$canCreateRelated ? 'disabled' : '' }}
        >
    </td>

    <td class="text-center action-radio-cell px-2" style="width: 58px; min-width: 58px;">
        <input
            class="form-check-input action-radio delete-cost-row-btn"
            type="radio"
            name="facility_action_{{ $item->id }}"
            data-entry-id="{{ $item->id }}"
            data-delete-url="{{ route('facility.events.destroy', $item->id) }}"
        >
    </td>
