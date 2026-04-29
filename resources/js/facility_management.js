import * as bootstrap from 'bootstrap';
document.addEventListener('DOMContentLoaded', () => {


        /**
         * Activates tool tip
         */
        const tooltipTriggerList = document.querySelectorAll('[data-bs-title]');
        tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));


        // 🔹 Restore scroll
        const savedScroll = sessionStorage.getItem('facilityScroll');

        if (savedScroll) {
            window.scrollTo({
                top: parseInt(savedScroll),
                behavior: 'smooth'
            });
            sessionStorage.removeItem('facilityScroll');
        }

        // 🔹 Save scroll on filter submit
        const filterForm = document.getElementById('facilityCostFilterForm');

        if (filterForm) {
            filterForm.addEventListener('submit', () => {
                sessionStorage.setItem('facilityScroll', window.scrollY);
            });
        }

        // 🔹 Save scroll on clear filters
        const clearBtn = document.getElementById('clearFacilityFilters');

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                sessionStorage.setItem('facilityScroll', window.scrollY);
            });
        }

    const $ = (id) => document.getElementById(id);

    const downloadCsvBtn = $('downloadCsvBtn');
    const downloadPdfBtn = $('downloadPdfBtn');

    const newClassroomName = $('newClassroomName');
    const newClassroomNameError = $('newClassroomNameError');
    const confirmAddClassroomBtn = $('confirmAddClassroomBtn');
    const addClassroomModal = $('addClassroomModal');

    const facilitySearch = $('facilitySearch');
    const searchFacilityBtn = $('searchFacilityBtn');

    const filterPeriodType = $('filterPeriodType');
    const filterRateMode = $('filterRateMode');
    const filterServices = $('filterServices');

    const FACILITY_COSTS_PER_PAGE = 10;
    let currentFacilityCostsPage = 1;
    const facilityCostPagination = $('facilityCostPagination');

    const reportType = $('reportType');
    const monthFilterWrapper = $('monthFilterWrapper');
    const yearFilterWrapper = $('yearFilterWrapper');
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

    const configClassroomArea = $('configClassroomArea');
    const configUtilities = $('configUtilities');
    const configElectricity = $('configElectricity');
    const configWater = $('configWater');
    const configDaily1 = $('configDaily1');
    const configWeekly1 = $('configWeekly1');
    const configMonthly1 = $('configMonthly1');
    const configDaily2 = $('configDaily2');
    const configWeekly2 = $('configWeekly2');
    const configMonthly2 = $('configMonthly2');
    const configDaily3 = $('configDaily3');
    const configWeekly3 = $('configWeekly3');
    const configMonthly3 = $('configMonthly3');

    const configWorkdayPreview = $('configWorkdayPreview');
    const configSaturdayPreview = $('configSaturdayPreview');
    const configSundayHolidayPreview = $('configSundayHolidayPreview');

    const rentalClassroom = $('rentalClassroom');
    const rentalResponsible = $('rentalResponsible');
    const rentalStartTime = $('rentalStartTime');
    const rentalEndTime = $('rentalEndTime');
    const rentalDescription = $('rentalDescription');
    const rentalPeriodType = $('rentalPeriodType');

    const rentalRangeType = $('rentalRangeType');
    const rentalStartDate = $('rentalStartDate');
    const rentalEndDate = $('rentalEndDate');
    const rentalStartDateTrigger = $('rentalStartDateTrigger');
    const rentalEndDateTrigger = $('rentalEndDateTrigger');
    function lockDateTyping(input) {
        if (!input) return;

        input.addEventListener('keydown', (e) => {
            e.preventDefault();
        });

        input.addEventListener('beforeinput', (e) => {
            e.preventDefault();
        });

        input.addEventListener('paste', (e) => {
            e.preventDefault();
        });

        input.addEventListener('drop', (e) => {
            e.preventDefault();
        });

        input.addEventListener('mousedown', () => {
            if (typeof input.showPicker === 'function') {
                input.showPicker();
            }
        });
    }

    function bindDateTrigger(button, input) {
        if (!button || !input) return;

        button.addEventListener('click', () => {
            input.focus();
            if (typeof input.showPicker === 'function') {
                input.showPicker();
            }
        });
    }

    lockDateTyping(rentalStartDate);
    lockDateTyping(rentalEndDate);

    bindDateTrigger(rentalStartDateTrigger, rentalStartDate);
    bindDateTrigger(rentalEndDateTrigger, rentalEndDate);


    const rentalEndDateRow = $('rentalEndDateRow');
    const rentalRangeWarningRow = $('rentalRangeWarningRow');
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
    const rentalResponsibleError = $('rentalResponsibleError');
    const rentalDescriptionError = $('rentalDescriptionError');

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
    let configureDirty = false;
    let rentalDirty = false;
    let servicesTouched = false;
    let allowConfigureModalClose = false;
    let allowRentalModalClose = false;

    const facilityConfig = window.facilityManagementConfig || {};
    const ratesByClassroom = facilityConfig.ratesByClassroom || {};


    function clearRatesForm() {
        [
            configClassroomArea, configUtilities, configElectricity, configWater,
            configDaily1, configWeekly1, configMonthly1,
            configDaily2, configWeekly2, configMonthly2,
            configDaily3, configWeekly3, configMonthly3,
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
        if (!newClassroomName || !newClassroomNameError) return true;

        const value = newClassroomName.value.trim();
        const allowedRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 ,.\-]+$/;

        const alreadyExists = [...document.querySelectorAll('.classroom-card-col')]
            .some((card) => card.dataset.classroomName?.toLowerCase() === value.toLowerCase());

        if (showError) {
            clearFieldError(newClassroomName, newClassroomNameError);
        }

        if (!value) {
            if (showError) {
                setFieldError(newClassroomName, newClassroomNameError, 'El nombre del área es obligatorio.');
            }
            return false;
        }

        if (!allowedRegex.test(value)) {
            if (showError) {
                setFieldError(
                    newClassroomName,
                    newClassroomNameError,
                    'Solo se permiten letras, números, espacios, punto, coma y guion.'
                );
            }
            return false;
        }

        if (value.length < 6) {
            if (showError) {
                setFieldError(newClassroomName, newClassroomNameError, 'El nombre del área debe tener al menos 6 caracteres.');
            }
            return false;
        }

        if (value.length > 40) {
            if (showError) {
                setFieldError(newClassroomName, newClassroomNameError, 'Has alcanzado el máximo de 40 caracteres. No puedes escribir más.');
            }
            return false;
        }

        if (alreadyExists) {
            if (showError) {
                setFieldError(newClassroomName, newClassroomNameError, 'Esa área ya existe.');
            }
            return false;
        }

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
            const value = newClassroomName.value;

            if (value.length > 40) {
                newClassroomName.value = value.slice(0, 40);
                setFieldError(
                    newClassroomName,
                    newClassroomNameError,
                    'Has alcanzado el máximo de 40 caracteres. No puedes escribir más.'
                );
            } else if (value.length === 40) {
                setFieldError(
                    newClassroomName,
                    newClassroomNameError,
                    'Has alcanzado el máximo de 40 caracteres, puedes aún someter esa cantidad.'
                );
            } else {
                validateNewClassroomName(true);
            }

            updateAddClassroomButtonState();
        });

        newClassroomName.addEventListener('blur', () => {
            const value = newClassroomName.value.trim();

            if (value.length === 40) {
                clearFieldError(newClassroomName, newClassroomNameError);
            } else {
                validateNewClassroomName(true);
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
        rentalRangeWarningRow.classList.toggle('d-none', !showEndDate);

        rentalStartDateLabel.innerHTML = showEndDate
            ? 'Fecha inicial del evento <span class="text-danger">*</span>'
            : 'Fecha del evento <span class="text-danger">*</span>';

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

    function validateRentalDates(showError = true, forceRequired = false) {
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
            if (forceRequired && showError) {
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

        if (!isAllowedDateForSelectedPeriod(startValue)) {
            if (showError) {
                setFieldError(rentalStartDate, startError, getDateRestrictionMessage());
            }
            return false;
        }

        if (!requiresEndDate()) {
            return true;
        }

        if (!endValue) {
            if (forceRequired && showError) {
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

        if (!isAllowedDateForSelectedPeriod(endValue)) {
            if (showError) {
                setFieldError(rentalEndDate, endError, getDateRestrictionMessage());
            }
            return false;
        }

        if (endDate.getTime() === startDate.getTime()) {
            if (showError) {
                setFieldError(
                    rentalEndDate,
                    endError,
                    'La fecha de fin no puede ser igual a la fecha de inicio para eventos semanales o mensuales.'
                );
                rentalStartDate.classList.add('is-invalid');
                rentalEndDate.classList.add('is-invalid');
            }
            return false;
        }

        if (endDate < startDate) {
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


    function timeToMinutes(timeValue) {
        if (!timeValue) return 0;
        const [hour, minute] = timeValue.split(':').map(Number);
        return (hour * 60) + minute;
    }

    function calculateHours(start, end) {
        const diff = timeToMinutes(end) - timeToMinutes(start);
        return diff > 0 ? diff / 60 : 0;
    }

    function isWorkdayPeriod() {
        return rentalPeriodType?.value === 'workday';
    }

    function isNonWorkdayPeriod() {
        return ['non_workday_saturday', 'non_workday_sunday_holiday'].includes(rentalPeriodType?.value);
    }

    function formatDateLocal(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function buildTimeOptions(select, startHour, startMinute, endHour, endMinute, placeholder) {
        if (!select) return;

        select.innerHTML = `<option value="" selected disabled>${placeholder}</option>`;

        const current = new Date();
        current.setHours(startHour, startMinute, 0, 0);

        const end = new Date();
        end.setHours(endHour, endMinute, 0, 0);

        while (current <= end) {
            const hours = String(current.getHours()).padStart(2, '0');
            const minutes = String(current.getMinutes()).padStart(2, '0');
            const value = `${hours}:${minutes}`;

            const option = document.createElement('option');
            option.value = value;

            function formatTime12h(date) {
                let hours = date.getHours();
                const minutes = String(date.getMinutes()).padStart(2, '0');

                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12 || 12;

                return `${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;
            }
            option.textContent = formatTime12h(current);
            select.appendChild(option);

            current.setMinutes(current.getMinutes() + 15);
        }
    }

    function updateRentalTimeOptions() {
        const previousStart = rentalStartTime.value;
        const previousEnd = rentalEndTime.value;

        if (isWorkdayPeriod()) {
            buildTimeOptions(rentalStartTime, 7, 30, 16, 30, 'Seleccionar hora de inicio');
            buildTimeOptions(rentalEndTime, 7, 45, 16, 30, 'Seleccionar hora de fin');
        } else if (isNonWorkdayPeriod()) {
            buildTimeOptions(rentalStartTime, 8, 30, 21, 30, 'Seleccionar hora de inicio');
            buildTimeOptions(rentalEndTime, 8, 45, 21, 30, 'Seleccionar hora de fin');
        } else {
            rentalStartTime.innerHTML = '<option value="" selected disabled>Primero selecciona el tipo de período</option>';
            rentalEndTime.innerHTML = '<option value="" selected disabled>Primero selecciona el tipo de período</option>';
            return;
        }

        if ([...rentalStartTime.options].some(option => option.value === previousStart)) {
            rentalStartTime.value = previousStart;
        }

        if ([...rentalEndTime.options].some(option => option.value === previousEnd)) {
            rentalEndTime.value = previousEnd;
        }
    }

    function isAllowedDateForSelectedPeriod(dateString) {
        if (!dateString) return true;

        const date = new Date(`${dateString}T00:00:00`);
        const day = date.getDay();

        if (isWorkdayPeriod()) {
            return day >= 1 && day <= 5;
        }

        if (isNonWorkdayPeriod()) {
            return true;
        }

        return true;
    }

    function getDateRestrictionMessage() {
        if (isWorkdayPeriod()) {
            return 'Para el período laborable solo puedes seleccionar fechas de lunes a viernes.';
        }

        return '';
    }

    function updateRentalDateRestrictions() {
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const todayString = formatDateLocal(today);

        rentalStartDate.min = todayString;
        rentalEndDate.min = todayString;

        toggleRentalDateRangeUI();
    }

    rentalStartDate.addEventListener('change', () => {
        validateRentalDates(true, false);
        updateRentalDateRestrictions();
        calculateRentalEstimate();
        updateRentalSaveState();
    });

    rentalEndDate.addEventListener('change', () => {
        validateRentalDates(true, false);
        updateRentalDateRestrictions();
        calculateRentalEstimate();
        updateRentalSaveState();
    });


    function getSelectedClassrooms() {
        return getConfigClassroomChecks()
            .filter(check => check.checked)
            .map(check => check.value);
    }


    function updateDiscardButtonState() {
        const selected = getSelectedClassrooms();
        openDiscardSelectedClassroomsBtn.disabled = selected.length === 0;
    }

    getConfigClassroomChecks().forEach(check => {
        check.addEventListener('change', updateDiscardButtonState);
    });

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

    function isPastDate(dateString) {
        if (!dateString) return false;

        const inputDate = new Date(dateString + 'T00:00:00');
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        return inputDate < today;
    }

    const ratesSavedAutoTrigger = document.getElementById('ratesSavedAutoTrigger');
    const rentalSavedAutoTrigger = document.getElementById('rentalSavedAutoTrigger');
    const mockImportAutoTrigger = document.getElementById('mockImportAutoTrigger');

    const deleteEntryAutoTrigger = document.getElementById('deleteEntryAutoTrigger');

    if (deleteEntryAutoTrigger && toasts.deleteEntry) {
        toasts.deleteEntry.show();
    }

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

            if (reportType) reportType.value = '';
            if (reportMonth) reportMonth.value = '';
            if (reportYear) reportYear.value = '';
            if (filterClassroom) filterClassroom.value = '';


            if (filterPeriodType) filterPeriodType.value = '';
            if (filterRateMode) filterRateMode.value = '';
            if (filterServices) filterServices.value = '';

            toggleMonthFilter();
            updateFacilitySearchButtonState();

            currentFacilityCostsPage = 1;
            applyTableFilters();
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

    function loadRatesIntoForm(classroomId) {
        const data = ratesByClassroom[classroomId];

        if (!data || !data.configured) {
            clearRatesForm();
            return;
        }

        configClassroomArea.value = Number(data.area).toFixed(2);
        configUtilities.value = Number(data.utilities).toFixed(2);
        configElectricity.value = Number(data.electricity).toFixed(2);
        configWater.value = Number(data.water).toFixed(2);

        configDaily1.value = Number(data.daily1).toFixed(2);
        configWeekly1.value = Number(data.weekly1).toFixed(2);
        configMonthly1.value = Number(data.monthly1).toFixed(2);

        configDaily2.value = Number(data.daily2).toFixed(2);
        configWeekly2.value = Number(data.weekly2).toFixed(2);
        configMonthly2.value = Number(data.monthly2).toFixed(2);

        configDaily3.value = Number(data.daily3).toFixed(2);
        configWeekly3.value = Number(data.weekly3).toFixed(2);
        configMonthly3.value = Number(data.monthly3).toFixed(2);

        updateConfigPreview();
    }

    function updateConfigPreview() {
        const area = toNumber(configClassroomArea.value);
        configWorkdayPreview.textContent = formatMoney((area * toNumber(configDaily1.value)).toFixed(2));
        configSaturdayPreview.textContent = formatMoney((area * toNumber(configDaily2.value)).toFixed(2));
        configSundayHolidayPreview.textContent = formatMoney((area * toNumber(configDaily3.value)).toFixed(2));
    }

    function isConfigureFormValid(showError = false) {
    if (!getSelectedClassrooms().length) return false;

    const moneyFields = [
        configUtilities, configElectricity, configWater,
        configDaily1, configWeekly1, configMonthly1,
        configDaily2, configWeekly2, configMonthly2,
        configDaily3, configWeekly3, configMonthly3,
    ];

    return (
        validateAreaField(configClassroomArea, showError) &&
        moneyFields.every((input) => validateMoneyField(input, showError))
    );
    }

    function updateConfigureSaveState() {
        saveRatesBtn.disabled = !isConfigureFormValid();
    }

    function periodLabelFromValue(value) {
        if (value === 'workday') return 'Laborable';
        if (value === 'non_workday_saturday') return 'No laborable sábado';
        if (value === 'non_workday_sunday_holiday') return 'No laborable domingo o festivo';
        return '—';
    }

    function getPeriodRateData(classroomId, periodType) {
        const data = ratesByClassroom[classroomId];
        if (!data) return {daily: 0, weekly: 0, monthly: 0};

        if (periodType === 'workday') return {
            daily: toNumber(data.daily1),
            weekly: toNumber(data.weekly1),
            monthly: toNumber(data.monthly1)
        };
        if (periodType === 'non_workday_saturday') return {
            daily: toNumber(data.daily2),
            weekly: toNumber(data.weekly2),
            monthly: toNumber(data.monthly2)
        };
        if (periodType === 'non_workday_sunday_holiday') return {
            daily: toNumber(data.daily3),
            weekly: toNumber(data.weekly3),
            monthly: toNumber(data.monthly3)
        };

        return {daily: 0, weekly: 0, monthly: 0};
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
                setFieldError(input, errorElement, 'Ingresa una cantidad válida usando solo números, sin ceros a la izquierda y hasta 2 dígitos después del punto decimal.');
                inputGroup?.classList.add('is-invalid');
            }
            return false;
        }

        if (!/^(?:0|[1-9]\d*)(?:\.\d{1,2})?$/.test(value)) {
            if (showError) {
                setFieldError(input, errorElement, 'Ingresa una cantidad válida usando solo números, sin ceros a la izquierda y hasta 2 dígitos después del punto decimal.');
                inputGroup?.classList.add('is-invalid');
            }
            return false;
        }

        if (Number(value) < 0) {
            if (showError) {
                setFieldError(input, errorElement, 'El precio no puede ser negativo.');
                inputGroup?.classList.add('is-invalid');
            }
            return false;
        }

        if (Number(value) > MAX_RATE_PRICE) {
            if (showError) {
                setFieldError(input, errorElement, 'El precio no puede exceder $500.00.');
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
            setFieldError(input, errorElement, 'Ingresa una cantidad válida usando solo números, sin ceros a la izquierda y hasta 2 dígitos después del punto decimal.');
            inputGroup?.classList.add('is-invalid');
        }
        return false;
    }

    if (!/^(?:0|[1-9]\d*)(?:\.\d{1,2})?$/.test(value)) {
        if (showError) {
            setFieldError(input, errorElement, 'Ingresa una cantidad válida usando solo números, sin ceros a la izquierda y hasta 2 dígitos después del punto decimal.');
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

    function validateResponsible(showError = true) {
        const value = rentalResponsible.value.trim();
        const regex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;

        if (showError){
            clearFieldError(rentalResponsible, rentalResponsibleError);
        }

        if (!value) {
            if (showError) {
                setFieldError(rentalResponsible, rentalResponsibleError, 'El responsable es un campo obligatorio para llenar.');
            }
            return false;
        }

        if (!regex.test(value)){
            if (showError){
                setFieldError(rentalResponsible, rentalResponsibleError, 'Solo se permiten letras y espacios.');
            }
            return false;
        }

        if (value.length < 8) {
            if (showError) {
                setFieldError(rentalResponsible, rentalResponsibleError, 'El responsable debe tener al menos 8 caracteres.');
            }
            return false;
        }

        if (value.length > 40) {
            if (showError) {
                setFieldError(rentalResponsible, rentalResponsibleError, 'El responsable no puede exceder 40 caracteres.');
            }
            return false;
        }

        return true;
    }

    function validateDescription(showError = true) {
        const value = rentalDescription.value.trim();
        const regex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;

        if (showError) {
            clearFieldError(rentalDescription, rentalDescriptionError);
        }

        if (!value) {
            if (showError) {
                setFieldError(rentalDescription, rentalDescriptionError, 'La descripción es obligatoria.');
            }
            return false;
        }

        if(!regex.test(value)) {
            if(showError) {
                setFieldError(rentalDescription, rentalDescriptionError, 'Solo se permiten letras, números, espacios, punto, coma y guion.');
            }
            return false;
        }

        if (value.length < 10) {
            if (showError) setFieldError(rentalDescription, rentalDescriptionError, 'La descripción debe tener al menos 10 caracteres.');
            return false;
        }

        if (value.length > 250) {
            if (showError) {
                setFieldError(
                    rentalDescription,
                    rentalDescriptionError,
                    'Has alcanzado el máximo de 250 caracteres. No puedes escribir más.'
                );
            }
            return false;
        }


        return true;
    }

    if (configClassroomArea) {
        configClassroomArea.addEventListener('input', () => {
            validateAreaField(configClassroomArea, true);
            configureDirty = true;
            updateConfigPreview();
            updateConfigureSaveState();
        });

        configClassroomArea.addEventListener('blur', () => {
            validateAreaField(configClassroomArea, true);
            updateConfigPreview();
            updateConfigureSaveState();
        });
    }

    function calculateRentalEstimate() {
        const classroomId = rentalClassroom.value;
        const startTime = rentalStartTime.value;
        const endTime = rentalEndTime.value;
        const periodType = rentalPeriodType.value;

        const dailyHours = calculateHours(startTime, endTime);
        let totalDays = 1;

        if (requiresEndDate() && rentalStartDate.value && rentalEndDate.value) {
            const startDate = new Date(`${rentalStartDate.value}T00:00:00`);
            const endDate = new Date(`${rentalEndDate.value}T00:00:00`);

            const diffMs = endDate - startDate;
            const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24)) + 1;

            totalDays = diffDays > 0 ? diffDays : 1;
        }

        const totalHours = dailyHours * totalDays;

        detectedPeriodLabel.textContent = periodLabelFromValue(periodType);
        detectedHoursLabel.textContent = `${totalHours.toFixed(2)} horas`;

        if (!classroomId || !dailyHours || !periodType || !ratesByClassroom[classroomId]) {
            rentalEstimatedTotal.textContent = formatMoney(0);
            rentalEstimatedTotalInput.value = '0.00';
            return 0;
        }

        const data = ratesByClassroom[classroomId];
        const area = toNumber(data.area);
        const periodRates = getPeriodRateData(classroomId, periodType);

        let selectedRate = 0;

        if (rentalRangeType.value === 'daily') {
            selectedRate = periodRates.daily;
        } else if (rentalRangeType.value === 'weekly') {
            selectedRate = periodRates.weekly;
        } else if (rentalRangeType.value === 'monthly') {
            selectedRate = periodRates.monthly;
        }

        let total = area * selectedRate;

        if (rentalUtilities.checked) total += toNumber(data.utilities) * totalHours;
        if (rentalElectricity.checked) total += toNumber(data.electricity) * totalHours;
        if (rentalWater.checked) total += toNumber(data.water) * totalHours;

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
        } else {
            const startMinutes = timeToMinutes(rentalStartTime.value);
            const endMinutes = timeToMinutes(rentalEndTime.value);

            if (endMinutes <= startMinutes) {
                validTimes = false;
                rentalStartTime.classList.add('is-invalid');
                rentalEndTime.classList.add('is-invalid');

                if (timeError) {
                    timeError.textContent = 'La hora de fin debe ser mayor que la hora de inicio.';
                }
            } else if (isWorkdayPeriod() && (startMinutes < 450 || endMinutes > 990)) {
                validTimes = false;
                rentalStartTime.classList.add('is-invalid');
                rentalEndTime.classList.add('is-invalid');

                if (timeError) {
                    timeError.textContent = 'Para el período laborable solo se permiten horarios de 7:30 a.m. a 4:30 p.m.';
                }
            } else if (isNonWorkdayPeriod() && (startMinutes < 510 || endMinutes > 1290)) {
                validTimes = false;
                rentalStartTime.classList.add('is-invalid');
                rentalEndTime.classList.add('is-invalid');

                if (timeError) {
                    timeError.textContent = 'Para períodos no laborables solo se permiten horarios de 8:30 a.m. a 9:30 p.m.';
                }
            }
        }

        const validResponsible = validateResponsible(false);
        const validDescription = validateDescription(false);
        const validServices = hasSelectedServices();
        const validDates = validateRentalDates(false);

        if (servicesTouched) {
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
            validResponsible &&
            validDescription &&
            validServices
        );
    }


    function updateRentalSaveState() {
        saveRentalBtn.disabled = !isRentalFormValid();
    }

    function toggleMonthFilter() {
        const selectedType = reportType?.value || '';
        const isMonthly = selectedType === 'monthly';
        const hasTypeSelected = selectedType !== '';

        monthFilterWrapper?.classList.toggle('d-none', !isMonthly);
        yearFilterWrapper?.classList.toggle('d-none', !hasTypeSelected);

        if (!isMonthly && reportMonth) {
            reportMonth.value = '';
        }

        if (!hasTypeSelected && reportYear) {
            reportYear.value = '';
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
                const date = row.cells[0]?.textContent.trim() || '';
                const classroom = row.cells[1]?.textContent.trim() || '';
                const time = row.cells[2]?.textContent.trim() || '';
                const period = row.cells[3]?.textContent.trim() || '';
                const services = [...row.cells[4].querySelectorAll('.service-badge-table')]
                    .map((badge) => badge.textContent.trim())
                    .join(' | ');
                const total = row.cells[5]?.textContent.trim() || '';

                return [date, classroom, time, period, services, total].join(',');
            });

            content = [headers.join(','), ...rows].join('\n');
        } else {
            const rowsText = visibleRows.map((row, index) => {
                const date = row.cells[0]?.textContent.trim() || '';
                const classroom = row.cells[1]?.textContent.trim() || '';
                const time = row.cells[2]?.textContent.trim() || '';
                const period = row.cells[3]?.textContent.trim() || '';
                const services = [...row.cells[4].querySelectorAll('.service-badge-table')]
                    .map((badge) => badge.textContent.trim())
                    .join(', ');
                const total = row.cells[5]?.textContent.trim() || '';

                return `${index + 1}. ${date} | ${classroom} | ${time} | ${period} | ${services} | ${total}`;
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
        const periodType = filterPeriodType?.value || '';
        const rateMode = filterRateMode?.value || '';
        const service = filterServices?.value || '';

        const allRows = [...facilityCostTableBody.querySelectorAll('tr')];

        const filteredRows = allRows.filter((row) => {
            const rowMonth = row.dataset.month;
            const rowYear = row.dataset.year;
            const rowClassroom = row.dataset.classroom;
            const rowText = row.textContent.toLowerCase();

            const rowPeriodType = row.cells[6]?.textContent.trim() || '';
            const rowRateMode = row.cells[7]?.textContent.trim() || '';
            const rowServices = row.cells[8]?.textContent.trim() || '';

            const matchesType =
                !type ||
                (type === 'annual' && (!year || rowYear === year)) ||
                (type === 'monthly' && (!year || rowYear === year) && (!month || rowMonth === month));

            const matchesClassroom =
                !classroom || classroom === 'all' || rowClassroom === classroom;

            const matchesPeriodType =
                !periodType || rowPeriodType === periodType;

            const matchesRateMode =
                !rateMode || rowRateMode === rateMode;

            const matchesService =
                !service || rowServices.includes(service);

            const matchesSearch =
                !searchValue || rowText.includes(searchValue);

            return (
                matchesType &&
                matchesClassroom &&
                matchesPeriodType &&
                matchesRateMode &&
                matchesService &&
                matchesSearch
            );
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


    function bindDeleteButtons() {
        deleteButtons().forEach((btn) => {
            btn.onclick = () => {
                selectedDeleteUrl = btn.dataset.deleteUrl;
            };
        });
    }


    function configureHasChanges() {
        return (
            getSelectedClassrooms().length > 0 ||
            [
                configClassroomArea, configUtilities, configElectricity, configWater,
                configDaily1, configWeekly1, configMonthly1,
                configDaily2, configWeekly2, configMonthly2,
                configDaily3, configWeekly3, configMonthly3,
            ].some((input) => input && input.value.trim() !== '')
        );
    }

    function rentalHasChanges() {
        return (
            (rentalClassroom?.value || '') !== '' ||
            (rentalRangeType?.value || '') !== '' ||
            (rentalStartDate?.value || '') !== '' ||
            (rentalEndDate?.value || '') !== '' ||
            (rentalResponsible?.value || '').trim() !== '' ||
            (rentalStartTime?.value || '') !== '' ||
            (rentalEndTime?.value || '') !== '' ||
            (rentalDescription?.value || '').trim() !== '' ||
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
            configClassroomArea, configUtilities, configElectricity, configWater,
            configDaily1, configWeekly1, configMonthly1,
            configDaily2, configWeekly2, configMonthly2,
            configDaily3, configWeekly3, configMonthly3,
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
        clearFieldError(rentalResponsible, rentalResponsibleError);
        clearFieldError(rentalDescription, rentalDescriptionError);
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
        input.addEventListener('input', () => {
            validateMoneyField(input, true);
            configureDirty = true;
            updateConfigPreview();
            updateConfigureSaveState();
        });

        input.addEventListener('blur', () => {
            validateMoneyField(input, true);
            updateConfigPreview();
            updateConfigureSaveState();
        });
    });

    [rentalClassroom, rentalRangeType, rentalStartTime, rentalEndTime, rentalPeriodType].forEach((el) => {
        if (!el) return;

        el.addEventListener('change', () => {
            rentalDirty = true;


            if (el === rentalPeriodType) {
                rentalStartDate.value = '';
                rentalEndDate.value = '';
                updateRentalTimeOptions();
                updateRentalDateRestrictions();
            }

            if (el === rentalStartDate || el === rentalEndDate) {
                updateRentalDateRestrictions();
            }
            calculateRentalEstimate();
            updateRentalSaveState();
            toggleRentalDateRangeUI();
        });
    });


    rentalDescription.addEventListener('input', () => {
        rentalDirty = true;

        const value = rentalDescription.value;

        if (value.length > 250) {
            rentalDescription.value = value.slice(0, 250);
            setFieldError(
                rentalDescription,
                rentalDescriptionError,
                'Has alcanzado el máximo de 250 caracteres. No puedes escribir más.'
            );
        } else if (value.length === 250) {
            setFieldError(
                rentalDescription,
                rentalDescriptionError,
                'Has alcanzado el máximo de 250 caracteres, puedes aún someter esa cantidad.'
            );
        } else {
            validateDescription(true);
        }

        updateRentalSaveState();
    });

    rentalDescription.addEventListener('blur', () => {
        validateDescription(true);
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

    if (rentalResponsible) {
        rentalResponsible.addEventListener('input', () => {
            rentalDirty = true;

            const value = rentalResponsible.value;

            if (value.length > 40) {
                rentalResponsible.value = value.slice(0, 40);
                setFieldError(
                    rentalResponsible,
                    rentalResponsibleError,
                    'Has alcanzado el máximo de 40 caracteres. No puedes escribir más.'
                );
            } else if (value.length === 40) {
                setFieldError(
                    rentalResponsible,
                    rentalResponsibleError,
                    'Has alcanzado el máximo de 40 caracteres, puedes aún someter esa cantidad.'
                );
            } else {
                validateResponsible(true);
            }

            updateRentalSaveState();
        });

        rentalResponsible.addEventListener('blur', () => {
            const value = rentalResponsible.value.trim();

            if (value.length === 40) {
                clearFieldError(rentalResponsible, rentalResponsibleError);
            } else {
                validateResponsible(true);
            }

            updateRentalSaveState();
        });
    }

    [reportType, reportMonth, reportYear, filterClassroom, filterPeriodType, filterRateMode, filterServices].forEach((el) => {
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

    const responsibleOk = validateResponsible(true);
    const descriptionOk = validateDescription(true);
    const servicesOk = hasSelectedServices();
    toggleServicesError(!servicesOk);

        const datesOk = validateRentalDates(true, true);

        if (!(isRentalFormValid() && datesOk && responsibleOk && descriptionOk && servicesOk)) {
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

    if (configureRatesModal) {
        configureRatesModal.addEventListener('hide.bs.modal', (e) => {
            if (allowConfigureModalClose) {
                allowConfigureModalClose = false;
                return;
            }

            const confirmModalOpen = $('confirmCancelConfigureModal')?.classList.contains('show');

            if (confirmModalOpen) return;

            if (configureHasChanges()) {
                e.preventDefault();
                bootstrap.Modal.getOrCreateInstance($('confirmCancelConfigureModal')).show();
            }
        });
    }
    if (addRentalModal) {
        addRentalModal.addEventListener('hide.bs.modal', (e) => {
            if (allowRentalModalClose) {
                allowRentalModalClose = false;
                return;
            }

            const confirmModalOpen = $('confirmCancelRentalModal')?.classList.contains('show');

            if (confirmModalOpen) return;

            if (rentalHasChanges()) {
                e.preventDefault();
                bootstrap.Modal.getOrCreateInstance($('confirmCancelRentalModal')).show();
            }
        });
    }

    const confirmCancelConfigureBtn = $('confirmCancelConfigureBtn');
    const confirmCancelRentalBtn = $('confirmCancelRentalBtn');
    const confirmCancelConfigureModal = $('confirmCancelConfigureModal');
    const confirmCancelRentalModal = $('confirmCancelRentalModal');

    if (confirmCancelConfigureBtn && confirmCancelConfigureModal && configureRatesModal) {
        confirmCancelConfigureBtn.addEventListener('click', () => {
            configureDirty = false;
            allowConfigureModalClose = true;
            resetConfigureFormState();
            bootstrap.Modal.getOrCreateInstance(confirmCancelConfigureModal).hide();
            bootstrap.Modal.getOrCreateInstance(configureRatesModal).hide();
        });
    }

    if (confirmCancelRentalBtn && confirmCancelRentalModal && addRentalModal) {
        confirmCancelRentalBtn.addEventListener('click', () => {
            rentalDirty = false;
            allowRentalModalClose = true;
            resetRentalFormState();
            bootstrap.Modal.getOrCreateInstance(confirmCancelRentalModal).hide();
            bootstrap.Modal.getOrCreateInstance(addRentalModal).hide();
        });
    }

    if (configureRatesModal) {
        configureRatesModal.addEventListener('show.bs.modal', resetConfigureFormState);
    }

    if (addRentalModal) {
        addRentalModal.addEventListener('show.bs.modal', () => {
            servicesTouched = false;
            toggleServicesError(false);
            updateRentalTimeOptions();
            updateRentalDateRestrictions();
            updateRentalSaveState();
        });
    }


    function buildExportUrl(baseUrl) {
    const params = new URLSearchParams();

    const searchValue = facilitySearch?.value?.trim() || '';
    const reportTypeValue = reportType?.value || '';
    const reportMonthValue = reportMonth?.value || '';
    const reportYearValue = reportYear?.value || '';
    const classroomValue = filterClassroom?.value || '';
    const periodTypeValue = filterPeriodType?.value || '';
    const rateModeValue = filterRateMode?.value || '';
    const servicesValue = filterServices?.value || '';

    if (periodTypeValue) params.set('filter_period_type', periodTypeValue);
    if (rateModeValue) params.set('filter_rate_mode', rateModeValue);
    if (servicesValue) params.set('filter_services', servicesValue);

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
    updateDiscardButtonState();

    if (sessionStorage.getItem('reopenConfigureRatesModal') === 'true' && configureRatesModal) {
        bootstrap.Modal.getOrCreateInstance(configureRatesModal).show();
        sessionStorage.removeItem('reopenConfigureRatesModal');
    }
});
