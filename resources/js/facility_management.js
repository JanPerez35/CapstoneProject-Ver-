import * as bootstrap from 'bootstrap';
import flatpickr from "flatpickr";
import { Spanish } from "flatpickr/dist/l10n/es.js";
import "flatpickr/dist/flatpickr.min.css";
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
    function clearActionRadios() {
        document.querySelectorAll('.action-radio').forEach((radio) => {
            radio.checked = false;
        });
    }

    const downloadCsvBtn = $('downloadCsvBtn');
    const downloadPdfBtn = $('downloadPdfBtn');

    const newClassroomName = $('newClassroomName');
    const newClassroomType = $('newClassroomType');
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
    const rentalRateModeDisplay = $('rentalRateModeDisplay');
    const rentalStartDate = $('rentalStartDate');
    const rentalEndDate = $('rentalEndDate');

    const rentalEndDateRow = $('rentalEndDateRow');
    const rentalRangeWarningRow = $('rentalRangeWarningRow');
    const rentalStartDateLabel = $('rentalStartDateLabel');

    const rentalUtilities = $('rentalUtilities');
    const rentalElectricity = $('rentalElectricity');
    const rentalWater = $('rentalWater');
    const rentalServiceChecks = [rentalUtilities, rentalElectricity, rentalWater];

    rentalServiceChecks.forEach(input => {
        input?.addEventListener('change', () => {
            servicesTouched = true;
            calculateRentalEstimate();
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
    const editButtons = () => [...document.querySelectorAll('.edit-cost-row-btn')];
    const customizeDaysButtons = () => [...document.querySelectorAll('.customize-days-btn')];
    const createRelatedButtons = () => [...document.querySelectorAll('.create-related-btn')];

    const editSubEventButtons = () => [...document.querySelectorAll('.edit-sub-event-btn')];

    let relatedModalMode = 'create';
    let editingSubEventId = null;

    const customizeEventId = $('customizeEventId');
    const saveCustomizeDaysBtn = $('saveCustomizeDaysBtn');

    const editEventId = $('editEventId');
    const saveEditEventBtn = $('saveEditEventBtn');

    const relatedParentEventId = $('relatedParentEventId');
    const saveRelatedEventBtn = $('saveRelatedEventBtn');
    const relatedArea = $('relatedArea');
    const relatedResponsible = $('relatedResponsible');
    const relatedDescription = $('relatedDescription');
    const relatedUtilities = $('relatedUtilities');
    const relatedElectricity = $('relatedElectricity');
    const relatedWater = $('relatedWater');
    const relatedServiceChecks = [relatedUtilities, relatedElectricity, relatedWater];
    const relatedPeriodType = $('relatedPeriodType');
    const relatedRateMode = $('relatedRateMode');
    const relatedRateModeDisplay = $('relatedRateModeDisplay');
    const relatedStartDate = $('relatedStartDate');
    const relatedEndDate = $('relatedEndDate');
    const relatedStartTime = $('relatedStartTime');
    const relatedEndTime = $('relatedEndTime');
    const relatedEstimatedTotal = $('relatedEstimatedTotal');
    const relatedDetectedPeriodLabel = $('relatedDetectedPeriodLabel');
    const relatedDetectedHoursLabel = $('relatedDetectedHoursLabel');
    const relatedAreaError = $('relatedAreaError');
    const relatedResponsibleError = $('relatedResponsibleError');
    const relatedDescriptionError = $('relatedDescriptionError');
    const relatedStartDateError = $('relatedStartDateError');
    const relatedEndDateError = $('relatedEndDateError');
    const relatedTimeError = $('relatedTimeError');

    const editResponsible = $('editResponsible');
    const editDescription = $('editDescription');
    const editClassroom = $('editClassroom');
    const editStartDate = $('editStartDate');
    const editEndDate = $('editEndDate');
    const editStartTime = $('editStartTime');
    const editEndTime = $('editEndTime');
    const editPeriodType = $('editPeriodType');
    const editRateMode = $('editRateMode');
    const editRateModeDisplay = $('editRateModeDisplay');

    const editUtilities = $('editUtilities');
    const editElectricity = $('editElectricity');
    const editWater = $('editWater');
    const editServiceChecks = [editUtilities, editElectricity, editWater];

    const editEstimatedTotal = $('editEstimatedTotal');
    const editDetectedPeriodLabel = $('editDetectedPeriodLabel');
    const editDetectedHoursLabel = $('editDetectedHoursLabel');

    const editClassroomError = $('editClassroomError');
    const editStartDateError = $('editStartDateError');
    const editEndDateError = $('editEndDateError');
    const editTimeError = $('editTimeError');
    const editServicesError = $('editServicesError');
    const editResponsibleError = $('editResponsibleError');
    const editDescriptionError = $('editDescriptionError');

    const customizeScope = $('customizeScope');
    const customizeDate = $('customizeDate');
    const customizeStartTime = $('customizeStartTime');
    const customizeEndTime = $('customizeEndTime');
    const customizeScopeError = $('customizeScopeError');
    const customizeDateError = $('customizeDateError');
    const customizeTimeError = $('customizeTimeError');

    const rentalStartDateIcon = $('rentalStartDateIcon');
    const rentalEndDateIcon = $('rentalEndDateIcon');
    const relatedStartDateIcon = $('relatedStartDateIcon');
    const relatedEndDateIcon = $('relatedEndDateIcon');
    const customizeDateIcon = $('customizeDateIcon');


    const createToastInstance = (id) => {
        const el = $(id);
        return el ? bootstrap.Toast.getOrCreateInstance(el, {delay: 2500}) : null;
    };

    const toasts = {
        ratesSaved: createToastInstance('ratesSavedToast'),
        rentalSaved: createToastInstance('rentalSavedToast'),
        deleteEntry: createToastInstance('deleteEntryToast'),
        download: createToastInstance('downloadToast'),
        customizeSaved: createToastInstance('customizeSavedToast'),
        editSaved: createToastInstance('editSavedToast'),
        relatedSaved: createToastInstance('relatedSavedToast'),
    };


    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    async function sendJson(url, method, payload) {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message || 'Ocurrió un error procesando la solicitud.');
        }

        return data;
    }

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
        confirmAddClassroomBtn.disabled = !validateNewClassroomName(false) || !newClassroomType?.value;
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

        newClassroomType?.addEventListener('change', () => {
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

            document.getElementById('hiddenNewClassroomType').value =
                newClassroomType.value;

            sessionStorage.setItem('reopenConfigureRatesModal', 'true');
            document.getElementById('addClassroomForm').submit();
        });
    }

    function requiresEndDate() {
        return true;
    }

    function getInclusiveDays(startValue, endValue) {
        if (!startValue) return 0;

        const start = new Date(`${startValue}T00:00:00`);
        const end = new Date(`${endValue || startValue}T00:00:00`);

        const diffMs = end - start;
        return Math.floor(diffMs / (1000 * 60 * 60 * 24)) + 1;
    }

    function resolveRateModeByDuration() {
        const days = getInclusiveDays(rentalStartDate.value, rentalEndDate.value);

        if (days <= 0) return '';

        if (days < 7) return 'daily';
        if (days < 28) return 'weekly';

        return 'monthly';
    }

    function resolveRelatedRateModeByDuration() {
        const days = getInclusiveDays(relatedStartDate.value, relatedEndDate.value);

        if (days <= 0) return '';
        if (days < 7) return 'daily';
        if (days < 28) return 'weekly';

        return 'monthly';
    }

    function updateRelatedAutomaticRateMode() {
        const mode = resolveRelatedRateModeByDuration();

        relatedRateMode.value = mode;
        relatedRateModeDisplay.value = rateModeLabel(mode);
    }

    function rateModeLabel(value) {
        if (value === 'daily') return 'Diario';
        if (value === 'weekly') return 'Semanal';
        if (value === 'monthly') return 'Mensual';
        return 'Se calculará automáticamente';
    }

    function updateAutomaticRateMode() {
        const mode = resolveRateModeByDuration();

        if (rentalRangeType) {
            rentalRangeType.value = mode;
        }

        if (rentalRateModeDisplay) {
            rentalRateModeDisplay.value = rateModeLabel(mode);
        }
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

    function getSelectedStartDateDay() {
        if (!rentalStartDate.value) return null;

        const date = new Date(`${rentalStartDate.value}T00:00:00`);
        //Sunday =0, Monday = 1, ... , Saturday = 6
        return date.getDay();
    }

    function getSelectedRelatedStartDateDay() {
        if (!relatedStartDate.value) return null;

        const date = new Date(`${relatedStartDate.value}T00:00:00`);
        return date.getDay();
    }

    function isRelatedWorkdayPeriod() {
        return relatedPeriodType?.value === 'workday';
    }

    function isRelatedNonWorkdayPeriod() {
        return ['non_workday_saturday', 'non_workday_sunday_holiday'].includes(relatedPeriodType?.value);
    }

    function updateRentalTimeOptions() {
        const previousStart = rentalStartTime.value;
        const previousEnd = rentalEndTime.value;
        const selectedDay = getSelectedStartDateDay();

        if (isWorkdayPeriod()) {
            buildTimeOptions(rentalStartTime, 7, 30, 16, 30, 'Seleccionar hora de inicio');
            buildTimeOptions(rentalEndTime, 7, 45, 16, 30, 'Seleccionar hora de fin');
        } else if (rentalPeriodType?.value === 'non_workday_saturday') {
        if (selectedDay === 6) {
            buildTimeOptions(rentalStartTime, 8, 0, 21, 30, 'Seleccionar hora de inicio');
            buildTimeOptions(rentalEndTime, 8, 15, 21, 30, 'Seleccionar hora de fin');
        } else {
            buildTimeOptions(rentalStartTime, 16, 30, 21, 30, 'Seleccionar hora de inicio');
            buildTimeOptions(rentalEndTime, 16, 45, 21, 30, 'Seleccionar hora de fin');
        }
    } else if (rentalPeriodType?.value === 'non_workday_sunday_holiday') {
            if (selectedDay === 0) {
                buildTimeOptions(rentalStartTime, 8, 0, 21, 30, 'Seleccionar hora de inicio');
                buildTimeOptions(rentalEndTime, 8, 15, 21, 30, 'Seleccionar hora de fin');
            } else {
                buildTimeOptions(rentalStartTime, 16, 30, 21, 30, 'Seleccionar hora de inicio');
                buildTimeOptions(rentalEndTime, 16, 45, 21, 30, 'Seleccionar hora de fin');
            }
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

    function updateRelatedTimeOptions() {
        const previousStart = relatedStartTime.value;
        const previousEnd = relatedEndTime.value;
        const selectedDay = getSelectedRelatedStartDateDay();

        if (isRelatedWorkdayPeriod()) {
            buildTimeOptions(relatedStartTime, 7, 30, 16, 30, 'Seleccionar hora de inicio');
            buildTimeOptions(relatedEndTime, 7, 45, 16, 30, 'Seleccionar hora de fin');
        } else if (relatedPeriodType?.value === 'non_workday_saturday') {
            if (selectedDay === 6) {
                buildTimeOptions(relatedStartTime, 8, 0, 21, 30, 'Seleccionar hora de inicio');
                buildTimeOptions(relatedEndTime, 8, 15, 21, 30, 'Seleccionar hora de fin');
            } else {
                buildTimeOptions(relatedStartTime, 16, 30, 21, 30, 'Seleccionar hora de inicio');
                buildTimeOptions(relatedEndTime, 16, 45, 21, 30, 'Seleccionar hora de fin');
            }
        } else if (relatedPeriodType?.value === 'non_workday_sunday_holiday') {
            if (selectedDay === 0) {
                buildTimeOptions(relatedStartTime, 8, 0, 21, 30, 'Seleccionar hora de inicio');
                buildTimeOptions(relatedEndTime, 8, 15, 21, 30, 'Seleccionar hora de fin');
            } else {
                buildTimeOptions(relatedStartTime, 16, 30, 21, 30, 'Seleccionar hora de inicio');
                buildTimeOptions(relatedEndTime, 16, 45, 21, 30, 'Seleccionar hora de fin');
            }
        } else {
            relatedStartTime.innerHTML = '<option value="" selected disabled>Primero selecciona el tipo de período</option>';
            relatedEndTime.innerHTML = '<option value="" selected disabled>Primero selecciona el tipo de período</option>';
            return;
        }

        if ([...relatedStartTime.options].some(option => option.value === previousStart)) {
            relatedStartTime.value = previousStart;
        }

        if ([...relatedEndTime.options].some(option => option.value === previousEnd)) {
            relatedEndTime.value = previousEnd;
        }
    }

    function getSelectedEditStartDateDay() {
        if (!editStartDate?.value) return null;

        const date = new Date(`${editStartDate.value}T00:00:00`);
        return date.getDay();
    }

    function isEditWorkdayPeriod() {
        return editPeriodType?.value === 'workday';
    }

    function isEditNonWorkdayPeriod() {
        return ['non_workday_saturday', 'non_workday_sunday_holiday'].includes(editPeriodType?.value);
    }

    function updateEditTimeOptions() {
        const previousStart = editStartTime?.value || '';
        const previousEnd = editEndTime?.value || '';
        const selectedDay = getSelectedEditStartDateDay();

        if (!editStartTime || !editEndTime) return;

        if (isEditWorkdayPeriod()) {
            buildTimeOptions(editStartTime, 7, 30, 16, 30, 'Seleccionar hora de inicio');
            buildTimeOptions(editEndTime, 7, 45, 16, 30, 'Seleccionar hora de fin');
        } else if (editPeriodType?.value === 'non_workday_saturday') {
            if (selectedDay === 6) {
                buildTimeOptions(editStartTime, 8, 0, 21, 30, 'Seleccionar hora de inicio');
                buildTimeOptions(editEndTime, 8, 15, 21, 30, 'Seleccionar hora de fin');
            } else {
                buildTimeOptions(editStartTime, 16, 30, 21, 30, 'Seleccionar hora de inicio');
                buildTimeOptions(editEndTime, 16, 45, 21, 30, 'Seleccionar hora de fin');
            }
        } else if (editPeriodType?.value === 'non_workday_sunday_holiday') {
            if (selectedDay === 0) {
                buildTimeOptions(editStartTime, 8, 0, 21, 30, 'Seleccionar hora de inicio');
                buildTimeOptions(editEndTime, 8, 15, 21, 30, 'Seleccionar hora de fin');
            } else {
                buildTimeOptions(editStartTime, 16, 30, 21, 30, 'Seleccionar hora de inicio');
                buildTimeOptions(editEndTime, 16, 45, 21, 30, 'Seleccionar hora de fin');
            }
        } else {
            editStartTime.innerHTML = '<option value="" selected disabled>Primero selecciona el tipo de período</option>';
            editEndTime.innerHTML = '<option value="" selected disabled>Primero selecciona el tipo de período</option>';
            return;
        }

        if ([...editStartTime.options].some(option => option.value === previousStart)) {
            editStartTime.value = previousStart;
        }

        if ([...editEndTime.options].some(option => option.value === previousEnd)) {
            editEndTime.value = previousEnd;
        }
    }

    function updateCustomizeTimeOptions() {
        const previousStart = customizeStartTime.value;
        const previousEnd = customizeEndTime.value;

        buildTimeOptions(customizeStartTime, 7, 30, 21, 30, 'Seleccionar hora de inicio');
        buildTimeOptions(customizeEndTime, 7, 45, 21, 30, 'Seleccionar hora de fin');

        if ([...customizeStartTime.options].some(option => option.value === previousStart)) {
            customizeStartTime.value = previousStart;
        }

        if ([...customizeEndTime.options].some(option => option.value === previousEnd)) {
            customizeEndTime.value = previousEnd;
        }
    }


    if (saveCustomizeDaysBtn) {
        saveCustomizeDaysBtn.addEventListener('click', async () => {
            const payload = validateCustomizeDaysForm(true);

            if (!payload) {
                updateCustomizeSaveState();
                return;
            }

            const body = {
                scope: payload.scope,
                date: payload.date,
                start_time: payload.start_time,
                end_time: payload.end_time,
            };

            const url = `/facility/events/${payload.event_id}/customize-days`;
            const method = 'POST';
        try {
            await sendJson(url, method, body);

            bootstrap.Modal.getOrCreateInstance($('customizeDaysModal')).hide();
            toasts.customizeSaved?.show();

            setTimeout(() => {
                window.location.reload();
            }, 900);
        } catch (error) {
            alert(error.message);
        }
    });
}

if (saveRelatedEventBtn) {
    saveRelatedEventBtn.addEventListener('click', async () => {
        const payload = validateRelatedEventForm(true);

        if (!payload) {
            updateRelatedSaveState();
            return;
        }

        const body = {
            classroom: payload.classroom,
            responsible: payload.responsible,
            description: payload.description,
            services: payload.services,
            period_type: payload.period_type,
            rate_mode: payload.rate_mode,
            event_date: payload.event_date,
            event_end_date: payload.end_date,
            start_time: payload.start_time,
            end_time: payload.end_time,
        };

        const url = relatedModalMode === 'edit'
            ? `/facility/events/${editingSubEventId}/sub-event`
            : `/facility/events/${payload.parent_event_id}/related`;

        const method = relatedModalMode === 'edit' ? 'PUT' : 'POST';

        try {
            await sendJson(url, method, body);

            bootstrap.Modal.getOrCreateInstance($('createRelatedModal')).hide();
            toasts.relatedSaved?.show();

            setTimeout(() => {
                window.location.reload();
            }, 900);
        } catch (error) {
            alert(error.message);
        }
    });
}

function setRelatedModalTitle(text) {
    const title = $('createRelatedModalLabel');
    if (title) title.textContent = text;
}

function setRelatedModalNotice(text) {
    const notice = $('relatedModalNoticeText');

    if (notice) {
        notice.textContent = text;
    }
}

function setRelatedSaveButtonText(text) {
    if (saveRelatedEventBtn) {
        saveRelatedEventBtn.textContent = text;
    }
}

function getParentRowForGroup(row) {
    const groupKey = row?.dataset.groupKey;
    if (!groupKey) return null;

    return facilityCostTableBody.querySelector(
        `tr.parent-event-row[data-group-key="${groupKey}"]`
    );
}

function setRelatedModalDateRangeFromParent(parentRow) {
    if (!parentRow) return;

    const startDate = parentRow.dataset.date || '';
    const endDate = parentRow.dataset.endDate || startDate;

    relatedStartDate.min = startDate;
    relatedStartDate.max = endDate;

    relatedEndDate.min = startDate;
    relatedEndDate.max = endDate;
}

function fillRelatedModalFromRow(row) {
    if (!row) return;

    relatedArea.value = row.dataset.classroom || '';
    relatedResponsible.value = row.dataset.responsible || '';
    relatedDescription.value = row.dataset.description || '';

    relatedStartDate.value = row.dataset.date || '';
    relatedEndDate.value = row.dataset.endDate || row.dataset.date || '';

    relatedPeriodType.value = row.dataset.periodType || '';
    relatedRateMode.value = row.dataset.rateMode || '';
    relatedRateModeDisplay.value = rateModeLabel(row.dataset.rateMode || '');

    updateRelatedTimeOptions();

    relatedStartTime.value = row.dataset.startTime || '';
    relatedEndTime.value = row.dataset.endTime || '';

    relatedUtilities.checked = false;
    relatedElectricity.checked = false;
    relatedWater.checked = false;

    let services = [];

    try {
        services = JSON.parse(row.dataset.services || '[]');
    } catch {
        services = [];
    }

    relatedUtilities.checked = services.includes('utilities');
    relatedElectricity.checked = services.includes('electricity');
    relatedWater.checked = services.includes('water');

    updateRelatedSummary();
    updateRelatedSaveState();
}

    let pendingEditPayload = null;
    let editingIsCustomDay = false;

    async function submitEditEvent(payload, deleteOutOfRangeCustomDays = false) {
        try {
            await sendJson(`/facility/events/${payload.event_id}`, 'PUT', {
                classroom: payload.classroom,
                responsible: payload.responsible,
                description: payload.description,
                services: payload.services,
                period_type: payload.period_type,
                rate_mode: payload.rate_mode,
                event_date: payload.event_date,
                event_end_date: payload.event_end_date,
                start_time: payload.start_time,
                end_time: payload.end_time,
                delete_out_of_range_custom_days: deleteOutOfRangeCustomDays,
            });

            bootstrap.Modal.getOrCreateInstance($('editEventModal')).hide();
            toasts.editSaved?.show();

            setTimeout(() => {
                window.location.reload();
            }, 900);
        } catch (error) {
            alert(error.message);
        }
    }

    const confirmParentRangeDeleteBtn = $('confirmParentRangeDeleteBtn');

    function getCustomDaysOutsideEditedParentRange(parentId, newStart, newEnd) {
        const parentRow = document.querySelector(`tr.parent-event-row[data-entry-id="${parentId}"]`);
        const groupKey = parentRow?.dataset.groupKey;

        if (!groupKey) return [];

        const start = new Date(`${newStart}T00:00:00`);
        const end = new Date(`${newEnd}T00:00:00`);

        return [...document.querySelectorAll(`tr.sub-event-row[data-group-key="${groupKey}"][data-sub-event-type="custom_day"]`)]
            .filter(row => {
                const customDate = new Date(`${row.dataset.date}T00:00:00`);
                return customDate < start || customDate > end;
            });
    }

    function showParentRangeWarning(count) {
        const text = $('parentRangeWarningText');

        if (text) {
            text.textContent = `Esta edición deja ${count} modificación(es) fuera del rango del evento padre. Si continúas, esas modificaciones serán eliminadas.`;
        }

        bootstrap.Modal.getOrCreateInstance($('parentRangeWarningModal')).show();
    }

    confirmParentRangeDeleteBtn?.addEventListener('click', async () => {
        if (!pendingEditPayload) return;

        bootstrap.Modal.getOrCreateInstance($('parentRangeWarningModal')).hide();

        await submitEditEvent(pendingEditPayload, true);

        pendingEditPayload = null;
    });

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
        toggleRentalDateRangeUI();
    }


    customizeScope?.addEventListener('change', () => {
        clearFieldError(customizeScope, customizeScopeError);

        if (!customizeScope.value) {
            setFieldError(customizeScope, customizeScopeError, 'Selecciona el alcance de la modificación.');
        }

        updateCustomizeSaveState();
    });

    [customizeStartTime, customizeEndTime].forEach(input => {
        input?.addEventListener('change', () => {
            customizeStartTime.classList.remove('is-invalid');
            customizeEndTime.classList.remove('is-invalid');
            customizeTimeError.textContent = '';

            if (!customizeStartTime.value && !customizeEndTime.value) {
                customizeTimeError.textContent = '';
            } else if (customizeStartTime.value && !customizeEndTime.value) {
                clearFieldError(customizeEndTime, customizeTimeError);
            } else if (!customizeStartTime.value && customizeEndTime.value) {
                clearFieldError(customizeStartTime, customizeTimeError);
            }else if (timeToMinutes(customizeEndTime.value) <= timeToMinutes(customizeStartTime.value)) {
                customizeStartTime.classList.add('is-invalid');
                customizeEndTime.classList.add('is-invalid');
                customizeTimeError.textContent = 'La hora de fin debe ser mayor que la hora de inicio.';
            }

            updateCustomizeSaveState();
        });
    });

    relatedArea?.addEventListener('change', () => {
        clearFieldError(relatedArea, relatedAreaError);

        if (!relatedArea.value) {
            setFieldError(relatedArea, relatedAreaError, 'El área es requerida.');
        }

        updateRelatedSummary();
        updateRelatedSaveState();
    });

    attachResponsibleBehavior(
        editResponsible,
        editResponsibleError,
        (showError) => validateResponsible(showError, editResponsible, editResponsibleError),
        updateEditSaveState
    );

    attachDescriptionBehavior(
        editDescription,
        editDescriptionError,
        (showError) => validateDescription(showError, editDescription, editDescriptionError),
        updateEditSaveState
    );

    relatedPeriodType?.addEventListener('change', () => {
        relatedStartTime.value = '';
        relatedEndTime.value = '';
        updateRelatedTimeOptions();
        updateRelatedSummary();
        updateRelatedSaveState();
    });

    [relatedStartTime, relatedEndTime].forEach(input => {
        input?.addEventListener('change', () => {
            relatedStartTime.classList.remove('is-invalid');
            relatedEndTime.classList.remove('is-invalid');
            relatedTimeError.textContent = '';

            if (!relatedStartTime.value || !relatedEndTime.value) {
                relatedStartTime.classList.add('is-invalid');
                relatedEndTime.classList.add('is-invalid');
                relatedTimeError.textContent = 'Selecciona hora de inicio y hora de fin.';
            } else if (timeToMinutes(relatedEndTime.value) <= timeToMinutes(relatedStartTime.value)) {
                relatedStartTime.classList.add('is-invalid');
                relatedEndTime.classList.add('is-invalid');
                relatedTimeError.textContent = 'La hora de fin debe ser mayor que la hora de inicio.';
            }

            updateRelatedSummary();
            updateRelatedSaveState();
        });
    });

    relatedServiceChecks.forEach(input => {
        input?.addEventListener('change', () => {
            toggleRelatedServicesError(!hasRelatedServices());
            updateRelatedSummary();
            updateRelatedSaveState();
        });
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

    function hasRelatedServices() {
        return relatedServiceChecks.some(input => input?.checked);
    }

    function toggleRelatedServicesError(show) {
        const error = $('relatedServicesError');
        if (!error) return;

        error.classList.toggle('d-none', !show);
        error.classList.toggle('d-block', show);
    }

    function toggleServicesError(show) {
        servicesRequiredMessage.classList.toggle('d-none', !show);
        servicesRequiredMessage.classList.toggle('d-block', show);
    }

    function setFieldError(field, errorElement, message) {
        if (!field) return;

        field.classList.add('is-invalid');

        if (field._flatpickr?.altInput) {
            field._flatpickr.altInput.classList.add('is-invalid');
        }

        if (errorElement) errorElement.textContent = message;
    }

    function clearFieldError(field, errorElement) {
        if (!field) return;

        field.classList.remove('is-invalid');

        if (field._flatpickr?.altInput) {
            field._flatpickr.altInput.classList.remove('is-invalid');
        }

        if (errorElement) errorElement.textContent = '';
    }

    function attachResponsibleBehavior(input, errorElement, validateFn, saveStateFn) {
        input?.addEventListener('input', () => {
            const value = input.value;

            if (value.length > 40) {
                input.value = value.slice(0, 40);
                setFieldError(input, errorElement, 'Has alcanzado el máximo de 40 caracteres. No puedes escribir más.');
            } else if (value.length === 40) {
                setFieldError(input, errorElement, 'Has alcanzado el máximo de 40 caracteres, puedes aún someter esa cantidad.');
            } else {
                validateFn(true);
            }

            saveStateFn();
        });

        input?.addEventListener('blur', () => {
            input.value = input.value.trim();

            if (input.value.length === 40) {
                clearFieldError(input, errorElement);
            } else {
                validateFn(true);
            }

            saveStateFn();
        });
    }

    function attachDescriptionBehavior(input, errorElement, validateFn, saveStateFn) {
        input?.addEventListener('input', () => {
            const value = input.value;

            if (value.length > 250) {
                input.value = value.slice(0, 250);
                setFieldError(input, errorElement, 'Has alcanzado el máximo de 250 caracteres. No puedes escribir más.');
            } else if (value.length === 250) {
                setFieldError(input, errorElement, 'Has alcanzado el máximo de 250 caracteres, puedes aún someter esa cantidad.');
            } else {
                validateFn(true);
            }

            saveStateFn();
        });

        input?.addEventListener('blur', () => {
            input.value = input.value.trim();

            if (input.value.length === 250) {
                clearFieldError(input, errorElement);
            } else {
                validateFn(true);
            }

            saveStateFn();
        });
    }

    attachResponsibleBehavior(
        editResponsible,
        editResponsibleError,
        validateEditEventForm,
        updateEditSaveState
    );

    attachDescriptionBehavior(
        editDescription,
        editDescriptionError,
        validateEditEventForm,
        updateEditSaveState
    );

    function initializeFacilityDatePickers() {
        const options = {
            locale: Spanish,
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd-F-Y',
            allowInput: false,
            disableMobile: true,
            minDate: 'today'
        };

        const setupPicker = (input, icon, onChangeCallback) => {
            if (!input) return;

            flatpickr(input, {
                ...options,
                onChange: () => {
                    if (typeof onChangeCallback === 'function') {
                        onChangeCallback();
                    }
                }
            });

            icon?.addEventListener('click', () => {
                input._flatpickr?.open();
            });
        };

        setupPicker(rentalStartDate, rentalStartDateIcon, () => {
            updateAutomaticRateMode();

            if (rentalEndDate?._flatpickr && rentalStartDate.value) {
                rentalEndDate._flatpickr.set('minDate', rentalStartDate.value);
            }

            validateRentalDates(true, false);
            updateRentalDateRestrictions();
            updateRentalTimeOptions();
            calculateRentalEstimate();
            updateRentalSaveState();
        });

        setupPicker(rentalEndDate, rentalEndDateIcon, () => {
            updateAutomaticRateMode();
            validateRentalDates(true, false);
            updateRentalDateRestrictions();
            calculateRentalEstimate();
            updateRentalSaveState();
        });

        setupPicker(customizeDate, customizeDateIcon, () => {
            clearFieldError(customizeDate, customizeDateError);

            if (!customizeDate.value) {
                setFieldError(customizeDate, customizeDateError, 'La fecha es requerida.');
            }

            updateCustomizeSaveState();
        });

        setupPicker(relatedStartDate, relatedStartDateIcon, () => {
            updateRelatedAutomaticRateMode();

            if (relatedEndDate?._flatpickr && relatedStartDate.value) {
                relatedEndDate._flatpickr.set('minDate', relatedStartDate.value);
            }

            validateRelatedEventForm(true);
            updateRelatedTimeOptions();
            updateRelatedSummary();
            updateRelatedSaveState();
        });

        setupPicker(relatedEndDate, relatedEndDateIcon, () => {
            updateRelatedAutomaticRateMode();
            validateRelatedEventForm(true);
            updateRelatedSummary();
            updateRelatedSaveState();
        });

        setupPicker(editStartDate, null, () => {
            updateEditAutomaticRateMode();

            if (editEndDate?._flatpickr && editStartDate.value) {
                editEndDate._flatpickr.set('minDate', editStartDate.value);
            }

            clearFieldError(editStartDate, editStartDateError);
            validateEditEventForm(true);
            updateEditTimeOptions();
            updateEditSummary();
            updateEditSaveState();
        });

        setupPicker(editEndDate, null, () => {
            updateEditAutomaticRateMode();
            clearFieldError(editEndDate, editEndDateError);
            validateEditEventForm(true);
            updateEditSummary();
            updateEditSaveState();
        });
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

    function validateResponsible(showError = true, input = rentalResponsible, errorElement = rentalResponsibleError) {
        const value = input.value.trim();
        const regex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;

        if (showError){
            clearFieldError(input, errorElement);
        }

        if (!value) {
            if (showError) {
                setFieldError(input, errorElement, 'El responsable es un campo obligatorio para llenar.');
            }
            return false;
        }

        if (!regex.test(value)){
            if (showError){
                setFieldError(input, errorElement, 'Solo se permiten letras y espacios.');
            }
            return false;
        }

        if (value.length < 8) {
            if (showError) {
                setFieldError(input, errorElement,  'El responsable debe tener al menos 8 caracteres.');
            }
            return false;
        }

        if (value.length > 40) {
            if (showError) {
                setFieldError(input, errorElement, 'El responsable no puede exceder 40 caracteres.');
            }
            return false;
        }

        return true;
    }

    function validateDescription(showError = true, input = rentalDescription, errorElement = rentalDescriptionError) {
        const value = input.value.trim();
        const regex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;

        if (showError) {
            clearFieldError(input, errorElement);
        }

        if (!value) {
            if (showError) {
                setFieldError(input, errorElement, 'La descripción es obligatoria.');
            }
            return false;
        }

        if(!regex.test(value)) {
            if(showError) {
                setFieldError(input, errorElement, 'Solo se permiten letras, números, espacios, punto, coma y guion.');
            }
            return false;
        }

        if (value.length < 10) {
            if (showError) setFieldError(input, errorElement, 'La descripción debe tener al menos 10 caracteres.');
            return false;
        }

        if (value.length > 250) {
            if (showError) {
                setFieldError( input, errorElement, 'Has alcanzado el máximo de 250 caracteres. No puedes escribir más.'
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

    function getBillingUnits(startValue, endValue, rateMode) {
        const daysUsed = getInclusiveDays(startValue, endValue || startValue);

        if (daysUsed <= 0) return 1;

        if (rateMode === 'daily') {
            return daysUsed;
        }

        if (rateMode === 'weekly') {
            return Math.ceil(daysUsed / 7);
        }

        if (rateMode === 'monthly') {
            return getMonthsCrossed(startValue, endValue || startValue);
        }

        return 1;
    }

    function getMonthsCrossed(startValue, endValue) {
        if (!startValue) return 1;

        const start = new Date(`${startValue}T00:00:00`);
        const end = new Date(`${endValue || startValue}T00:00:00`);

        const startMonthIndex = start.getFullYear() * 12 + start.getMonth();
        const endMonthIndex = end.getFullYear() * 12 + end.getMonth();

        return Math.max(1, endMonthIndex - startMonthIndex + 1);
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

        const unitsUsed = getBillingUnits(
            rentalStartDate.value,
            rentalEndDate.value,
            rentalRangeType.value
        );

        let total = area * selectedRate * unitsUsed;

        if (rentalUtilities.checked) total += toNumber(data.utilities) * totalHours;
        if (rentalElectricity.checked) total += toNumber(data.electricity) * totalHours;
        if (rentalWater.checked) total += toNumber(data.water) * totalHours;

        rentalEstimatedTotal.textContent = formatMoney(total);
        rentalEstimatedTotalInput.value = total.toFixed(2);
        return total;
    }
    function updateRelatedSummary() {
        const classroomId = relatedArea.value;
        const startTime = relatedStartTime.value;
        const endTime = relatedEndTime.value;
        const periodType = relatedPeriodType.value;
        const rateMode = relatedRateMode.value;

        const dailyHours = calculateHours(startTime, endTime);

        let totalDays = 1;

        if (relatedStartDate.value && relatedEndDate.value) {
            totalDays = getInclusiveDays(relatedStartDate.value, relatedEndDate.value);
        }

        const totalHours = dailyHours * Math.max(totalDays, 1);

        if (relatedDetectedPeriodLabel) {
            relatedDetectedPeriodLabel.textContent = periodLabelFromValue(periodType);
        }

        if (relatedDetectedHoursLabel) {
            relatedDetectedHoursLabel.textContent = `${totalHours.toFixed(2)} horas`;
        }

        if (
            !classroomId ||
            !dailyHours ||
            !periodType ||
            !rateMode ||
            !ratesByClassroom[classroomId]
        ) {
            relatedEstimatedTotal.textContent = formatMoney(0);
            return 0;
        }

        const data = ratesByClassroom[classroomId];
        const area = toNumber(data.area);
        const periodRates = getPeriodRateData(classroomId, periodType);

        let selectedRate = 0;

        if (rateMode === 'daily') {
            selectedRate = periodRates.daily;
        } else if (rateMode === 'weekly') {
            selectedRate = periodRates.weekly;
        } else if (rateMode === 'monthly') {
            selectedRate = periodRates.monthly;
        }

        const unitsUsed = getBillingUnits(
            relatedStartDate.value,
            relatedEndDate.value,
            rateMode
        );

        let total = area * selectedRate * unitsUsed;

        if (relatedUtilities.checked) total += toNumber(data.utilities) * totalHours;
        if (relatedElectricity.checked) total += toNumber(data.electricity) * totalHours;
        if (relatedWater.checked) total += toNumber(data.water) * totalHours;

        relatedEstimatedTotal.textContent = formatMoney(total);

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
            } else if (isNonWorkdayPeriod()) {
                const selectedDay = getSelectedStartDateDay();

                // 4:30 PM
                let minMinutes = 990;
                // 9:30 PM
                let maxMinutes = 1290;
                let message = 'Para días lunes a viernes en período no laborable solo se permiten horarios de 4:30 p.m. a 9:30 p.m.';

                if (
                    rentalPeriodType.value === 'non_workday_saturday' &&
                    selectedDay === 6
                ) {
                    // 8:00 AM
                    minMinutes = 480;
                    message = 'Para sábado solo se permiten horarios de 8:00 a.m. a 9:30 p.m.';
                }

                if (
                    rentalPeriodType.value === 'non_workday_sunday_holiday' &&
                    selectedDay === 0
                ) {
                    // 8:00 AM
                    minMinutes = 480;
                    message = 'Para domingo o festivo solo se permiten horarios de 8:00 a.m. a 9:30 p.m.';
                }

                if (startMinutes < minMinutes || endMinutes > maxMinutes) {
                    validTimes = false;
                    rentalStartTime.classList.add('is-invalid');
                    rentalEndTime.classList.add('is-invalid');

                    if (timeError) {
                        timeError.textContent = message;
                    }
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
        return [...facilityCostTableBody.querySelectorAll('tr[data-entry-id]')]
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

        const allGroups = [...facilityCostTableBody.querySelectorAll('tr.event-group-header')];
        const allRows = [...facilityCostTableBody.querySelectorAll('tr[data-entry-id]')];
        const allTableRows = [...facilityCostTableBody.querySelectorAll('tr')];

        // Hide absolutely everything first: headers, parent rows, sub rows, spacers.
        allTableRows.forEach((row) => {
            row.style.display = 'none';
        });

        // A group matches if at least one real row inside the group matches the filters.
        const filteredGroups = allGroups.filter((groupHeader) => {
            const groupKey = groupHeader.dataset.groupKey;
            const groupRows = allRows.filter(row => row.dataset.groupKey === groupKey);

            return groupRows.some((row) => {
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
        });

        // Pagination is now based on event groups, not individual rows.
        const totalPages = Math.max(1, Math.ceil(filteredGroups.length / FACILITY_COSTS_PER_PAGE));

        if (currentFacilityCostsPage > totalPages) {
            currentFacilityCostsPage = totalPages;
        }

        const start = (currentFacilityCostsPage - 1) * FACILITY_COSTS_PER_PAGE;
        const end = start + FACILITY_COSTS_PER_PAGE;
        const paginatedGroups = filteredGroups.slice(start, end);

        // Show selected groups: header + all real rows inside + spacer.
        paginatedGroups.forEach((groupHeader) => {
            const groupKey = groupHeader.dataset.groupKey;

            groupHeader.style.display = '';

            allRows
                .filter(row => row.dataset.groupKey === groupKey)
                .forEach(row => {
                    row.style.display = '';
                });

            const totalRow = facilityCostTableBody.querySelector(
                `tr.event-group-total[data-group-key="${groupKey}"]`
            );

            if (totalRow) {
                totalRow.style.display = '';
            }

            const spacer = facilityCostTableBody.querySelector(
                `tr.event-group-spacer[data-group-key="${groupKey}"]`
            );

            if (spacer) {
                spacer.style.display = '';
            }
        });

        // Total only from visible real rows, not group headers/spacers.
        const visibleRows = allRows.filter((row) => row.style.display !== 'none');

        const totalAmount = visibleRows.reduce((sum, row) => {
            const amountCell = row.querySelector('td:nth-child(10)');
            return sum + parseMoney(amountCell?.textContent || '0');
        }, 0);

        facilityCostGrandTotal.textContent = formatMoney(totalAmount);

        const hasGroups = filteredGroups.length > 0;
        facilityCostTable.classList.toggle('d-none', !hasGroups);
        facilityCostEmptyState.classList.toggle('d-none', hasGroups);

        renderLocalPagination(
            facilityCostPagination,
            currentFacilityCostsPage,
            filteredGroups.length,
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


    function openModalById(modalId) {
        const modalEl = $(modalId);
        if (!modalEl) return;

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function clearActionRadios() {
        document.querySelectorAll('.action-radio').forEach((radio) => {
            radio.checked = false;
        });
    }
    function bindDeleteButtons() {
        deleteButtons().forEach((btn) => {
            btn.onchange = () => {
                if (!btn.checked) return;

                selectedDeleteUrl = btn.dataset.deleteUrl;
                openModalById('deleteCostEntryModal');
            };
        });
    }

    document.addEventListener('click', (event) => {
        const btn = event.target.closest('.edit-cost-row-btn');
        if (!btn) return;

        btn.checked = true;

        const row = btn.closest('tr');
        if (!row) return;

        editingIsCustomDay = row.dataset.subEventType === 'custom_day';

        editEventId.value = btn.dataset.entryId;
        editClassroom.value = row.dataset.classroom || '';
        editResponsible.value = row.dataset.responsible || '';
        editDescription.value = row.dataset.description || '';
        editStartDate.value = row.dataset.date || '';
        editEndDate.value = row.dataset.endDate || row.dataset.date || '';
        editPeriodType.value = row.dataset.periodType || '';
        editRateMode.value = row.dataset.rateMode || '';
        editRateModeDisplay.value = rateModeLabel(row.dataset.rateMode || '');

        updateEditTimeOptions();

        editStartTime.value = row.dataset.startTime || '';
        editEndTime.value = row.dataset.endTime || '';

        if (editUtilities) editUtilities.checked = false;
        if (editElectricity) editElectricity.checked = false;
        if (editWater) editWater.checked = false;

        let services = [];

        try {
            services = JSON.parse(row.dataset.services || '[]');
        } catch {
            services = [];
        }

        if (editUtilities) editUtilities.checked = services.includes('utilities');
        if (editElectricity) editElectricity.checked = services.includes('electricity');
        if (editWater) editWater.checked = services.includes('water');

        const disableFullFields = editingIsCustomDay;

        if (editClassroom) editClassroom.disabled = disableFullFields;
        if (editResponsible) editResponsible.disabled = disableFullFields;
        if (editDescription) editDescription.disabled = disableFullFields;

        if (editUtilities) editUtilities.disabled = disableFullFields;
        if (editElectricity) editElectricity.disabled = disableFullFields;
        if (editWater) editWater.disabled = disableFullFields;

        clearEditErrors();
        updateEditSummary();
        updateEditSaveState();

        openModalById('editEventModal');
    });

    function bindOptionThreeButtons() {

        customizeDaysButtons().forEach((btn) => {
            btn.onclick = () => {
                const row = btn.closest('tr');

                if (customizeEventId) {
                    customizeEventId.value = btn.dataset.entryId;
                }

                const startDate = row?.dataset.date || '';
                const endDate = row?.dataset.endDate || startDate;

                customizeScope.value = '';

                customizeDate.value = '';
                customizeDate.min = startDate;
                customizeDate.max = endDate;

                if (customizeDate?._flatpickr) {
                    customizeDate._flatpickr.set('minDate', startDate);
                    customizeDate._flatpickr.set('maxDate', endDate);
                    customizeDate._flatpickr.clear();
                    customizeDate._flatpickr.jumpToDate(startDate);
                }

                customizeStartTime.value = '';
                customizeEndTime.value = '';

                clearFieldError(customizeScope, customizeScopeError);
                clearFieldError(customizeDate, customizeDateError);

                updateCustomizeTimeOptions();

                customizeStartTime.classList.remove('is-invalid');
                customizeEndTime.classList.remove('is-invalid');
                customizeTimeError.textContent = '';

                updateCustomizeSaveState();

                console.log('Personalizar días para evento:', btn.dataset.entryId);
                openModalById('customizeDaysModal');
            };
        });

        createRelatedButtons().forEach((btn) => {
            btn.onclick = () => {
                const row = btn.closest('tr');

                relatedModalMode = 'create';
                editingSubEventId = null;
                setRelatedModalTitle('Crear Evento Relacionado');
                setRelatedSaveButtonText('Guardar Evento Relacionado');

                setRelatedModalNotice(
                    'Esta opción permite agregar otra área relacionada al mismo evento principal. Su costo se suma al total del evento, sin alterar el costo del evento principal.'
                );

                relatedParentEventId.value = btn.dataset.entryId;

                const startDate = row?.dataset.date || '';
                const endDate = row?.dataset.endDate || startDate;

                relatedStartDate.min = startDate;
                relatedStartDate.max = endDate;

                relatedEndDate.min = startDate;
                relatedEndDate.max = endDate;

                relatedArea.value = '';
                relatedResponsible.value = row?.children[2]?.textContent.trim() || '';
                relatedDescription.value = row?.children[4]?.textContent.trim() || '';

                relatedUtilities.checked = false;
                relatedElectricity.checked = false;
                relatedWater.checked = false;

                relatedPeriodType.value = '';
                relatedRateMode.value = '';
                relatedRateModeDisplay.value = 'Se calculará automáticamente';

                relatedStartDate.value = '';
                relatedEndDate.value = '';

                relatedStartTime.innerHTML = '<option value="" selected disabled>Primero selecciona el tipo de período</option>';
                relatedEndTime.innerHTML = '<option value="" selected disabled>Primero selecciona el tipo de período</option>';

                relatedEstimatedTotal.textContent = '$0.00';
                if (relatedDetectedPeriodLabel) relatedDetectedPeriodLabel.textContent = '—';
                if (relatedDetectedHoursLabel) relatedDetectedHoursLabel.textContent = '0.00 horas';

                clearFieldError(relatedArea, relatedAreaError);
                clearFieldError(relatedResponsible, relatedResponsibleError);
                clearFieldError(relatedDescription, relatedDescriptionError);
                clearFieldError(relatedStartDate, relatedStartDateError);
                clearFieldError(relatedEndDate, relatedEndDateError);

                relatedStartTime.classList.remove('is-invalid');
                relatedEndTime.classList.remove('is-invalid');
                relatedTimeError.textContent = '';

                toggleRelatedServicesError(false);
                updateRelatedSaveState();

                console.log('Crear evento relacionado desde:', btn.dataset.entryId);
                openModalById('createRelatedModal');
            };
        });

        editSubEventButtons().forEach((btn) => {
            btn.onclick = () => {
                relatedModalMode = 'edit';
                editingSubEventId = btn.dataset.entryId;

                setRelatedModalTitle('Editar Sub-evento');
                setRelatedSaveButtonText('Actualizar Sub-evento');

                setRelatedModalNotice(
                    'Esta opción permite modificar únicamente este sub-evento. Los cambios recalcularán su costo y se mantendrán dentro del rango de fechas del evento principal.'
                );

                const row = btn.closest('tr');
                const parentRow = getParentRowForGroup(row);

                relatedParentEventId.value = editingSubEventId;

                setRelatedModalDateRangeFromParent(parentRow);
                fillRelatedModalFromRow(row);

                clearFieldError(relatedArea, relatedAreaError);
                clearFieldError(relatedResponsible, relatedResponsibleError);
                clearFieldError(relatedDescription, relatedDescriptionError);
                clearFieldError(relatedStartDate, relatedStartDateError);
                clearFieldError(relatedEndDate, relatedEndDateError);

                relatedStartTime.classList.remove('is-invalid');
                relatedEndTime.classList.remove('is-invalid');
                relatedTimeError.textContent = '';

                toggleRelatedServicesError(false);
                updateRelatedSaveState();
            };
        });
    }

    function validateCustomizeDaysForm(showError = true) {
        let valid = true;

        if (showError) {
            clearFieldError(customizeScope, customizeScopeError);
            clearFieldError(customizeDate, customizeDateError);

            if (customizeStartTime) customizeStartTime.classList.remove('is-invalid');
            if (customizeEndTime) customizeEndTime.classList.remove('is-invalid');
            if (customizeTimeError) customizeTimeError.textContent = '';
        }

        if (!customizeEventId?.value) {
            valid = false;
        }

        if (!customizeScope?.value) {
            valid = false;
            if (showError) {
                setFieldError(customizeScope, customizeScopeError, 'Selecciona el alcance de la modificación.');
            }
        }

        if (!customizeDate?.value) {
            valid = false;
            if (showError) {
                setFieldError(customizeDate, customizeDateError, 'La fecha es requerida.');
            }
        }

        if (!customizeStartTime?.value || !customizeEndTime?.value) {
            valid = false;
            if (showError) {
                customizeStartTime?.classList.add('is-invalid');
                customizeEndTime?.classList.add('is-invalid');
                if (customizeTimeError) customizeTimeError.textContent = 'Selecciona hora de inicio y hora de fin.';
            }
        } else if (timeToMinutes(customizeEndTime.value) <= timeToMinutes(customizeStartTime.value)) {
            valid = false;
            if (showError) {
                customizeStartTime?.classList.add('is-invalid');
                customizeEndTime?.classList.add('is-invalid');
                if (customizeTimeError) customizeTimeError.textContent = 'La hora de fin debe ser mayor que la hora de inicio.';
            }
        }

        if (!valid) return null;

        return {
            event_id: customizeEventId.value,
            scope: customizeScope.value,
            date: customizeDate.value,
            start_time: customizeStartTime.value,
            end_time: customizeEndTime.value
        };
    }

    function updateCustomizeSaveState() {
        if (!saveCustomizeDaysBtn) return;
        saveCustomizeDaysBtn.disabled = !validateCustomizeDaysForm(false);
    }


    function hasEditServices() {
        return editServiceChecks.some(input => input?.checked);
    }

    function toggleEditServicesError(show) {
        if (!editServicesError) return;

        editServicesError.classList.toggle('d-none', !show);
        editServicesError.classList.toggle('d-block', show);
    }

    function clearEditErrors() {
        clearFieldError(editClassroom, editClassroomError);
        clearFieldError(editResponsible, editResponsibleError);
        clearFieldError(editDescription, editDescriptionError);
        clearFieldError(editStartDate, editStartDateError);
        clearFieldError(editEndDate, editEndDateError);

        editStartTime?.classList.remove('is-invalid');
        editEndTime?.classList.remove('is-invalid');

        if (editTimeError) editTimeError.textContent = '';

        toggleEditServicesError(false);
    }

    function updateEditAutomaticRateMode() {
        if (!editRateMode || !editRateModeDisplay) return;

        const days = getInclusiveDays(editStartDate?.value, editEndDate?.value);

        let mode = '';

        if (days > 0 && days < 7) {
            mode = 'daily';
        } else if (days >= 7 && days < 28) {
            mode = 'weekly';
        } else if (days >= 28) {
            mode = 'monthly';
        }

        editRateMode.value = mode;
        editRateModeDisplay.value = rateModeLabel(mode);
    }

    function updateEditSummary() {
        updateEditAutomaticRateMode();

        const classroomId = editClassroom?.value;
        const startTime = editStartTime?.value;
        const endTime = editEndTime?.value;
        const periodType = editPeriodType?.value;
        const rateMode = editRateMode?.value;

        const dailyHours = calculateHours(startTime, endTime);
        const totalDays = getInclusiveDays(editStartDate?.value, editEndDate?.value);
        const totalHours = dailyHours * Math.max(totalDays, 1);

        if (editDetectedPeriodLabel) {
            editDetectedPeriodLabel.textContent = periodLabelFromValue(periodType);
        }

        if (editDetectedHoursLabel) {
            editDetectedHoursLabel.textContent = `${totalHours.toFixed(2)} horas`;
        }

        if (
            !classroomId ||
            !dailyHours ||
            !periodType ||
            !rateMode ||
            !ratesByClassroom[classroomId]
        ) {
            if (editEstimatedTotal) editEstimatedTotal.textContent = formatMoney(0);
            return 0;
        }

        const data = ratesByClassroom[classroomId];
        const area = toNumber(data.area);
        const periodRates = getPeriodRateData(classroomId, periodType);

        let selectedRate = 0;

        if (rateMode === 'daily') selectedRate = periodRates.daily;
        if (rateMode === 'weekly') selectedRate = periodRates.weekly;
        if (rateMode === 'monthly') selectedRate = periodRates.monthly;

        const unitsUsed = getBillingUnits(
            editStartDate.value,
            editEndDate.value,
            rateMode
        );

        let total = area * selectedRate * unitsUsed;

        if (editUtilities?.checked) total += toNumber(data.utilities) * totalHours;
        if (editElectricity?.checked) total += toNumber(data.electricity) * totalHours;
        if (editWater?.checked) total += toNumber(data.water) * totalHours;

        if (editEstimatedTotal) {
            editEstimatedTotal.textContent = formatMoney(total);
        }

        return total;
    }

    function validateEditEventForm(showError = true) {
        let valid = true;

        const services = [];
        if (editUtilities?.checked) services.push('utilities');
        if (editElectricity?.checked) services.push('electricity');
        if (editWater?.checked) services.push('water');

        const responsible = editResponsible?.value.trim() || '';
        const description = editDescription?.value.trim() || '';

        const responsibleRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;
        const descriptionRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;

        if (showError) {
            clearEditErrors();
        }

        if (!editEventId?.value) {
            valid = false;
        }

        if (!editClassroom?.value) {
            valid = false;
            if (showError) setFieldError(editClassroom, editClassroomError, 'El área es requerida.');
        }

        if (!responsible) {
            valid = false;
            if (showError) setFieldError(editResponsible, editResponsibleError, 'El responsable es obligatorio.');
        } else if (!responsibleRegex.test(responsible)) {
            valid = false;
            if (showError) setFieldError(editResponsible, editResponsibleError, 'Solo se permiten letras y espacios.');
        } else if (responsible.length < 8) {
            valid = false;
            if (showError) setFieldError(editResponsible, editResponsibleError, 'El responsable debe tener al menos 8 caracteres.');
        } else if (responsible.length > 40) {
            valid = false;
            if (showError) setFieldError(editResponsible, editResponsibleError, 'El responsable no puede exceder 40 caracteres.');
        }

        if (!description) {
            valid = false;
            if (showError) setFieldError(editDescription, editDescriptionError, 'La descripción es obligatoria.');
        } else if (!descriptionRegex.test(description)) {
            valid = false;
            if (showError) setFieldError(editDescription, editDescriptionError, 'Solo se permiten letras, números, espacios, punto, coma y guion.');
        } else if (description.length < 10) {
            valid = false;
            if (showError) setFieldError(editDescription, editDescriptionError, 'La descripción debe tener al menos 10 caracteres.');
        } else if (description.length > 250) {
            valid = false;
            if (showError) setFieldError(editDescription, editDescriptionError, 'La descripción no puede exceder 250 caracteres.');
        }

        if (!editStartDate?.value) {
            valid = false;
            if (showError) setFieldError(editStartDate, editStartDateError, 'La fecha de inicio es requerida.');
        }

        if (!editEndDate?.value) {
            valid = false;
            if (showError) setFieldError(editEndDate, editEndDateError, 'La fecha de fin es requerida.');
        }

        if (editStartDate?.value && editEndDate?.value) {
            const start = new Date(`${editStartDate.value}T00:00:00`);
            const end = new Date(`${editEndDate.value}T00:00:00`);

            if (end < start) {
                valid = false;
                if (showError) {
                    setFieldError(editEndDate, editEndDateError, 'La fecha de fin debe ser posterior a la fecha de inicio.');
                    editStartDate.classList.add('is-invalid');
                }
            }
        }

        if (!editPeriodType?.value) {
            valid = false;
        }

        if (!editRateMode?.value) {
            valid = false;
        }

        if (!editStartTime?.value || !editEndTime?.value) {
            valid = false;
            if (showError) {
                editStartTime?.classList.add('is-invalid');
                editEndTime?.classList.add('is-invalid');
                if (editTimeError) editTimeError.textContent = 'Selecciona hora de inicio y hora de fin.';
            }
        } else if (timeToMinutes(editEndTime.value) <= timeToMinutes(editStartTime.value)) {
            valid = false;
            if (showError) {
                editStartTime?.classList.add('is-invalid');
                editEndTime?.classList.add('is-invalid');
                if (editTimeError) editTimeError.textContent = 'La hora de fin debe ser mayor que la hora de inicio.';
            }
        }

        if (!editingIsCustomDay && !services.length) {
            valid = false;
            if (showError) toggleEditServicesError(true);
        }

        if (!valid) return null;

        return {
            event_id: editEventId.value,
            classroom: editClassroom.value,
            responsible,
            description,
            services,
            period_type: editPeriodType.value,
            rate_mode: editRateMode.value,
            event_date: editStartDate.value,
            event_end_date: editEndDate.value,
            start_time: editStartTime.value,
            end_time: editEndTime.value,
            scope: 'whole_event'
        };
    }

    function updateEditSaveState() {
        if (!saveEditEventBtn) return;
        saveEditEventBtn.disabled = !validateEditEventForm(false);
    }

    function validateRelatedEventForm(showError = true) {
        let valid = true;

        const services = [];
        if (relatedUtilities?.checked) services.push('utilities');
        if (relatedElectricity?.checked) services.push('electricity');
        if (relatedWater?.checked) services.push('water');

        const responsible = relatedResponsible?.value.trim() || '';
        const description = relatedDescription?.value.trim() || '';
        const responsibleRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;
        const descriptionRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;

        if (showError) {
            clearFieldError(relatedArea, relatedAreaError);
            clearFieldError(relatedResponsible, relatedResponsibleError);
            clearFieldError(relatedDescription, relatedDescriptionError);
            clearFieldError(relatedStartDate, relatedStartDateError);
            clearFieldError(relatedEndDate, relatedEndDateError);

            relatedStartTime?.classList.remove('is-invalid');
            relatedEndTime?.classList.remove('is-invalid');

            if (relatedTimeError) relatedTimeError.textContent = '';
            toggleRelatedServicesError(false);
        }

        if (!relatedParentEventId?.value) {
            valid = false;
        }

        if (!relatedArea?.value) {
            valid = false;
            if (showError) setFieldError(relatedArea, relatedAreaError, 'El área es requerida.');
        }

        if (!responsible) {
            valid = false;
            if (showError) setFieldError(relatedResponsible, relatedResponsibleError, 'El responsable es obligatorio.');
        } else if (!responsibleRegex.test(responsible)) {
            valid = false;
            if (showError) setFieldError(relatedResponsible, relatedResponsibleError, 'Solo se permiten letras y espacios.');
        } else if (responsible.length < 8) {
            valid = false;
            if (showError) setFieldError(relatedResponsible, relatedResponsibleError, 'El responsable debe tener al menos 8 caracteres.');
        } else if (responsible.length > 40) {
            valid = false;
            if (showError) setFieldError(relatedResponsible, relatedResponsibleError, 'El responsable no puede exceder 40 caracteres.');
        }

        if (!description) {
            valid = false;
            if (showError) setFieldError(relatedDescription, relatedDescriptionError, 'La descripción es obligatoria.');
        } else if (!descriptionRegex.test(description)) {
            valid = false;
            if (showError) setFieldError(relatedDescription, relatedDescriptionError, 'Solo se permiten letras, números, espacios, punto, coma y guion.');
        } else if (description.length < 10) {
            valid = false;
            if (showError) setFieldError(relatedDescription, relatedDescriptionError, 'La descripción debe tener al menos 10 caracteres.');
        } else if (description.length > 250) {
            valid = false;
            if (showError) setFieldError(relatedDescription, relatedDescriptionError, 'La descripción no puede exceder 250 caracteres.');
        }

        if (!relatedPeriodType?.value) {
            valid = false;
        }

        if (!relatedRateMode?.value) {
            valid = false;
        }

        if (!relatedStartDate?.value) {
            valid = false;
            if (showError) setFieldError(relatedStartDate, relatedStartDateError, 'La fecha inicial es requerida.');
        }

        if (!relatedEndDate?.value) {
            valid = false;
            if (showError) setFieldError(relatedEndDate, relatedEndDateError, 'La fecha final es requerida.');
        }

        if (relatedStartDate?.value && relatedEndDate?.value) {
            const startDate = new Date(`${relatedStartDate.value}T00:00:00`);
            const endDate = new Date(`${relatedEndDate.value}T00:00:00`);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (startDate < today) {
                valid = false;
                if (showError) setFieldError(relatedStartDate, relatedStartDateError, 'No puedes seleccionar una fecha anterior a hoy.');
            }

            if (endDate < today) {
                valid = false;
                if (showError) setFieldError(relatedEndDate, relatedEndDateError, 'No puedes seleccionar una fecha anterior a hoy.');
            }

            if (endDate < startDate) {
                valid = false;
                if (showError) {
                    setFieldError(relatedEndDate, relatedEndDateError, 'La fecha de fin debe ser posterior a la fecha de inicio.');
                    relatedStartDate.classList.add('is-invalid');
                    relatedEndDate.classList.add('is-invalid');
                }
            }
        }

        if (!relatedStartTime?.value || !relatedEndTime?.value) {
            valid = false;
            if (showError) {
                relatedStartTime?.classList.add('is-invalid');
                relatedEndTime?.classList.add('is-invalid');
                if (relatedTimeError) relatedTimeError.textContent = 'Selecciona hora de inicio y hora de fin.';
            }
        } else {
            const startMinutes = timeToMinutes(relatedStartTime.value);
            const endMinutes = timeToMinutes(relatedEndTime.value);
            const selectedDay = getSelectedRelatedStartDateDay();

            if (endMinutes <= startMinutes) {
                valid = false;
                if (showError) {
                    relatedStartTime.classList.add('is-invalid');
                    relatedEndTime.classList.add('is-invalid');
                    if (relatedTimeError) relatedTimeError.textContent = 'La hora de fin debe ser mayor que la hora de inicio.';
                }
            } else if (isRelatedWorkdayPeriod() && (startMinutes < 450 || endMinutes > 990)) {
                valid = false;
                if (showError) {
                    relatedStartTime.classList.add('is-invalid');
                    relatedEndTime.classList.add('is-invalid');
                    if (relatedTimeError) relatedTimeError.textContent = 'Para el período laborable solo se permiten horarios de 7:30 a.m. a 4:30 p.m.';
                }
            } else if (isRelatedNonWorkdayPeriod()) {
                let minMinutes = 990;
                let maxMinutes = 1290;
                let message = 'Para días lunes a viernes en período no laborable solo se permiten horarios de 4:30 p.m. a 9:30 p.m.';

                if (relatedPeriodType.value === 'non_workday_saturday' && selectedDay === 6) {
                    minMinutes = 480;
                    message = 'Para sábado solo se permiten horarios de 8:00 a.m. a 9:30 p.m.';
                }

                if (relatedPeriodType.value === 'non_workday_sunday_holiday' && selectedDay === 0) {
                    minMinutes = 480;
                    message = 'Para domingo o festivo solo se permiten horarios de 8:00 a.m. a 9:30 p.m.';
                }

                if (startMinutes < minMinutes || endMinutes > maxMinutes) {
                    valid = false;
                    if (showError) {
                        relatedStartTime.classList.add('is-invalid');
                        relatedEndTime.classList.add('is-invalid');
                        if (relatedTimeError) relatedTimeError.textContent = message;
                    }
                }
            }
        }

        if (services.length === 0) {
            valid = false;
            if (showError) toggleRelatedServicesError(true);
        }

        if (!valid) return null;

        return {
            parent_event_id: relatedParentEventId.value,
            classroom: relatedArea.value,
            responsible,
            description,
            services,
            period_type: relatedPeriodType.value,
            rate_mode: relatedRateMode.value,
            event_date: relatedStartDate.value,
            end_date: relatedEndDate.value,
            start_time: relatedStartTime.value,
            end_time: relatedEndTime.value,
            relation_type: 'same_event_group'
        };
    }

    function updateRelatedSaveState() {
        if (!saveRelatedEventBtn) return;
        saveRelatedEventBtn.disabled = !validateRelatedEventForm(false);
    }
    if (saveCustomizeDaysBtn) {
        saveCustomizeDaysBtn.addEventListener('click', async () => {
            const payload = validateCustomizeDaysForm(true);

            if (!payload) {
                updateCustomizeSaveState();
                return;
            }

            const body = {
                scope: payload.scope,
                date: payload.date,
                start_time: payload.start_time,
                end_time: payload.end_time,
            };

            const isEntireEvent = payload.scope === 'entire_event';

            const url = isEntireEvent
                ? `/facility/events/${payload.event_id}/schedule`
                : `/facility/events/${payload.event_id}/customize-days`;

            const method = isEntireEvent ? 'PUT' : 'POST';

            try {
                await sendJson(url, method, body);

                bootstrap.Modal.getOrCreateInstance($('customizeDaysModal')).hide();
                toasts.customizeSaved?.show();

                setTimeout(() => {
                    window.location.reload();
                }, 900);
            } catch (error) {
                alert(error.message);
            }
        });
    }

    if (saveEditEventBtn) {
        saveEditEventBtn.addEventListener('click', async () => {
            const payload = validateEditEventForm(true);

            if (!payload) {
                updateEditSaveState();
                return;
            }

            if (!editingIsCustomDay) {
                const affectedCustomDays = getCustomDaysOutsideEditedParentRange(
                    payload.event_id,
                    payload.event_date,
                    payload.event_end_date
                );

                if (affectedCustomDays.length > 0) {
                    pendingEditPayload = payload;
                    showParentRangeWarning(affectedCustomDays.length);
                    return;
                }
            }

            await submitEditEvent(payload, false);
        });
    }

    if (saveRelatedEventBtn) {
        saveRelatedEventBtn.addEventListener('click', async () => {
            const payload = validateRelatedEventForm(true);

            if (!payload) {
                updateRelatedSaveState();
                return;
            }

            const body = {
                classroom: payload.classroom,
                responsible: payload.responsible,
                description: payload.description,
                services: payload.services,
                period_type: payload.period_type,
                rate_mode: payload.rate_mode,
                event_date: payload.event_date,
                event_end_date: payload.end_date,
                start_time: payload.start_time,
                end_time: payload.end_time,
            };

            const url = relatedModalMode === 'edit'
                ? `/facility/events/${editingSubEventId}/sub-event`
                : `/facility/events/${payload.parent_event_id}/related`;

            const method = relatedModalMode === 'edit' ? 'PUT' : 'POST';

            try {
                await sendJson(url, method, body);

                bootstrap.Modal.getOrCreateInstance($('createRelatedModal')).hide();
                toasts.relatedSaved?.show();

                setTimeout(() => {
                    window.location.reload();
                }, 900);
            } catch (error) {
                alert(error.message);
            }
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

        updateAutomaticRateMode();
        updateRentalTimeOptions();
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

    function resetEditEventState() {
        if (editResponsible) editResponsible.value = '';
        if (editDescription) editDescription.value = '';

        clearFieldError(editResponsible, editResponsibleError);
        clearFieldError(editDescription, editDescriptionError);

        updateEditSaveState();
    }

    function resetCustomizeDaysState() {
        if (customizeScope) customizeScope.value = '';
        if (customizeDate) customizeDate.value = '';
        if (customizeStartTime) customizeStartTime.value = '';
        if (customizeEndTime) customizeEndTime.value = '';

        clearFieldError(customizeScope, customizeScopeError);
        clearFieldError(customizeDate, customizeDateError);

        customizeStartTime?.classList.remove('is-invalid');
        customizeEndTime?.classList.remove('is-invalid');
        if (customizeTimeError) customizeTimeError.textContent = '';

        updateCustomizeSaveState();
    }

    function resetRelatedEventState() {
        relatedModalMode = 'create';
        editingSubEventId = null;
        setRelatedModalTitle('Crear Evento Relacionado');
        setRelatedSaveButtonText('Guardar Evento Relacionado');

        setRelatedModalNotice(
            'Esta opción permite agregar otra área relacionada al mismo evento principal.'
        );

        if (relatedArea) relatedArea.value = '';
        if (relatedResponsible) relatedResponsible.value = '';
        if (relatedDescription) relatedDescription.value = '';

        if (relatedUtilities) relatedUtilities.checked = false;
        if (relatedElectricity) relatedElectricity.checked = false;
        if (relatedWater) relatedWater.checked = false;

        if (relatedPeriodType) relatedPeriodType.value = '';
        if (relatedRateMode) relatedRateMode.value = '';
        if (relatedRateModeDisplay) relatedRateModeDisplay.value = 'Se calculará automáticamente';

        if (relatedStartDate) relatedStartDate.value = '';
        if (relatedEndDate) relatedEndDate.value = '';

        if (relatedStartTime) {
            relatedStartTime.innerHTML = '<option value="" selected disabled>Primero selecciona el tipo de período</option>';
        }

        if (relatedEndTime) {
            relatedEndTime.innerHTML = '<option value="" selected disabled>Primero selecciona el tipo de período</option>';
        }

        if (relatedEstimatedTotal) relatedEstimatedTotal.textContent = '$0.00';
        if (relatedDetectedPeriodLabel) relatedDetectedPeriodLabel.textContent = '—';
        if (relatedDetectedHoursLabel) relatedDetectedHoursLabel.textContent = '0.00 horas';

        clearFieldError(relatedArea, relatedAreaError);
        clearFieldError(relatedResponsible, relatedResponsibleError);
        clearFieldError(relatedDescription, relatedDescriptionError);
        clearFieldError(relatedStartDate, relatedStartDateError);
        clearFieldError(relatedEndDate, relatedEndDateError);

        relatedStartTime?.classList.remove('is-invalid');
        relatedEndTime?.classList.remove('is-invalid');
        if (relatedTimeError) relatedTimeError.textContent = '';

        toggleRelatedServicesError(false);
        updateRelatedSaveState();
    }

    $('editEventModal')?.addEventListener('hidden.bs.modal', resetEditEventState);
    $('customizeDaysModal')?.addEventListener('hidden.bs.modal', resetCustomizeDaysState);
    $('createRelatedModal')?.addEventListener('hidden.bs.modal', resetRelatedEventState);
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

    [rentalClassroom,rentalStartTime, rentalEndTime, rentalPeriodType].forEach((el) => {
        if (!el) return;

        el.addEventListener('change', () => {
            rentalDirty = true;


            if (el === rentalPeriodType) {
                rentalStartTime.value = '';
                rentalEndTime.value = '';
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

    attachResponsibleBehavior(
        relatedResponsible,
        relatedResponsibleError,
        (showError) => validateResponsible(showError, relatedResponsible, relatedResponsibleError),
        updateRelatedSaveState
    );

    attachDescriptionBehavior(
        relatedDescription,
        relatedDescriptionError,
        (showError) => validateDescription(showError, relatedDescription, relatedDescriptionError),
        updateRelatedSaveState
    );

    editClassroom?.addEventListener('change', () => {
        clearFieldError(editClassroom, editClassroomError);
        updateEditSummary();
        updateEditSaveState();
    });

    editPeriodType?.addEventListener('change', () => {
        if (editStartTime) editStartTime.value = '';
        if (editEndTime) editEndTime.value = '';

        updateEditTimeOptions();
        updateEditSummary();
        updateEditSaveState();
    });

    [editStartTime, editEndTime].forEach(input => {
        input?.addEventListener('change', () => {
            editStartTime?.classList.remove('is-invalid');
            editEndTime?.classList.remove('is-invalid');
            if (editTimeError) editTimeError.textContent = '';

            if (!editStartTime?.value || !editEndTime?.value) {
                editStartTime?.classList.add('is-invalid');
                editEndTime?.classList.add('is-invalid');
                if (editTimeError) editTimeError.textContent = 'Selecciona hora de inicio y hora de fin.';
            } else if (timeToMinutes(editEndTime.value) <= timeToMinutes(editStartTime.value)) {
                editStartTime.classList.add('is-invalid');
                editEndTime.classList.add('is-invalid');
                if (editTimeError) editTimeError.textContent = 'La hora de fin debe ser mayor que la hora de inicio.';
            }

            updateEditSummary();
            updateEditSaveState();
        });
    });

    editServiceChecks.forEach(input => {
        input?.addEventListener('change', () => {
            toggleEditServicesError(!hasEditServices());
            updateEditSummary();
            updateEditSaveState();
        });
    });


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

    saveRentalBtn?.addEventListener('click', () => {
        addRentalForm.classList.add('was-validated');

        updateAutomaticRateMode();
        calculateRentalEstimate();

        const responsibleOk = validateResponsible(true);
        const descriptionOk = validateDescription(true);
        const servicesOk = hasSelectedServices();
        const datesOk = validateRentalDates(true, true);
        const formOk = isRentalFormValid();

        toggleServicesError(!servicesOk);

        if (!(formOk && datesOk && responsibleOk && descriptionOk && servicesOk)) {
            updateRentalSaveState();
            return;
        }

        addRentalForm.submit();
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
            resetRentalFormState();
            servicesTouched = false;
            rentalDirty = false;
            allowRentalModalClose = false;

            toggleServicesError(false);
            updateRentalTimeOptions();
            updateRentalDateRestrictions();
            updateRentalSaveState();

            setTimeout(() => {
                rentalDirty = false;
            }, 0);
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
    bindOptionThreeButtons();
    bindClassroomCheckboxes();
    toggleMonthFilter();
    updateFacilitySearchButtonState();
    resetConfigureFormState();
    resetRentalFormState();
    applyTableFilters();
    updateDiscardButtonState();
    initializeFacilityDatePickers();

    if (sessionStorage.getItem('reopenConfigureRatesModal') === 'true' && configureRatesModal) {
        bootstrap.Modal.getOrCreateInstance(configureRatesModal).show();
        sessionStorage.removeItem('reopenConfigureRatesModal');
    }

    [
        'editEventModal',
        'customizeDaysModal',
        'createRelatedModal',
        'deleteCostEntryModal'
    ].forEach((modalId) => {
        const modal = $(modalId);
        modal?.addEventListener('hidden.bs.modal', clearActionRadios);
    });

});
