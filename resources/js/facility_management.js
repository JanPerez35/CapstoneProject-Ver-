import * as bootstrap from 'bootstrap';
document.addEventListener('DOMContentLoaded', () => {
    const $ = (id) => document.getElementById(id);

    const downloadCsvBtn = $('downloadCsvBtn');
    const downloadPdfBtn = $('downloadPdfBtn');

    const newClassroomName = $('newClassroomName');
    const newClassroomNameError = $('newClassroomNameError');
    const confirmAddClassroomBtn = $('confirmAddClassroomBtn');
    const addClassroomModal = $('addClassroomModal');

    const facilitySearch = $('facilitySearch');
    const searchFacilityBtn = $('searchFacilityBtn');

    const FACILITY_COSTS_PER_PAGE = 10;
    let currentFacilityCostsPage = 1;
    const facilityCostPagination = $('facilityCostPagination');

    const reportType = $('reportType');
    const monthFilterWrapper = $('monthFilterWrapper');
    const reportMonth = $('reportMonth');
    const reportYear = $('reportYear');
    const filterClassroom = $('filterClassroom');
    const clearFacilityFilters = $('clearFacilityFilters');

    const cancelAddClassroomBtn = $('cancelAddClassroomBtn');
    const closeAddClassroomBtn = $('closeAddClassroomBtn');

    const facilityCostTable = $('facilityCostTable');
    const facilityCostTableBody = $('facilityCostTableBody');
    const facilityCostEmptyState = $('facilityCostEmptyState');
    const facilityCostGrandTotal = $('facilityCostGrandTotal');

    const configureRatesModal = $('configureRatesModal');
    const addRentalModal = $('addRentalModal');

    const configureRatesForm = $('configureRatesForm');
    const addRentalForm = $('addRentalForm');

    const saveRatesBtn = $('saveRatesBtn');
    const saveRentalBtn = $('saveRentalBtn');

    const getConfigClassroomChecks = () => [...document.querySelectorAll('.config-classroom-check')];
    const moneyInputs = [...document.querySelectorAll('.money-input')];
    const openDiscardSelectedClassroomsBtn = $('openDiscardSelectedClassroomsBtn');
    const deleteClassroomNameText = $('deleteClassroomNameText');
    const confirmDeleteClassroomBtn = $('confirmDeleteClassroomBtn');

    const configAreaSalon = $('configAreaSalon');
    const configUtilidades = $('configUtilidades');
    const configElectricidad = $('configElectricidad');
    const configAgua = $('configAgua');
    const configDiaria1 = $('configDiaria1');
    const configSemanal1 = $('configSemanal1');
    const configMensual1 = $('configMensual1');
    const configDiaria2 = $('configDiaria2');
    const configSemanal2 = $('configSemanal2');
    const configMensual2 = $('configMensual2');
    const configDiaria3 = $('configDiaria3');
    const configSemanal3 = $('configSemanal3');
    const configMensual3 = $('configMensual3');

    const configPreviewLaborable = $('configPreviewLaborable');
    const configPreviewSabado = $('configPreviewSabado');
    const configPreviewDomingo = $('configPreviewDomingo');

    const rentalClassroom = $('rentalClassroom');
    const rentalResponsable = $('rentalResponsable');
    const rentalStartTime = $('rentalStartTime');
    const rentalEndTime = $('rentalEndTime');
    const rentalDescripcion = $('rentalDescripcion');
    const rentalPeriodType = $('rentalPeriodType');

    const rentalRangeType = $('rentalRangeType');
    const rentalStartDate = $('rentalStartDate');
    const rentalEndDate = $('rentalEndDate');
    const rentalEndDateRow = $('rentalEndDateRow');
    const rentalStartDateLabel = $('rentalStartDateLabel');

    const rentalUtilities = $('rentalUtilities');
    const rentalElectricity = $('rentalElectricity');
    const rentalWater = $('rentalWater');
    const rentalServiceChecks = [rentalUtilities, rentalElectricity, rentalWater];

    rentalServiceChecks.forEach(input => {
        input.addEventListener('change', () => {
            servicesTouched = true;
            updateRentalSaveState();
        });
    });

    const rentalEstimatedTotal = $('rentalEstimatedTotal');
    const rentalEstimatedTotalInput = $('rentalEstimatedTotalInput');
    const detectedPeriodLabel = $('detectedPeriodLabel');
    const detectedHoursLabel = $('detectedHoursLabel');
    const servicesRequiredMessage = $('servicesRequiredMessage');
    const rentalResponsableError = $('rentalResponsableError');
    const rentalDescripcionError = $('rentalDescripcionError');

    const confirmDeleteCostEntryBtn = $('confirmDeleteCostEntryBtn');
    const deleteButtons = () => [...document.querySelectorAll('.delete-cost-row-btn')];


    const createToastInstance = (id) => {
        const el = $(id);
        return el ? bootstrap.Toast.getOrCreateInstance(el, {delay: 2500}) : null;
    };

    const toasts = {
        ratesSaved: createToastInstance('ratesSavedToast'),
        rentalSaved: createToastInstance('rentalSavedToast'),
        deleteEntry: createToastInstance('deleteEntryToast'),
        download: createToastInstance('downloadToast'),
    };

    function updateFacilitySearchButtonState() {
        if (!facilitySearch || !searchFacilityBtn) return;

        const value = facilitySearch.value;
        searchFacilityBtn.disabled = value.trim() === '';
    }

    let selectedDeleteUrl = null;
    let selectedClassroomsToDelete = [];
    // let nextEntryId = 3;
    let configureDirty = false;
    let rentalDirty = false;
    let servicesTouched = false;

    const facilityConfig = window.facilityManagementConfig || {};
    const tarifasPorSalon = facilityConfig.tarifasPorSalon || {};


    function clearRatesForm() {
        [
            configAreaSalon, configUtilidades, configElectricidad, configAgua,
            configDiaria1, configSemanal1, configMensual1,
            configDiaria2, configSemanal2, configMensual2,
            configDiaria3, configSemanal3, configMensual3,
        ].forEach((input) => {
            input.value = '';
        });

        updateConfigPreview();
    }

    if (addClassroomModal && configureRatesModal) {
        let shouldReturnToConfigureRates = false;

        const openAddClassroomModalBtn = $('openAddClassroomModalBtn');

        if (openAddClassroomModalBtn) {
            openAddClassroomModalBtn.addEventListener('click', () => {
                shouldReturnToConfigureRates = true;
            });
        }

        if (cancelAddClassroomBtn) {
            cancelAddClassroomBtn.addEventListener('click', () => {
                shouldReturnToConfigureRates = true;
            });

        }

        if (closeAddClassroomBtn) {
            closeAddClassroomBtn.addEventListener('click', () => {
                shouldReturnToConfigureRates = true;
            });
        }

        addClassroomModal.addEventListener('hidden.bs.modal', () => {
            newClassroomName.value = '';
            newClassroomName.classList.remove('is-invalid');
            newClassroomNameError.textContent = '';
            confirmAddClassroomBtn.disabled = true;

            if (shouldReturnToConfigureRates) {
                bootstrap.Modal.getOrCreateInstance(configureRatesModal).show();
                shouldReturnToConfigureRates = false;
            }
        });
    }

    function setAddClassroomError(message = '') {
        if (!newClassroomName || !newClassroomNameError) return;

        newClassroomName.classList.toggle('is-invalid', !!message);
        newClassroomNameError.textContent = message;
    }

    function validateNewClassroomName(showError = true) {
        if (!newClassroomName || !confirmAddClassroomBtn) return false;

        const value = newClassroomName.value.trim();
        const allowedRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 ,.\-]+$/;

        const alreadyExists = [...document.querySelectorAll('.classroom-card-col')]
            .some((card) => card.dataset.classroomName?.toLowerCase() === value.toLowerCase());

        if (!value) {
            if (showError) setAddClassroomError('Este campo es requerido.');
            return false;
        }

        if (value.length < 6) {
            if (showError) setAddClassroomError('El nombre del salón debe tener al menos 6 caracteres.');
            return false;
        }

        if (value.length > 40) {
            if (showError) setAddClassroomError('El nombre del salón no puede exceder 40 caracteres.');
            return false;
        }

        if (!allowedRegex.test(value)) {
            if (showError) {
                setAddClassroomError('Solo se permiten letras, números, espacios, punto, coma y guion.');
            }
            return false;
        }

        if (alreadyExists) {
            if (showError) setAddClassroomError('Ese salón ya existe.');
            return false;
        }

        if (showError) setAddClassroomError('');
        return true;
    }

    function updateAddClassroomButtonState() {
        if (!confirmAddClassroomBtn) return;
        confirmAddClassroomBtn.disabled = !validateNewClassroomName(false);
    }

    function getAllRenderedClassrooms() {
        return [...document.querySelectorAll('.classroom-card-col')]
            .map(card => card.dataset.classroomName)
            .filter(Boolean);
        }

        function getAcademicRenderedClassrooms() {
            return getAllRenderedClassrooms().filter(name => /^CM\s?\d+/i.test(name));
        }

        function getLateralRenderedClassrooms() {
            return getAllRenderedClassrooms().filter(name => /lateral/i.test(name));
        }

    if (newClassroomName) {
        newClassroomName.addEventListener('input', () => {
            let value = newClassroomName.value;
            const allowedRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 ,.\-]*$/;

            // Detect if user tried to exceed 40
            const exceeded = value.length > 40;

            // Cut to 40 (prevents typing more)
            if (exceeded) {
                value = value.slice(0, 40);
                newClassroomName.value = value;
            }

            const trimmedValue = value.trim();

            const alreadyExists = [...document.querySelectorAll('.classroom-card-col')]
                .some((card) => card.dataset.classroomName?.toLowerCase() === trimmedValue.toLowerCase());

            if (!trimmedValue) {
                setAddClassroomError('');
            } else if (!allowedRegex.test(value)) {
                setAddClassroomError('Solo se permiten letras, números, espacios, punto, coma y guion.');
            } else if (trimmedValue.length < 6) {
                setAddClassroomError('El nombre del salón debe tener al menos 6 caracteres.');
            } else if (exceeded) {
                setAddClassroomError('El nombre del salón no puede exceder 40 caracteres.');
            } else if (alreadyExists) {
                setAddClassroomError('Ese salón ya existe.');
            } else {
                setAddClassroomError('');
            }

            updateAddClassroomButtonState();
        });
    }


    if (confirmAddClassroomBtn) {
        confirmAddClassroomBtn.addEventListener('click', () => {
            if (!validateNewClassroomName(true)) {
                updateAddClassroomButtonState();
                return;
            }

            document.getElementById('hiddenNewClassroomName').value =
                newClassroomName.value.trim();

            sessionStorage.setItem('reopenConfigureRatesModal', 'true');
            document.getElementById('addClassroomForm').submit();
        });
    }

    function requiresEndDate() {
        return ['weekly', 'monthly'].includes(rentalRangeType?.value);
    }

    function toggleRentalDateRangeUI() {
        const showEndDate = requiresEndDate();

        rentalEndDateRow.classList.toggle('d-none', !showEndDate);
        rentalStartDateLabel.innerHTML = showEndDate
            ? 'Fecha de inicio <span class="text-danger">*</span>'
            : 'Fecha <span class="text-danger">*</span>';

        if (!showEndDate) {
            rentalEndDate.value = '';
            clearFieldError(rentalEndDate, $('rentalEndDateError'));
        }

        if (rentalStartDate.value && requiresEndDate()) {
            const nextDay = new Date(`${rentalStartDate.value}T00:00:00`);
            nextDay.setDate(nextDay.getDate() + 1);
            rentalEndDate.min = nextDay.toISOString().split('T')[0];
        } else if (rentalStartDate.value) {
            rentalEndDate.min = rentalStartDate.value;
        } else {
            rentalEndDate.min = new Date().toISOString().split('T')[0];
        }
    }

    function validateRentalDates(showError = true) {
        const startError = $('rentalStartDateError');
        const endError = $('rentalEndDateError');

        if (showError) {
            clearFieldError(rentalStartDate, startError);
            clearFieldError(rentalEndDate, endError);
        }

        const startValue = rentalStartDate.value;
        const endValue = rentalEndDate.value;
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (!startValue) {
            if (showError) {
                setFieldError(rentalStartDate, startError, 'La fecha de inicio es requerida.');
            }
            return false;
        }

        const startDate = new Date(`${startValue}T00:00:00`);

        if (startDate < today) {
            if (showError) {
                setFieldError(rentalStartDate, startError, 'No puedes seleccionar una fecha anterior a hoy.');
            }
            return false;
        }

        if (!requiresEndDate()) {
            return true;
        }

        if (!endValue) {
            if (showError) {
                setFieldError(rentalEndDate, endError, 'La fecha de fin es requerida.');
            }
            return false;
        }

        const endDate = new Date(`${endValue}T00:00:00`);

        if (endDate < today) {
            if (showError) {
                setFieldError(rentalEndDate, endError, 'No puedes seleccionar una fecha anterior a hoy.');
            }
            return false;
        }

        if (endDate <= startDate) {
            if (showError) {
                setFieldError(
                    rentalEndDate,
                    endError,
                    'La fecha de fin debe ser posterior a la fecha de inicio para eventos semanales o mensuales.'
                );
                rentalStartDate.classList.add('is-invalid');
                rentalEndDate.classList.add('is-invalid');
            }
            return false;
        }
        return true;
    }

    const formatMoney = (value) => Number(value).toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    const parseMoney = (text) => Number(String(text).replace(/[^0-9.]/g, '')) || 0;
    const toNumber = (value) => Number(String(value).replace(/[^0-9.]/g, '')) || 0;

    const MAX_RATE_PRICE = 500;
    const MAX_AREA_SQFT = 25000000.00;

    function sanitizeMoneyInput(input) {
        let value = input.value.replace(/[^0-9.]/g, '');

        if (value.startsWith('.')) {
            value = '0' + value;
        }

        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }

        const firstDotIndex = value.indexOf('.');
        if (firstDotIndex !== -1) {
            const integerPart = value.slice(0, firstDotIndex);
            const decimalPart = value.slice(firstDotIndex + 1).slice(0, 2);
            value = integerPart + '.' + decimalPart;
        }

        const numericValue = Number(value);

        if (value && !Number.isNaN(numericValue) && numericValue > MAX_RATE_PRICE) {
            const errorElement = document.getElementById(`${input.id}Error`);
            const inputGroup = input.closest('.money-input-group');

            setFieldError(
                input,
                errorElement,
                'El valor no puede exceder $500.00.'
            );
            inputGroup?.classList.add('is-invalid');

            const previousValidValue = input.dataset.previousValidValue || '';
            input.value = previousValidValue || '';
        } else {
            input.value = value;
            input.dataset.previousValidValue = value;
            validateMoneyField(input, true);
        }
    }

    function normalizeMoneyInput(input) {
        const rawValue = input.value.trim();

        if (!rawValue){
            validateMoneyField(input, true);
            return;
        }

        const numericValue = Number(rawValue);
        const errorElement = document.getElementById(`${input.id}Error`);
        const inputGroup = input.closest('.money-input-group');


        if (Number.isNaN(numericValue)) {
            input.value = '';
            validateMoneyField(input, true);
            return;
        }

        if (numericValue > MAX_RATE_PRICE) {
            input.value = input.dataset.previousValidValue || '';
            setFieldError(
                input,
                errorElement,
                'El valor no puede exceder $500.00.'
            );

            inputGroup?.classList.add('is-invalid');
            return;

        }
            input.value = numericValue.toFixed(2);
            input.dataset.previousValidValue = input.value;
            validateMoneyField(input, true);

    }

    function sanitizeAreaInput(input) {
        let value = input.value.replace(/[^0-9.]/g, '');

        if (value.startsWith('.')) {
            value = '0' + value;
        }

        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }

        const firstDotIndex = value.indexOf('.');
        if (firstDotIndex !== -1) {
            const integerPart = value.slice(0, firstDotIndex);
            const decimalPart = value.slice(firstDotIndex + 1).slice(0, 2);
            value = integerPart + '.' + decimalPart;
        }

        const numericValue = Number(value);
        const errorElement = document.getElementById(`${input.id}Error`);
        const inputGroup = input.closest('.money-input-group');

        if (value && !Number.isNaN(numericValue) && numericValue > MAX_AREA_SQFT) {
            setFieldError(input, errorElement, `El área no puede exceder ${MAX_AREA_SQFT.toFixed(2)} ft².`);
            inputGroup?.classList.add('is-invalid');

            const previousValidValue = input.dataset.previousValidValue || '';
            input.value = previousValidValue || '';
        } else {
            input.value = value;
            input.dataset.previousValidValue = value;
            validateAreaField(input, true);
        }
    }

    function normalizeAreaInput(input) {
        const rawValue = input.value.trim();
        const errorElement = document.getElementById(`${input.id}Error`);
        const inputGroup = input.closest('.money-input-group');

        if (!rawValue) {
            validateAreaField(input, true);
            return;
        }

        const numericValue = Number(rawValue);

        if (Number.isNaN(numericValue)) {
            input.value = '';
            validateAreaField(input, true);
            return;
        }

        if (numericValue > MAX_AREA_SQFT) {
            input.value = input.dataset.previousValidValue || '';
            setFieldError(input, errorElement, `El área no puede exceder ${MAX_AREA_SQFT.toFixed(2)} ft².`);
            inputGroup?.classList.add('is-invalid');
            return;
        }

        input.value = numericValue.toFixed(2);
        input.dataset.previousValidValue = input.value;
        validateAreaField(input, true);
    }

    function timeToMinutes(timeValue) {
        if (!timeValue) return 0;
        const [hour, minute] = timeValue.split(':').map(Number);
        return (hour * 60) + minute;
    }

    function calculateHours(start, end) {
        const diff = timeToMinutes(end) - timeToMinutes(start);
        return diff > 0 ? diff / 60 : 0;
    }

    function getSelectedClassrooms() {
        return getConfigClassroomChecks()
            .filter(check => check.checked)
            .map(check => check.value);
    }

    function removeSelectedClassroomsFromUI(classrooms) {
        classrooms.forEach((classroomName) => {
            const cardCol = document.querySelector(`.classroom-card-col[data-classroom-name="${classroomName}"]`);
            if (cardCol) {
                cardCol.remove();
            }
            const classroomOption = filterClassroom.querySelector(`option[value="${classroomName}"]`);
            if (classroomOption) {
                classroomOption.remove();
            }
            const rentalOption = rentalClassroom.querySelector(`option[value="${classroomName}"]`);
            if (rentalOption) {
                rentalOption.remove();
            }

        });
    }

    const ratesSavedAutoTrigger = document.getElementById('ratesSavedAutoTrigger');
    const rentalSavedAutoTrigger = document.getElementById('rentalSavedAutoTrigger');
    const mockImportAutoTrigger = document.getElementById('mockImportAutoTrigger');

    if (ratesSavedAutoTrigger && toasts.ratesSaved) {
        toasts.ratesSaved.show();
    }

    if (mockImportAutoTrigger) {
        const mockImportToastEl = document.getElementById('mockImportToast');
        if (mockImportToastEl) {
            const mockImportToast = bootstrap.Toast.getOrCreateInstance(mockImportToastEl, { delay: 3000 });
            mockImportToast.show();
        }
    }

    if (rentalSavedAutoTrigger && toasts.rentalSaved) {
        toasts.rentalSaved.show();
    }

    if (clearFacilityFilters) {
        clearFacilityFilters.addEventListener('click', () => {
            if (facilitySearch) facilitySearch.value = '';
            updateFacilitySearchButtonState();

            window.location.href = facilityConfig.facilityManagementUrl || window.location.pathname;
        });
    }

    if (openDiscardSelectedClassroomsBtn && deleteClassroomNameText && $('deleteClassroomModal')) {
        openDiscardSelectedClassroomsBtn.addEventListener('click', () => {
            selectedClassroomsToDelete = getSelectedClassrooms();

            if (!selectedClassroomsToDelete.length) {
                return;
            }

            deleteClassroomNameText.innerHTML = selectedClassroomsToDelete
                .map((name) => `<div>• ${name}</div>`)
                .join('');

            bootstrap.Modal.getOrCreateInstance($('deleteClassroomModal')).show();
        });
    }

    if (confirmDeleteClassroomBtn && $('deleteClassroomModal')) {
        confirmDeleteClassroomBtn.addEventListener('click', () => {
            if (!selectedClassroomsToDelete.length) return;

            const deleteForm = document.getElementById('deleteClassroomsForm');
            deleteForm.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
            <input type="hidden" name="_method" value="DELETE">
        `;

            selectedClassroomsToDelete.forEach((classroomName) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'classrooms[]';
                input.value = classroomName;
                deleteForm.appendChild(input);
            });

            sessionStorage.setItem('reopenConfigureRatesModal', 'true');
            deleteForm.submit();
        });
    }

    function setSelectionByList(list) {
        getConfigClassroomChecks().forEach(check => {
            check.checked = list.includes(check.value);
        });
        configureDirty = true;
        updateConfigureSaveState();
    }

    // function loadRatesIntoForm(classroomId) {
    //     const data = tarifasPorSalon[classroomId];
    //     if (!data) return;
    //
    //     configAreaSalon.value = Number(data.area).toFixed(2);
    //     configUtilidades.value = Number(data.utilidades).toFixed(2);
    //     configElectricidad.value = Number(data.electricidad).toFixed(2);
    //     configAgua.value = Number(data.agua).toFixed(2);
    //     configDiaria1.value = Number(data.diaria1).toFixed(2);
    //     configSemanal1.value = Number(data.semanal1).toFixed(2);
    //     configMensual1.value = Number(data.mensual1).toFixed(2);
    //     configDiaria2.value = Number(data.diaria2).toFixed(2);
    //     configSemanal2.value = Number(data.semanal2).toFixed(2);
    //     configMensual2.value = Number(data.mensual2).toFixed(2);
    //     configDiaria3.value = Number(data.diaria3).toFixed(2);
    //     configSemanal3.value = Number(data.semanal3).toFixed(2);
    //     configMensual3.value = Number(data.mensual3).toFixed(2);
    //
    //     updateConfigPreview();
    // }

    function loadRatesIntoForm(classroomId) {
        const data = tarifasPorSalon[classroomId];

        if (!data || !data.configured) {
            clearRatesForm();
            return;
        }

        configAreaSalon.value = Number(data.area).toFixed(2);
        configUtilidades.value = Number(data.utilidades).toFixed(2);
        configElectricidad.value = Number(data.electricidad).toFixed(2);
        configAgua.value = Number(data.agua).toFixed(2);

        configDiaria1.value = Number(data.diaria1).toFixed(2);
        configSemanal1.value = Number(data.semanal1).toFixed(2);
        configMensual1.value = Number(data.mensual1).toFixed(2);

        configDiaria2.value = Number(data.diaria2).toFixed(2);
        configSemanal2.value = Number(data.semanal2).toFixed(2);
        configMensual2.value = Number(data.mensual2).toFixed(2);

        configDiaria3.value = Number(data.diaria3).toFixed(2);
        configSemanal3.value = Number(data.semanal3).toFixed(2);
        configMensual3.value = Number(data.mensual3).toFixed(2);

        updateConfigPreview();
    }

    function updateConfigPreview() {
        const area = toNumber(configAreaSalon.value);
        configPreviewLaborable.textContent = formatMoney((area * toNumber(configDiaria1.value)).toFixed(2));
        configPreviewSabado.textContent = formatMoney((area * toNumber(configDiaria2.value)).toFixed(2));
        configPreviewDomingo.textContent = formatMoney((area * toNumber(configDiaria3.value)).toFixed(2));
    }

    function isConfigureFormValid(showError = false) {
    if (!getSelectedClassrooms().length) return false;

    const moneyFields = [
        configUtilidades, configElectricidad, configAgua,
        configDiaria1, configSemanal1, configMensual1,
        configDiaria2, configSemanal2, configMensual2,
        configDiaria3, configSemanal3, configMensual3,
    ];

    return (
        validateAreaField(configAreaSalon, showError) &&
        moneyFields.every((input) => validateMoneyField(input, showError))
    );
    }

    function updateConfigureSaveState() {
        saveRatesBtn.disabled = !isConfigureFormValid();
    }

    function periodLabelFromValue(value) {
        if (value === 'laborable') return 'Laborable';
        if (value === 'no_laborable_sabado') return 'No laborable sábado';
        if (value === 'no_laborable_domingo_festivo') return 'No laborable domingo o festivo';
        return '—';
    }

    function getPeriodRateData(classroomId, periodType) {
        const data = tarifasPorSalon[classroomId];
        if (!data) return {diaria: 0, semanal: 0, mensual: 0};

        if (periodType === 'laborable') return {
            diaria: toNumber(data.diaria1),
            semanal: toNumber(data.semanal1),
            mensual: toNumber(data.mensual1)
        };
        if (periodType === 'no_laborable_sabado') return {
            diaria: toNumber(data.diaria2),
            semanal: toNumber(data.semanal2),
            mensual: toNumber(data.mensual2)
        };
        if (periodType === 'no_laborable_domingo_festivo') return {
            diaria: toNumber(data.diaria3),
            semanal: toNumber(data.semanal3),
            mensual: toNumber(data.mensual3)
        };

        return {diaria: 0, semanal: 0, mensual: 0};
    }

    function hasSelectedServices() {
        return rentalServiceChecks.some(input => input.checked);
    }

    function toggleServicesError(show) {
        servicesRequiredMessage.classList.toggle('d-none', !show);
        servicesRequiredMessage.classList.toggle('d-block', show);
    }

    function setFieldError(field, errorElement, message) {
        if (!field) return;
        field.classList.add('is-invalid');
        if (errorElement) errorElement.textContent = message;
    }

    function clearFieldError(field, errorElement) {
        if (!field) return;
        field.classList.remove('is-invalid');
        if (errorElement) errorElement.textContent = '';
    }

    function validateMoneyField(input, showError = true) {
        if (!input) return true;

        const errorElement = document.getElementById(`${input.id}Error`);
        const inputGroup = input.closest('.money-input-group');
        const value = input.value.trim();

        if (showError) {
            clearFieldError(input, errorElement);
            inputGroup?.classList.remove('is-invalid');
        }

        if (!value) {
            if (showError) {
                setFieldError(input, errorElement, 'Este campo es obligatorio.');
                inputGroup?.classList.add('is-invalid');
            }
            return false;
        }

        if (/[eE+\-]/.test(value)) {
            if (showError) {
                setFieldError(input, errorElement, 'No se permite usar e, E, + ni -.');
                inputGroup?.classList.add('is-invalid');
            }
            return false;
        }

        if (!/^(?:0|[1-9]\d*)(?:\.\d{1,2})?$/.test(value)) {
            if (showError) {
                setFieldError(input, errorElement, 'Ingresa un valor válido usando solo números y hasta 2 decimales.');
                inputGroup?.classList.add('is-invalid');
            }
            return false;
        }

        if (Number(value) < 0) {
            if (showError) {
                setFieldError(input, errorElement, 'El valor no puede ser negativo.');
                inputGroup?.classList.add('is-invalid');
            }
            return false;
        }

        if (Number(value) > MAX_RATE_PRICE) {
            if (showError) {
                setFieldError(input, errorElement, 'El valor no puede exceder $500.00.');
                inputGroup?.classList.add('is-invalid');
            }
            return false;
        }

        return true;
    }

    function validateAreaField(input, showError = true) {
    if (!input) return true;

    const errorElement = document.getElementById(`${input.id}Error`);
    const inputGroup = input.closest('.money-input-group');
    const value = input.value.trim();

    if (showError) {
        clearFieldError(input, errorElement);
        inputGroup?.classList.remove('is-invalid');
    }

    if (!value) {
        if (showError) {
            setFieldError(input, errorElement, 'Este campo es obligatorio.');
            inputGroup?.classList.add('is-invalid');
        }
        return false;
    }

    if (/[eE+\-]/.test(value)) {
        if (showError) {
            setFieldError(input, errorElement, 'No se permite usar e, E, + ni -.');
            inputGroup?.classList.add('is-invalid');
        }
        return false;
    }

    if (!/^(?:0|[1-9]\d*)(?:\.\d{1,2})?$/.test(value)) {
        if (showError) {
            setFieldError(input, errorElement, 'Ingresa un valor válido usando solo números y hasta 2 decimales.');
            inputGroup?.classList.add('is-invalid');
        }
        return false;
    }

    if (Number(value) <= 0) {
        if (showError) {
            setFieldError(input, errorElement, 'El área debe ser mayor que 0.');
            inputGroup?.classList.add('is-invalid');
        }
        return false;
    }

        if (Number(value) > MAX_AREA_SQFT) {
            if (showError) {
                setFieldError(input, errorElement, `El área no puede exceder ${MAX_AREA_SQFT.toFixed(2)} ft².`);
                inputGroup?.classList.add('is-invalid');
            }
            return false;
        }

    return true;
    }

    function validateResponsable(showError = true) {
        const value = rentalResponsable.value.trim();
        const regex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;

        if (showError){
            clearFieldError(rentalResponsable, rentalResponsableError);
        }

        if (!value) {
            if (showError) {
                setFieldError(rentalResponsable, rentalResponsableError, 'El responsable es requerido.');
            }
            return false;
        }

        if (!regex.test(value)){
            if (showError){
                setFieldError(rentalResponsable, rentalResponsableError, 'Solo se permiten letras y espacios.');
            }
            return false;
        }

        if (value.length < 10) {
            if (showError) {
                setFieldError(rentalResponsable, rentalResponsableError, 'El responsable debe tener al menos 10 caracteres.');
            }
            return false;
        }

        if (value.length > 40) {
            if (showError) {
                setFieldError(rentalResponsable, rentalResponsableError, 'El responsable no puede exceder 40 caracteres.');
            }
            return false;
        }

        return true;
    }

    function validateDescripcion(showError = true) {
        const value = rentalDescripcion.value.trim();
        const regex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;

        if (showError) {
            clearFieldError(rentalDescripcion, rentalDescripcionError);
        }

        if (!value) {
            if (showError) {
                setFieldError(rentalDescripcion, rentalDescripcionError, 'La descripción es requerida.');
            }
            return false;
        }

        if(!regex.test(value)) {
            if(showError) {
                setFieldError(rentalDescripcion, rentalDescripcionError, 'Solo se permiten letras, números, espacios, punto, coma y guion.');
            }
            return false;
        }

        if (value.length < 10) {
            if (showError) setFieldError(rentalDescripcion, rentalDescripcionError, 'La descripción debe tener al menos 10 caracteres.');
            return false;
        }

        if (value.length > 500) {
            if (showError) setFieldError(rentalDescripcion, rentalDescripcionError, 'La descripción no puede exceder 500 caracteres.');
            return false;
        }


        return true;
    }

    if (configAreaSalon) {
        configAreaSalon.addEventListener('input', () => {
            sanitizeAreaInput(configAreaSalon);
            configureDirty = true;
            updateConfigPreview();
            updateConfigureSaveState();
        });

        configAreaSalon.addEventListener('blur', () => {
            normalizeAreaInput(configAreaSalon);
            updateConfigPreview();
            updateConfigureSaveState();
        });
    }

    function calculateRentalEstimate() {
        const classroomId = rentalClassroom.value;
        const startTime = rentalStartTime.value;
        const endTime = rentalEndTime.value;
        const periodType = rentalPeriodType.value;
        const hours = calculateHours(startTime, endTime);

        detectedPeriodLabel.textContent = periodLabelFromValue(periodType);
        detectedHoursLabel.textContent = `${hours.toFixed(2)} horas`;

        if (!classroomId || !hours || !periodType || !tarifasPorSalon[classroomId]) {
            rentalEstimatedTotal.textContent = formatMoney(0);
            rentalEstimatedTotalInput.value = '0.00';
            return 0;
        }

        const data = tarifasPorSalon[classroomId];
        const area = toNumber(data.area);
        const periodRates = getPeriodRateData(classroomId, periodType);

        let selectedRate = 0;

        if (rentalRangeType.value === 'daily') {
            selectedRate = periodRates.diaria;
        } else if (rentalRangeType.value === 'weekly') {
            selectedRate = periodRates.semanal;
        } else if (rentalRangeType.value === 'monthly') {
            selectedRate = periodRates.mensual;
        }

        let total = area * selectedRate;

        if (rentalUtilities.checked) total += toNumber(data.utilidades) * hours;
        if (rentalElectricity.checked) total += toNumber(data.electricidad) * hours;
        if (rentalWater.checked) total += toNumber(data.agua) * hours;

        rentalEstimatedTotal.textContent = formatMoney(total);
        rentalEstimatedTotalInput.value = total.toFixed(2);
        return total;
    }

    function isRentalFormValid() {
        const timeError = document.getElementById('rentalTimeError');

        let validTimes = true;

        rentalStartTime.classList.remove('is-invalid');
        rentalEndTime.classList.remove('is-invalid');

        if (timeError) {
            timeError.textContent = '';
        }

        if (!rentalStartTime.value || !rentalEndTime.value) {
            validTimes = false;
        } else if (timeToMinutes(rentalEndTime.value) <= timeToMinutes(rentalStartTime.value)) {
            validTimes = false;

            rentalStartTime.classList.add('is-invalid');
            rentalEndTime.classList.add('is-invalid');

            if (timeError) {
                timeError.textContent = 'La hora de fin debe ser mayor que la hora de inicio.';
            }
        }

        const validResponsable = validateResponsable(false);
        const validDescription = validateDescripcion(false);
        const validServices = hasSelectedServices();
        const validDates = validateRentalDates(false);

        if(servicesTouched){
            toggleServicesError(!validServices);
        } else {
            toggleServicesError(false);
        }
        return !!(
            rentalClassroom.value &&
            rentalRangeType.value &&
            validDates &&
            rentalPeriodType.value &&
            validTimes &&
            validResponsable &&
            validDescription &&
            validServices
        );
    }

    function updateRentalSaveState() {
        saveRentalBtn.disabled = !isRentalFormValid();
    }

    function toggleMonthFilter() {
       const isMonthly = reportType.value === 'monthly';

       monthFilterWrapper.classList.toggle('d-none', !isMonthly);

       if(!isMonthly && reportMonth) {
           reportMonth.value = '';
       }
    }

    function renderLocalPagination(container, currentPage, totalItems, itemsPerPage, onPageChange) {
        if (!container) return;

        const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));

        if (totalItems <= 0) {
            container.innerHTML = '';
            return;
        }

        let paginationHTML = '';

        paginationHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <button type="button" class="page-link" data-page="prev">&laquo;</button>
        </li>
    `;

        for (let page = 1; page <= totalPages; page++) {
            paginationHTML += `
            <li class="page-item ${page === currentPage ? 'active' : ''}">
                <button type="button" class="page-link" data-page="${page}">${page}</button>
            </li>
        `;
        }

        paginationHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <button type="button" class="page-link" data-page="next">&raquo;</button>
        </li>
    `;

        container.innerHTML = paginationHTML;

        container.querySelectorAll('.page-link').forEach((button) => {
            button.addEventListener('click', () => {
                const action = button.dataset.page;
                let newPage = currentPage;

                if (action === 'prev' && currentPage > 1) {
                    newPage = currentPage - 1;
                } else if (action === 'next' && currentPage < totalPages) {
                    newPage = currentPage + 1;
                } else if (!isNaN(action)) {
                    newPage = Number(action);
                }

                if (newPage !== currentPage) {
                    onPageChange(newPage);
                }
            });
        });
    }

    function triggerFacilityDownload(type) {
        if (toasts.download) {
            toasts.download.show();
        }

        const selectedType = reportType.value === 'annual' ? 'annual' : 'mensual';
        const selectedYear = reportYear.value;
        const selectedMonth = reportMonth.options[reportMonth.selectedIndex]?.text || '';
        const selectedClassroom = filterClassroom.options[filterClassroom.selectedIndex]?.text || 'Todos los salones';

        const visibleRows = getVisibleFacilityRows();

        let content = '';

        if (type === 'csv') {
            const headers = ['Fecha', 'Salón', 'Hora', 'Periodo', 'Servicios', 'Total'];

            const rows = visibleRows.map((row) => {
                const fecha = row.cells[0]?.textContent.trim() || '';
                const salon = row.cells[1]?.textContent.trim() || '';
                const hora = row.cells[2]?.textContent.trim() || '';
                const periodo = row.cells[3]?.textContent.trim() || '';
                const servicios = [...row.cells[4].querySelectorAll('.service-badge-table')]
                    .map((badge) => badge.textContent.trim())
                    .join(' | ');
                const total = row.cells[5]?.textContent.trim() || '';

                return [fecha, salon, hora, periodo, servicios, total].join(',');
            });

            content = [headers.join(','), ...rows].join('\n');
        } else {
            const rowsText = visibleRows.map((row, index) => {
                const fecha = row.cells[0]?.textContent.trim() || '';
                const salon = row.cells[1]?.textContent.trim() || '';
                const hora = row.cells[2]?.textContent.trim() || '';
                const periodo = row.cells[3]?.textContent.trim() || '';
                const servicios = [...row.cells[4].querySelectorAll('.service-badge-table')]
                    .map((badge) => badge.textContent.trim())
                    .join(', ');
                const total = row.cells[5]?.textContent.trim() || '';

                return `${index + 1}. ${fecha} | ${salon} | ${hora} | ${periodo} | ${servicios} | ${total}`;
            }).join('\n');

            content =
                `REPORTE DE COSTOS DE FACILIDAD
Tipo: ${selectedType}
${selectedType === 'mensual' ? 'Mes: ' + selectedMonth : ''}
Año: ${selectedYear}
Salón: ${selectedClassroom}
Total estimado del período: ${facilityCostGrandTotal.textContent.trim()}

REGISTROS:
${rowsText || 'No hay registros visibles para exportar.'}`;
        }

        const blob = new Blob(
            [content],
            {type: type === 'csv' ? 'text/csv;charset=utf-8;' : 'application/pdf'}
        );

        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);

        link.href = url;
        link.download = type === 'csv'
            ? `reporte_costos_facilidad_${selectedType}_${selectedYear}.csv`
            : `reporte_costos_facilidad_${selectedType}_${selectedYear}.pdf`;

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    function getVisibleFacilityRows() {
        return [...facilityCostTableBody.querySelectorAll('tr')]
            .filter((row) => row.style.display !== 'none');
    }


    function applyTableFilters() {
        const searchValue = (facilitySearch?.value || '').toLowerCase().trim();
        const type = reportType?.value || '';
        const month = reportMonth?.value || '';
        const year = reportYear?.value || '';
        const classroom = filterClassroom?.value || '';

        const allRows = [...facilityCostTableBody.querySelectorAll('tr')];

        const filteredRows = allRows.filter((row) => {
            const rowMonth = row.dataset.month;
            const rowYear = row.dataset.year;
            const rowClassroom = row.dataset.classroom;
            const rowText = row.textContent.toLowerCase();

            const matchesType = !type || (type === 'annual' && (!year || rowYear === year)) ||
                (type === 'monthly' && (!year || rowYear === year) && (!month || rowMonth === month));

            const matchesClassroom = !classroom ||
            classroom === 'all' || rowClassroom === classroom;


            const matchesSearch =
                !searchValue ||
                rowText.includes(searchValue);

            return matchesType && matchesClassroom && matchesSearch;
        });

        const totalPages = Math.max(1, Math.ceil(filteredRows.length / FACILITY_COSTS_PER_PAGE));

        if (currentFacilityCostsPage > totalPages) {
            currentFacilityCostsPage = totalPages;
        }

        const start = (currentFacilityCostsPage - 1) * FACILITY_COSTS_PER_PAGE;
        const end = start + FACILITY_COSTS_PER_PAGE;
        const paginatedRows = filteredRows.slice(start, end);

        allRows.forEach((row) => {
            row.style.display = 'none';
        });

        paginatedRows.forEach((row) => {
            row.style.display = '';
        });

        const totalAmount = filteredRows.reduce((sum, row) => {
            const amountCell = row.querySelector('td:nth-child(10)');
            return sum + parseMoney(amountCell.textContent || '0');
        }, 0);

        facilityCostGrandTotal.textContent = formatMoney(totalAmount);

        const hasRows = filteredRows.length > 0;
        facilityCostTable.classList.toggle('d-none', !hasRows);
        facilityCostEmptyState.classList.toggle('d-none', hasRows);

        renderLocalPagination(
            facilityCostPagination,
            currentFacilityCostsPage,
            filteredRows.length,
            FACILITY_COSTS_PER_PAGE,
            (page) => {
                currentFacilityCostsPage = page;
                applyTableFilters();
            }
        );
    }

    if (facilitySearch) {
        facilitySearch.addEventListener('input', () => {
            updateFacilitySearchButtonState();
        });

        facilitySearch.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !searchFacilityBtn.disabled) {
                e.preventDefault();
                currentFacilityCostsPage = 1;
                applyTableFilters();
            }
        });
    }

    if (searchFacilityBtn) {
        searchFacilityBtn.addEventListener('click', () => {
            currentFacilityCostsPage = 1;
            applyTableFilters();
        });
    }

    function formatDisplayTime24To12(timeValue) {
        const [hourStr, minuteStr] = timeValue.split(':');
        let hour = Number(hourStr);
        const suffix = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12 || 12;
        return `${String(hour).padStart(2, '0')}:${minuteStr} ${suffix}`;
    }

    // function appendRentalRow() {
    //     const classroomId = rentalClassroom.value;
    //     const eventDate = rentalDate.value;
    //     const startTime = rentalStartTime.value;
    //     const endTime = rentalEndTime.value;
    //     const periodLabel = periodLabelFromValue(rentalPeriodType.value);

    //     const services = [];
    //     if (rentalUtilities.checked) services.push('Utilidades');
    //     if (rentalElectricity.checked) services.push('Electricidad');
    //     if (rentalWater.checked) services.push('Agua');

    //     const total = calculateRentalEstimate();
    //     // const rowId = `cost-row-${String(nextEntryId).padStart(3, '0')}`;
    //     // nextEntryId += 1;

    //     const row = document.createElement('tr');
    //     row.dataset.entryId = rowId;
    //     row.dataset.date = eventDate;
    //     row.dataset.month = String(new Date(`${eventDate}T12:00:00`).getMonth() + 1);
    //     row.dataset.year = String(new Date(`${eventDate}T12:00:00`).getFullYear());
    //     row.dataset.classroom = classroomId;

    //     row.innerHTML = `
    //         <td>${formatDisplayDate(eventDate)}</td>
    //         <td>${classroomId}</td>
    //         <td>${formatDisplayTime24To12(startTime)} - ${formatDisplayTime24To12(endTime)}</td>
    //         <td>${periodLabel}</td>
    //         <td>${createServiceBadges(services)}</td>
    //         <td class="text-end fw-semibold">${formatMoney(total)}</td>
    //         <td class="text-center">
    //             <button
    //                 type="button"
    //                 class="btn btn-sm btn-outline-danger delete-cost-row-btn"
    //                 data-entry-id="${rowId}"
    //                 data-bs-toggle="modal"
    //                 data-bs-target="#deleteCostEntryModal"
    //             >
    //                 <i class="bi bi-trash"></i>
    //             </button>
    //         </td>
    //     `;

    //     facilityCostTableBody.appendChild(row);
    //     bindDeleteButtons();
    //     applyTableFilters();
    // }
    //     facilityCostTableBody.appendChild(row);
    //     bindDeleteButtons();
    //     currentFacilityCostsPage = 1;
    //     applyTableFilters();
    // }

    function bindDeleteButtons() {
        deleteButtons().forEach((btn) => {
            btn.onclick = () => {
                selectedDeleteUrl = btn.dataset.deleteUrl;
            };
        });
    }

    function buildTimeOptions(selectElement, startHour, startMinute, endHour, endMinute) {
        selectElement.innerHTML = '<option value="" selected disabled>Seleccionar</option>';

        let current = (startHour * 60) + startMinute;
        const end = (endHour * 60) + endMinute;

        while (current <= end) {
            const hour = Math.floor(current / 60);
            const minute = current % 60;
            const value = `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
            const label = formatDisplayTime24To12(value);

            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            selectElement.appendChild(option);

            current += 15;
        }
    }

    function configureHasChanges() {
        return (
            getSelectedClassrooms().length > 0 ||
            [
                configAreaSalon, configUtilidades, configElectricidad, configAgua,
                configDiaria1, configSemanal1, configMensual1,
                configDiaria2, configSemanal2, configMensual2,
                configDiaria3, configSemanal3, configMensual3,
            ].some((input) => input && input.value.trim() !== '')
        );
    }

    function rentalHasChanges() {
        return (
            (rentalClassroom?.value || '') !== '' ||
            (rentalRangeType?.value || '') !== '' ||
            (rentalStartDate?.value || '') !== '' ||
            (rentalEndDate?.value || '') !== '' ||
            (rentalResponsable?.value || '').trim() !== '' ||
            (rentalStartTime?.value || '') !== '' ||
            (rentalEndTime?.value || '') !== '' ||
            (rentalDescripcion?.value || '').trim() !== '' ||
            (rentalPeriodType?.value || '') !== '' ||
            rentalServiceChecks.some((input) => input?.checked)
        );
    }

    function resetConfigureFormState() {
        configureRatesForm.reset();
        setSelectionByList([]);
        configureRatesForm.classList.remove('was-validated');
        configureDirty = false;

        [
            configAreaSalon, configUtilidades, configElectricidad, configAgua,
            configDiaria1, configSemanal1, configMensual1,
            configDiaria2, configSemanal2, configMensual2,
            configDiaria3, configSemanal3, configMensual3,
        ].forEach((input) => {
            if (!input) return;

            input.classList.remove('is-invalid');

            const errorElement = document.getElementById(`${input.id}Error`);
            if (errorElement) {
                errorElement.textContent = '';
            }

            const inputGroup = input.closest('.money-input-group');
            if (inputGroup) {
                inputGroup.classList.remove('is-invalid');
            }
        });

        updateConfigureSaveState();
        updateConfigPreview();
    }

    function resetRentalFormState() {
        addRentalForm.reset();
        addRentalForm.classList.remove('was-validated');
        rentalDirty = false;

        toggleServicesError(false);
        clearFieldError(rentalResponsable, rentalResponsableError);
        clearFieldError(rentalDescripcion, rentalDescripcionError);
        clearFieldError(rentalStartDate, $('rentalStartDateError'));
        clearFieldError(rentalEndDate, $('rentalEndDateError'));
        toggleRentalDateRangeUI();

        rentalEstimatedTotal.textContent = formatMoney(0);
        rentalEstimatedTotalInput.value = '0.00';
        detectedPeriodLabel.textContent = '—';
        detectedHoursLabel.textContent = '0.00 horas';

        updateRentalSaveState();
    }

    $('selectAllClassroomsBtn').addEventListener('click', () => {
    setSelectionByList(getAllRenderedClassrooms());
    });

    $('selectAcademicClassroomsBtn').addEventListener('click', () => {
        setSelectionByList(getAcademicRenderedClassrooms());
    });

    $('selectLateralClassroomsBtn').addEventListener('click', () => {
        setSelectionByList(getLateralRenderedClassrooms());
    });

    $('clearClassroomsSelectionBtn').addEventListener('click', () => {
        setSelectionByList([]);
    });

    // configClassroomChecks.forEach((check) => {
    //     check.addEventListener('change', () => {
    //         const selected = getSelectedClassrooms();
    //         if (selected.length === 1) loadRatesIntoForm(selected[0]);
    //         configureDirty = true;
    //         updateConfigureSaveState();
    //     });
    // });

    function handleClassroomCheckboxChange() {
        const selected = getSelectedClassrooms();

        if (selected.length === 1) {
            loadRatesIntoForm(selected[0]);
        } else {
            clearRatesForm();
        }

        configureDirty = true;
        updateConfigureSaveState();
    }

    function bindClassroomCheckboxes() {
        getConfigClassroomChecks().forEach((check) => {
            check.removeEventListener('change', handleClassroomCheckboxChange);
            check.addEventListener('change', handleClassroomCheckboxChange);
        });
    }

    moneyInputs.forEach((input) => {
        input.addEventListener('keydown', (e) => {
            if(['e', 'E', '+', '-'].includes(e.key)){
                e.preventDefault();
            }
        });

        input.addEventListener('input', () =>{
            sanitizeMoneyInput(input);
            configureDirty = true;
            updateConfigPreview();
            updateConfigureSaveState();
        });

        input.addEventListener('blur', () => {
            normalizeMoneyInput(input);
            validateMoneyField(input, true);
            updateConfigPreview();
            updateConfigureSaveState();
        });
    });

    [rentalClassroom, rentalRangeType,rentalStartDate, rentalEndDate,rentalStartTime, rentalEndTime, rentalPeriodType].forEach((el) => {
        el.addEventListener('change', () => {
            rentalDirty = true;
            calculateRentalEstimate();
            updateRentalSaveState();
            toggleRentalDateRangeUI();
        });
    });


    rentalDescripcion.addEventListener('input', () => {
        rentalDirty = true;

        let value = rentalDescripcion.value;

        const exceeded = value.length > 250;

        if (exceeded) {
            rentalDescripcion.value = value.slice(0, 250);
            setFieldError(
                rentalDescripcion,
                rentalDescripcionError,
                'Has alcanzado el máximo de 500 caracteres. No puedes escribir más.'
            );
        } else if (value.length === 250) {
            setFieldError(
                rentalDescripcion,
                rentalDescripcionError,
                'Has alcanzado el máximo de 250 caracteres, puedes aún someter esa cantidad.'
            );
        } else {
            validateDescripcion(true);
        }

        updateRentalSaveState();


    });

    rentalDescripcion.addEventListener('blur', () => {
        validateDescripcion(true);
        updateRentalSaveState();
    });

    rentalServiceChecks.forEach((el) => {
        el.addEventListener('change', () => {
            rentalDirty = true;
            calculateRentalEstimate();
            toggleServicesError(!hasSelectedServices());
            updateRentalSaveState();
        });
    });

    rentalResponsable.addEventListener('input', () => {
        rentalDirty = true;

        let value = rentalResponsable.value;
        const exceeded = value.length > 40;

        if (exceeded) {
            rentalResponsable.value = value.slice(0, 40);
            setFieldError(
                rentalResponsable,
                rentalResponsableError,
                'Has alcanzado el máximo de 40 caracteres. No puedes escribir más.'
            );
        } else if (value.length === 40) {
            setFieldError(
                rentalResponsable,
                rentalResponsableError,
                'Has alcanzado el máximo de 40 caracteres, puedes aún someter esa cantidad.'
            );
        } else {
            validateResponsable(true);
        }

        updateRentalSaveState();
    });

    [reportType, reportMonth, reportYear, filterClassroom].forEach((el) => {
        if (!el) return;

        el.addEventListener('change', () => {
            currentFacilityCostsPage = 1;
            toggleMonthFilter();
            applyTableFilters();
        });
    });

    saveRatesBtn.addEventListener('click', () => {
        configureRatesForm.classList.add('was-validated');

        if (!isConfigureFormValid(true)) {
            updateConfigureSaveState();
            return;
        }

        configureRatesForm.submit();
    });

    addRentalForm.addEventListener('submit', (e) => {
    addRentalForm.classList.add('was-validated');

    const responsableOk = validateResponsable(true);
    const descripcionOk = validateDescripcion(true);
    const servicesOk = hasSelectedServices();
    toggleServicesError(!servicesOk);

    if (!(isRentalFormValid() && responsableOk && descripcionOk && servicesOk)) {
        e.preventDefault();
        updateRentalSaveState();
    }
});

    if (confirmDeleteCostEntryBtn) {
        confirmDeleteCostEntryBtn.addEventListener('click', () => {
            if (selectedDeleteUrl) {
                const deleteForm = $('deleteCostEntryForm');
                if (!deleteForm) return;

                deleteForm.action = selectedDeleteUrl;
                deleteForm.submit();
            }
        });
    }


    document.querySelectorAll('.configure-cancel-btn, .configure-close-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (configureHasChanges()) {
                bootstrap.Modal.getOrCreateInstance($('confirmCancelConfigureModal')).show();
            } else {
                bootstrap.Modal.getOrCreateInstance(configureRatesModal).hide();
            }
        });
    });

    document.querySelectorAll('.rental-cancel-btn, .rental-close-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (rentalHasChanges()) {
                bootstrap.Modal.getOrCreateInstance($('confirmCancelRentalModal')).show();
            } else {
                bootstrap.Modal.getOrCreateInstance(addRentalModal).hide();
            }
        });
    });

    const confirmCancelConfigureBtn = $('confirmCancelConfigureBtn');
    const confirmCancelRentalBtn = $('confirmCancelRentalBtn');
    const confirmCancelConfigureModal = $('confirmCancelConfigureModal');
    const confirmCancelRentalModal = $('confirmCancelRentalModal');

    if (confirmCancelConfigureBtn && confirmCancelConfigureModal && configureRatesModal) {
        confirmCancelConfigureBtn.addEventListener('click', () => {
            configureDirty = false;
            bootstrap.Modal.getOrCreateInstance(confirmCancelConfigureModal).hide();
            bootstrap.Modal.getOrCreateInstance(configureRatesModal).hide();
            resetConfigureFormState();
        });
    }

    if (confirmCancelRentalBtn && confirmCancelRentalModal && addRentalModal) {
        confirmCancelRentalBtn.addEventListener('click', () => {
            rentalDirty = false;
            bootstrap.Modal.getOrCreateInstance(confirmCancelRentalModal).hide();
            bootstrap.Modal.getOrCreateInstance(addRentalModal).hide();
            resetRentalFormState();
        });
    }

    if (configureRatesModal) {
        configureRatesModal.addEventListener('show.bs.modal', resetConfigureFormState);
    }

    if (addRentalModal) {
        addRentalModal.addEventListener('show.bs.modal', () =>{
            servicesTouched = false;
            toggleServicesError(false);
        });
    }

    buildTimeOptions(rentalStartTime, 7, 30, 21, 30);
    buildTimeOptions(rentalEndTime, 7, 45, 21, 45);


    function buildExportUrl(baseUrl) {
    const params = new URLSearchParams();

    const searchValue = facilitySearch?.value?.trim() || '';
    const reportTypeValue = reportType?.value || '';
    const reportMonthValue = reportMonth?.value || '';
    const reportYearValue = reportYear?.value || '';
    const classroomValue = filterClassroom?.value || '';

    if (searchValue) params.set('search', searchValue);
    if (reportTypeValue) params.set('report_type', reportTypeValue);
    if (reportMonthValue) params.set('report_month', reportMonthValue);
    if (reportYearValue) params.set('report_year', reportYearValue);
    if (classroomValue) params.set('filter_classroom', classroomValue);

    const query = params.toString();
    return query ? `${baseUrl}?${query}` : baseUrl;
    }

    if (downloadCsvBtn) {
        downloadCsvBtn.addEventListener('click', (e) => {
            e.preventDefault();

            if (toasts.download) {
                toasts.download.show();
            }

            window.location.href = buildExportUrl(downloadCsvBtn.getAttribute('href'));
        });
    }

    if (downloadPdfBtn) {
        downloadPdfBtn.addEventListener('click', (e) => {
            e.preventDefault();

            if (toasts.download) {
                toasts.download.show();
            }

            window.location.href = buildExportUrl(downloadPdfBtn.getAttribute('href'));
        });
    }

    bindDeleteButtons();
    bindClassroomCheckboxes();
    toggleMonthFilter();
    updateFacilitySearchButtonState();
    resetConfigureFormState();
    resetRentalFormState();
    applyTableFilters();

    if (sessionStorage.getItem('reopenConfigureRatesModal') === 'true' && configureRatesModal) {
        bootstrap.Modal.getOrCreateInstance(configureRatesModal).show();
        sessionStorage.removeItem('reopenConfigureRatesModal');
    }
});
