/**
 * Bootstrap import
 *
 * Responsible  for modals tooltips and toast configurations
 * and behaviors
 * */
import * as bootstrap from 'bootstrap';

/**
 * Flatpickr imports
 *
 * Responsibilities:
 * - Provides the custom calendar component used by the access logs date filter
 * - Replaces the browser-native date input for consistent UI behavior
 * - Enables Spanish localization support
 * - Loads Flatpickr default styles
 */
import flatpickr from "flatpickr";
import { Spanish } from "flatpickr/dist/l10n/es.js";
import "flatpickr/dist/flatpickr.min.css";

/**
 * Facility operational cost management front-end behavior controller.
 *
 * This file controls the client-side behavior for the Gestión de Costos Operacionales page.
 * Responsible:
 * - restoring scroll position after facility actions and page reloads
 * - filtering facility operational cost records
 * - validating facility search input and filter behavior
 * - rendering local pagination for facility operational cost rows
 * - configuring classrom and lateral area rates
 * - validating classroom creation fields
 * - validating rental dates, times, period types, and selected services
 * - calculating estimated operational rental costs
 * - dynamically restricting schedules based on laborable and non-laborable periods
 * - managing event creation workflows
 * - managing event editing workflows
 * - managing related-area event workflows
 * - managing custom day modification workflows
 * - synchronizing date restrictions with selected period types
 * - rendering automatic tariff/rate mode calculations
 * - opening confirmation modals for administrative actions
 * - deleting operational cost records and classroom areas
 * - displaying toast notifications after successful actions
 * - coordinating frontend communication with backend endpoints
 */



/**
 * Main page initializer.
 *
 * Runs after the DOM (Document Object Model) is ready so all Blade-rendered /cost management
 * elements exist before references, Bootstrap components, date pickers,
 * and event listeners are registered.
 */
document.addEventListener('DOMContentLoaded', () => {


    /**
     * Activates every Bootstrap tooltip used by the page.
     *
     * The Blade view uses data-bs-title for action buttons, table icons,
     * and helper labels that need tooltip explanations.
     */
    const tooltipTriggerList = document.querySelectorAll('[data-bs-title]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));


    /**
     * Restores the user's previous scroll position after page reloads.
     *
     * facilityScrollTarget is used when the page should return to a specific
     * section (table row, event). facilityScroll is used when only the previous general page position
     * needs to be restored.
     */
    const savedScroll = sessionStorage.getItem('facilityScroll');
    const savedScrollTarget = sessionStorage.getItem('facilityScrollTarget');

    if (savedScrollTarget) {
        document.getElementById(savedScrollTarget)?.scrollIntoView({
            block: 'center'
        });

        sessionStorage.removeItem('facilityScrollTarget');
        sessionStorage.removeItem('facilityScroll');

    } else if (savedScroll) {
        window.scrollTo({
            top: parseInt(savedScroll)
        });

        sessionStorage.removeItem('facilityScroll');
    }

    /**
     * Saves the current scroll position before submitting the facility filters.
     *
     * This keeps the user near the same table location after the filtered
     * request reloads the page.
     */
    const filterForm = document.getElementById('facilityCostFilterForm');

    if (filterForm) {
        filterForm.addEventListener('submit', () => {
            sessionStorage.setItem('facilityScroll', window.scrollY);
        });
    }

    /**
     * Saves the current scroll position before clearing filters.
     */
    const clearBtn = document.getElementById('clearFacilityFilters');

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            sessionStorage.setItem('facilityScroll', window.scrollY);
        });
    }

    /**
     * Helper for finding elements by ID.
     *
     * @param {string} id - Element ID without the # symbol.
     * @returns {HTMLElement|null} Matching DOM element.
     */
    const $ = (id) => document.getElementById(id);

    /**
     * Export button references.
     *
     * These buttons are used to export the currently filtered operational cost
     * information as CSV or PDF.
     */
    const downloadCsvBtn = $('downloadCsvBtn');
    const downloadPdfBtn = $('downloadPdfBtn');

    /**
     * Add area modal references.
     *
     * These elements control the creation of new  areas/classrooms/laterals
     * from inside the configure-rates.
     */
    const newClassroomName = $('newClassroomName');
    const newClassroomType = $('newClassroomType');
    const newClassroomNameError = $('newClassroomNameError');
    const newClassroomTypeError = $('newClassroomTypeError');
    const confirmAddClassroomBtn = $('confirmAddClassroomBtn');
    const addClassroomModal = $('addClassroomModal');

    /**
     * Event search references.
     *
     * Used to search visible event cost records by text values such as
     * date, area, time, period type, tariff type, and services.
     */
    const facilitySearch = $('facilitySearch');
    const searchFacilityBtn = $('searchFacilityBtn');

    /**
     * Event table filter references.
     *
     * These filters narrow the table by period type, tariff type, and
     * selected services.
     */
    const filterPeriodType = $('filterPeriodType');
    const filterRateMode = $('filterRateMode');
    const filterServices = $('filterServices');

    /**
     * Variables that specify the maximum amount of facility cost records per
     * pagination page, the current pagination page, and the base period type
     * used when customizing event days.
     */
    const FACILITY_COSTS_PER_PAGE = 10;
    let currentFacilityCostsPage = 1;
    let customizeBasePeriodType = '';

    /**
     * Facility cost pagination container.
     */
    const facilityCostPagination = $('facilityCostPagination');
    const facilityCostPaginationSummary = $('facilityCostPaginationSummary');

    /**
     * Report filter references.
     *
     * These elements control monthly and annual report filtering.
     */
    const reportType = $('reportType');
    const monthFilterWrapper = $('monthFilterWrapper');
    const yearFilterWrapper = $('yearFilterWrapper');
    const reportMonth = $('reportMonth');
    const reportYear = $('reportYear');
    const filterClassroom = $('filterClassroom');
    const clearFacilityFilters = $('clearFacilityFilters');

    /**
     * Add classroom modal close and cancel button references.
     */
    const cancelAddClassroomBtn = $('cancelAddClassroomBtn');
    const closeAddClassroomBtn = $('closeAddClassroomBtn');

    /**
     * Event cost estimate table references.
     *
     * These elements control the table body, empty state, and displayed
     * estimated total.
     */
    const facilityCostTable = $('facilityCostTable');
    const facilityCostTableBody = $('facilityCostTableBody');
    const facilityCostEmptyState = $('facilityCostEmptyState');
    const facilityCostGrandTotal = $('facilityCostGrandTotal');

    /**
     * Modal references for configuring rates and adding rental events.
     */
    const configureRatesModal = $('configureRatesModal');
    const addRentalModal = $('addRentalModal');

    /**
     * Main form references for configuring rates and adding rental events.
     */
    const configureRatesForm = $('configureRatesForm');
    const addRentalForm = $('addRentalForm');

    /**
     *  Save button references.
     */
    const saveRatesBtn = $('saveRatesBtn');
    const saveRentalBtn = $('saveRentalBtn');

    /**
     * Configure-rates modal classroom references.
     *
     * Dynamically retrieves all selectable
     * classroom configuration checkboxes currently rendered.
     */
    const getConfigClassroomChecks = () => [...document.querySelectorAll('.config-classroom-check')];

    const configClassroomSelectionError = $('configClassroomSelectionError');
    let configClassroomSelectionTouched = false;

    /**
     * These inputs automatically format and validate operational rate values.
     */
    const moneyInputs = [...document.querySelectorAll('.money-input')];

    /**
     * Configure-rates deletion workflow.
     */
    const openDiscardSelectedClassroomsBtn = $('openDiscardSelectedClassroomsBtn');
    const deleteClassroomNameText = $('deleteClassroomNameText');
    const confirmDeleteClassroomBtn = $('confirmDeleteClassroomBtn');

    /**
     * Configure-rates modal pricing field references.
     *
     * These inputs store:
     * - classroom square footage
     * - utility, electricity, and water service pricing
     * - daily pricing
     * - weekly pricing
     * - monthly pricing
     *for each operational period type.
     */
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

    /**
     * Configure-rates modal estimated preview.
     *
     * These labels display calculated operational costs for:
     * - laborable periods
     * - non-laborable Saturday periods
     * - non-laborable Sunday/holiday periods
     */
    const configWorkdayPreview = $('configWorkdayPreview');
    const configSaturdayPreview = $('configSaturdayPreview');
    const configSundayHolidayPreview = $('configSundayHolidayPreview');

    /**
     * These elements control operational rental event creation.
     */
    const rentalClassroom = $('rentalClassroom');
    const rentalResponsible = $('rentalResponsible');
    const rentalStartTime = $('rentalStartTime');
    const rentalEndTime = $('rentalEndTime');
    const rentalDescription = $('rentalDescription');
    const rentalPeriodType = $('rentalPeriodType');

    /**
     * Calculates and displays the tariff rate modes.
     */
    const rentalRangeType = $('rentalRangeType');
    const rentalRateModeDisplay = $('rentalRateModeDisplay');

    /**
     * Date Selection for adding events and warnings.
     */
    const rentalStartDate = $('rentalStartDate');
    const rentalEndDate = $('rentalEndDate');

    const rentalEndDateRow = $('rentalEndDateRow');
    const rentalRangeWarningRow = $('rentalRangeWarningRow');
    const rentalStartDateLabel = $('rentalStartDateLabel');

    /**
     * Add-event service checkbox.
     *
     * Services that affect the estimated operational cost calculation.
     */
    const rentalUtilities = $('rentalUtilities');
    const rentalElectricity = $('rentalElectricity');
    const rentalWater = $('rentalWater');

    /**
     * Collection containing all selectable add-event services.
     */
    const rentalServiceChecks = [rentalUtilities, rentalElectricity, rentalWater];

    rentalServiceChecks.forEach(input => {
        input?.addEventListener('change', () => {
            servicesTouched = true;
            calculateRentalEstimate();
            updateRentalSaveState();
        });
    });

    /**
     * Add-event estimated total and validation.
     *
     * Responsible for:
     * - displaying calculated operational costs
     * - displaying detected tariff/period information
     * - displaying field validation errors
     */
    const rentalEstimatedTotal = $('rentalEstimatedTotal');
    const rentalEstimatedTotalInput = $('rentalEstimatedTotalInput');
    const detectedPeriodLabel = $('detectedPeriodLabel');
    const detectedHoursLabel = $('detectedHoursLabel');
    const servicesRequiredMessage = $('servicesRequiredMessage');
    const rentalResponsibleError = $('rentalResponsibleError');
    const rentalDescriptionError = $('rentalDescriptionError');
    const rentalClassroomError = $('rentalClassroomError');
    const rentalPeriodTypeError = $('rentalPeriodTypeError');
    const rentalStartTimeError = $('rentalStartTimeError');
    const rentalEndTimeError = $('rentalEndTimeError');

    /**
     * Handles deleting table row entires, customizing event dates,
     * creating related events and editing sub-events.
     */
    const confirmDeleteCostEntryBtn = $('confirmDeleteCostEntryBtn');

    /**
     * Dynamically retrieves all delete-row action buttons.
     *
     * @returns {HTMLElement[]} Array containing rendered delete buttons.
     */
    const deleteButtons = () => [...document.querySelectorAll('.delete-cost-row-btn')];

    /**
     * Dynamically retrieves all customize-days action buttons.
     *
     * @returns {HTMLElement[]} Array containing rendered customize-days buttons.
     */
    const customizeDaysButtons = () => [...document.querySelectorAll('.customize-days-btn')];

    /**
     * Customize-days time validation references.
     */
    const customizeStartTimeError = $('customizeStartTimeError');
    const customizeEndTimeError = $('customizeEndTimeError');

    /**
     * Dynamically retrieves all related-event action buttons.
     *
     * @returns {HTMLElement[]} Array containing rendered related-event buttons.
     */
    const createRelatedButtons = () => [...document.querySelectorAll('.create-related-btn')];

    /**
     * Dynamically retrieves all edit-sub-event action buttons.
     *
     * @returns {HTMLElement[]} Array containing rendered edit-sub-event buttons.
     */
    const editSubEventButtons = () => [...document.querySelectorAll('.edit-sub-event-btn')];


    /**
     * Edit-event modal period/time validation references.
     */
    const editPeriodTypeError = $('editPeriodTypeError');
    const editStartTimeError = $('editStartTimeError');
    const editEndTimeError = $('editEndTimeError');

    /**
     * Related-event modal state variables.
     *
     * Determines whether the modal is:
     * - creating a related event
     * - editing an existing related/custom event
     * Stores the sub-event currently being edited.
     */
    let relatedModalMode = 'create';
    let editingSubEventId = null;

    /**
     * Customize-days modal references.
     */
    const customizeEventId = $('customizeEventId');
    const saveCustomizeDaysBtn = $('saveCustomizeDaysBtn');

    /**
     * Edit-event modal references.
     */
    const editEventId = $('editEventId');
    const saveEditEventBtn = $('saveEditEventBtn');

    /**
     * Related-event modal items refrences.
     *
     * Responsible for:
     * - creating related operational events
     * - editing related/custom sub-events
     * - validating related-event fields
     * - calculating related-event estimated costs
     */
    const relatedPeriodTypeError = $('relatedPeriodTypeError');
    const relatedParentEventId = $('relatedParentEventId');
    const saveRelatedEventBtn = $('saveRelatedEventBtn');
    const relatedArea = $('relatedArea');
    const relatedResponsible = $('relatedResponsible');
    const relatedDescription = $('relatedDescription');
    const relatedUtilities = $('relatedUtilities');
    const relatedElectricity = $('relatedElectricity');
    const relatedWater = $('relatedWater');

    /**
     * Collection containing all selectable related-event services.
     */
    const relatedServiceChecks = [relatedUtilities, relatedElectricity, relatedWater];
    const relatedPeriodType = $('relatedPeriodType');

    /**
     * Related-event automatic tariff/rate mode references.
     */
    const relatedRateMode = $('relatedRateMode');
    const relatedRateModeDisplay = $('relatedRateModeDisplay');

    /**
     * Related-event date references.
     */
    const relatedStartDate = $('relatedStartDate');
    const relatedEndDate = $('relatedEndDate');

    /**
     * Related-event time references.
     */
    const relatedStartTime = $('relatedStartTime');
    const relatedEndTime = $('relatedEndTime');

    /**
     * Related-event estimated total and validation references.
     */
    const relatedEstimatedTotal = $('relatedEstimatedTotal');
    const relatedDetectedPeriodLabel = $('relatedDetectedPeriodLabel');
    const relatedDetectedHoursLabel = $('relatedDetectedHoursLabel');
    const relatedAreaError = $('relatedAreaError');
    const relatedResponsibleError = $('relatedResponsibleError');
    const relatedDescriptionError = $('relatedDescriptionError');
    const relatedStartDateError = $('relatedStartDateError');
    const relatedEndDateError = $('relatedEndDateError');
    const relatedTimeError = $('relatedTimeError');
    const relatedStartTimeError = $('relatedStartTimeError');
    const relatedEndTimeError = $('relatedEndTimeError');

    /**
     * Edit-event modal field item references.
     *
     * Responsible for:
     * - editing principle operational events
     * - editing sub-events
     * - recalculating operational costs
     * - validating edited schedules and services
     */
    const editResponsible = $('editResponsible');
    const editDescription = $('editDescription');
    const editClassroom = $('editClassroom');
    const editStartDate = $('editStartDate');
    const editEndDate = $('editEndDate');
    const editStartTime = $('editStartTime');
    const editEndTime = $('editEndTime');
    const editPeriodType = $('editPeriodType');

    /**
     * Edit-event automatic tariff/rate mode references.
     */
    const editRateMode = $('editRateMode');
    const editRateModeDisplay = $('editRateModeDisplay');

    /**
     * Edit-event service checkbox references.
     */
    const editUtilities = $('editUtilities');
    const editElectricity = $('editElectricity');
    const editWater = $('editWater');

    /**
     * Collection containing all selectable edit-event services.
     */
    const editServiceChecks = [editUtilities, editElectricity, editWater];

    /**
     * Edit-event estimated total references.
     *
     * Responsible for displaying the calculated operational totals,
     * detected tariff type, and detected event hours.
     */
    const editEstimatedTotal = $('editEstimatedTotal');
    const editDetectedPeriodLabel = $('editDetectedPeriodLabel');
    const editDetectedHoursLabel = $('editDetectedHoursLabel');

    /**
     * Edit-event validation references.
     */
    const editClassroomError = $('editClassroomError');
    const editStartDateError = $('editStartDateError');
    const editEndDateError = $('editEndDateError');
    const editTimeError = $('editTimeError');
    const editServicesError = $('editServicesError');
    const editResponsibleError = $('editResponsibleError');
    const editDescriptionError = $('editDescriptionError');

    /**
     * Customize-days modal references.
     *
     * Responsible for:
     * - selecting modification scope
     * - selecting custom dates
     * - overriding operational schedules
     * - overriding operational period types
     */
    const customizeScope = $('customizeScope');
    const customizeDate = $('customizeDate');
    const customizePeriodType = $('customizePeriodType');
    const customizePeriodTypeError = $('customizePeriodTypeError');
    const customizeStartTime = $('customizeStartTime');
    const customizeEndTime = $('customizeEndTime');
    const customizeScopeError = $('customizeScopeError');
    const customizeDateError = $('customizeDateError');
    const customizeTimeError = $('customizeTimeError');


    /**
     * Calendar icon button references.
     * These buttons manually open their corresponding Flatpickr calendars.
     */
    const rentalStartDateIcon = $('rentalStartDateIcon');
    const rentalEndDateIcon = $('rentalEndDateIcon');
    const relatedStartDateIcon = $('relatedStartDateIcon');
    const relatedEndDateIcon = $('relatedEndDateIcon');
    const customizeDateIcon = $('customizeDateIcon');
    const editStartDateIcon = $('editStartDateIcon');
    const editEndDateIcon = $('editEndDateIcon');


    /**
     * Creates or retrieves a Bootstrap toast instance.
     *
     * Toasts are used to display successful actions such as:
     * - saving rates
     * - creating events
     * - editing events
     * - deleting entries
     * - customizing days
     *
     * @param {string} id - Toast element ID.
     * @returns {bootstrap.Toast|null} Toast instance or null when missing.
     */
    const createToastInstance = (id) => {
        const el = $(id);
        return el ? bootstrap.Toast.getOrCreateInstance(el, {delay: 2500}) : null;
    };

    /**
     * Toast instances grouped by operational action.
     */
    const toasts = {
        ratesSaved: createToastInstance('ratesSavedToast'),
        rentalSaved: createToastInstance('rentalSavedToast'),
        deleteEntry: createToastInstance('deleteEntryToast'),
        download: createToastInstance('downloadToast'),
        customizeSaved: createToastInstance('customizeSavedToast'),
        editSaved: createToastInstance('editSavedToast'),
        relatedSaved: createToastInstance('relatedSavedToast'),
        classroomAdded: createToastInstance('classroomAddedToast'),
        classroomsDeleted: createToastInstance('classroomsDeletedToast'),
    };


    /**
     * Laravel CSRF token used for authenticated fetch requests.
     */
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    /**
     * Sends a JSON request to the backend.
     *
     * It attaches the CSRF token, sets JSON request headers,
     * parses JSON responses, and throws frontend errors for failed responses.
     *
     * @param {string} url - Backend endpoint URL.
     * @param {string} method - HTTP request method.
     * @param {Object} payload - JSON payload/data sent to the backend.
     * @returns {Promise<Object>} Parsed backend response.
     */
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

    /**
     * Enables or disables the search button.
     *
     * The button stays disabled until the search input contains text.
     */
    function updateFacilitySearchButtonState() {
        if (!facilitySearch || !searchFacilityBtn) return;

        const value = facilitySearch.value;
        searchFacilityBtn.disabled = value.trim() === '';
    }

    /**
     * Stores the delete URL selected from an operational cost row.
     */
    let selectedDeleteUrl = null;

    /**
     * Stores the selected classrooms queued for deletion.
     */
    let selectedClassroomsToDelete = [];

    /**
     * Dirty-state variables.
     *
     * These variables track whether modal forms contain unsaved changes.
     */
    let configureDirty = false;
    let rentalDirty = false;
    let editDirty = false;
    let relatedDirty = false;
    let customizeDirty = false;

    /**
     * Modal close-permission variables.
     *
     * These variables prevent accidental modal closure while unsaved
     * changes still exist.
     */
    let allowEditModalClose = false;
    let allowRelatedModalClose = false;
    let allowCustomizeModalClose = false;

    /**
     * Tracks whether rental services were manually touched by the user.
     */
    let servicesTouched = false;
    let allowConfigureModalClose = false;
    let allowRentalModalClose = false;


    /**
     * Global rate configuration injected from Blade.
     *
     * Contains all configured operational pricing data
     * grouped by area name.
     */
    const facilityConfig = window.facilityManagementConfig || {};
    const ratesByClassroom = facilityConfig.ratesByClassroom || {};


    /**
     *  Saves the current vertical scrolling position.
     */
    function saveFacilityScroll() {
        sessionStorage.setItem('facilityScroll', String(window.scrollY));
    }


    /**
     * Clears all configure-rates modal inputs.
     *
     * Also refreshes the operational cost preview totals after reset.
     */
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

    /**
     * Keeps the add-classroom modal connected to the configure-rates modal.
     */
    if (addClassroomModal && configureRatesModal) {
        let shouldReturnToConfigureRates = false;


        /**
         * When this button is used, the configure-rates modal should reopen
         * after the add-classroom modal closes.
         */
        const openAddClassroomModalBtn = $('openAddClassroomModalBtn');


        /**
         * When this button is used, the configure-rates modal should reopen
         * after the add-classroom modal closes.
         */
        if (openAddClassroomModalBtn) {
            openAddClassroomModalBtn.addEventListener('click', () => {
                shouldReturnToConfigureRates = true;
            });
        }

        /**
         * User returns to configure-rates after
         * canceling the add-classroom modal.
         */
        if (cancelAddClassroomBtn) {
            cancelAddClassroomBtn.addEventListener('click', () => {
                shouldReturnToConfigureRates = true;
            });

        }

        /**
         * User returns to configure-rates after
         * closing the add-classroom modal with the close button.
         */
        if (closeAddClassroomBtn) {
            closeAddClassroomBtn.addEventListener('click', () => {
                shouldReturnToConfigureRates = true;
            });
        }

        /**
         * Resets the add-classroom modal after it closes.
         *
         * Clears the name input, removes validation errors, disables the
         * confirmation button, and reopens configure-rates when the user came
         * from that workflow.
         */
        addClassroomModal.addEventListener('hidden.bs.modal', () => {
            newClassroomName.value = '';
            newClassroomName.classList.remove('is-invalid');
            newClassroomNameError.textContent = '';

            newClassroomType.value = '';
            newClassroomType.classList.remove('is-invalid');
            newClassroomTypeError.textContent = '';

            confirmAddClassroomBtn.disabled = true;

            if (shouldReturnToConfigureRates) {
                bootstrap.Modal.getOrCreateInstance(configureRatesModal).show();
                shouldReturnToConfigureRates = false;
            }
        });
    }


    /**
     * Validates the new area name before submission.
     *
     * Validation rules:
     * - value is required
     * - only letters, Spanish accents, numbers, spaces, comma, period, and hyphen are allowed
     * - minimum length is 6 characters & maximum length is 40 characters
     * - the area name cannot already exist in the rendered classroom cards
     *
     * @param {boolean} showError - Determines whether validation errors should be displayed.
     * @returns {boolean} True when the classroom name is valid.
     */
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
                    'Solo se permiten letras, números, espacios, puntos, comas y guiones.'
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

    function validateNewClassroomType(showError = true) {
        if (!newClassroomType || !newClassroomTypeError) return true;

        if (showError) {
            clearFieldError(newClassroomType, newClassroomTypeError);
        }

        if (!newClassroomType.value) {
            if (showError) {
                setFieldError(
                    newClassroomType,
                    newClassroomTypeError,
                    'Selecciona un tipo de área.'
                );
            }

            return false;
        }

        return true;
    }

    /**
     * Enables or disables the add-classroom confirmation button depending
     * on the current classroom name validation state.
     */
    function updateAddClassroomButtonState() {
        if (!confirmAddClassroomBtn) return;

        confirmAddClassroomBtn.disabled =
            !validateNewClassroomName(false) ||
            !validateNewClassroomType(false);
    }

    /**
     * Revalidates the classroom name while the user types
     * Also updates the confirmation button state dynamically.
     */
    function getAllRenderedClassrooms() {
        return [...document.querySelectorAll('.classroom-card-col')]
            .map(card => card.dataset.classroomName)
            .filter(Boolean);
        }

    /**
     * Gets only rendered academic areas.
     *
     * Academic classroom areas are identified by names that start with CM.
     *
     * @returns {string[]} Rendered academic classroom names.
     */
        function getAcademicRenderedClassrooms() {
            return getAllRenderedClassrooms().filter(name => /^CM\s?\d+/i.test(name));
        }

    /**
     * Gets only rendered lateral areas.
     *
     * Lateral areas are identified by names that contain the word lateral.
     *
     * @returns {string[]} Rendered lateral area names.
     */
        function getLateralRenderedClassrooms() {
            return getAllRenderedClassrooms().filter(name => /lateral/i.test(name));
        }

    /**
     *
     * While the user types, this block:
     * - enforces the 40 character limit
     * - displays the matching validation message
     * - validates the current name
     * - updates the confirmation button state
     */
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
                    'Has alcanzado el máximo de 40 caracteres. Puedes someter el texto tal como está.'
                );
            } else {
                validateNewClassroomName(true);
            }

            updateAddClassroomButtonState();
        });

        /**
         * Validates the add-area name when the input loses focus.
         *
         * If the value is exactly 40 characters, the warning is cleared because
         * the value can still be submitted.
         */
        newClassroomName.addEventListener('blur', () => {
            const value = newClassroomName.value.trim();

            if (value.length === 40) {
                clearFieldError(newClassroomName, newClassroomNameError);
            } else {
                validateNewClassroomName(true);
            }

            updateAddClassroomButtonState();
        });

        /**
         * Updates the confirmation button when the selected area type changes.
         */
        newClassroomType?.addEventListener('change', () => {
            validateNewClassroomType(true);
            updateAddClassroomButtonState();
        });
    }

        newClassroomType?.addEventListener('blur', () => {
            validateNewClassroomType(true);
            updateAddClassroomButtonState();
        });


    /**
     * Submits the add-area form after the new area name passes validation.
     */
    if (confirmAddClassroomBtn) {
        confirmAddClassroomBtn.addEventListener('click', () => {
            if (!validateNewClassroomName(true) || !validateNewClassroomType(true)) {
                updateAddClassroomButtonState();
                return;
            }

            document.getElementById('hiddenNewClassroomName').value =
                newClassroomName.value.trim();

            document.getElementById('hiddenNewClassroomType').value =
                newClassroomType.value;

            sessionStorage.setItem('reopenConfigureRatesModal', 'true');
            saveFacilityScroll();
            document.getElementById('addClassroomForm').submit();
        });
    }

    /**
     * Confirms all events require an enddate.
     *
     * @returns {boolean} Always true
     */
    function requiresEndDate() {
        return true;
    }

    /**
     * Calculates the number of calendar days between two dates, including
     * both the start date and the end date.
     *
     * If no end date is provided, the start date is used as the end date.
     *
     * @param {string} startValue - Start date in YYYY-MM-DD format.
     * @param {string} endValue - End date in YYYY-MM-DD format.
     * @returns {number} Day count.
     */
    function getInclusiveDays(startValue, endValue) {
        if (!startValue) return 0;

        const start = new Date(`${startValue}T00:00:00`);
        const end = new Date(`${endValue || startValue}T00:00:00`);

        const diffMs = end - start;
        return Math.floor(diffMs / (1000 * 60 * 60 * 24)) + 1;
    }

    /**
     * Selects tariff type based on the amount of inclusive selected days:
     * - less than 7 days uses daily
     * - less than 28 days uses weekly
     * - 28 days or more uses monthly
     *
     * @returns {string} Resolved rate mode value.
     */
    function resolveRateModeByDuration() {
        const days = getInclusiveDays(rentalStartDate.value, rentalEndDate.value);

        if (days <= 0) return '';

        if (days < 7) return 'daily';
        if (days < 28) return 'weekly';

        return 'monthly';
    }

    /**
     * Resolves the automatic tariff mode for a related event.
     *
     * Uses the related-event start and end dates to determine whether
     * the event should be charged daily, weekly, or monthly.
     *
     * @returns {string} Resolved related-event rate mode value.
     */
    function resolveRelatedRateModeByDuration() {
        const days = getInclusiveDays(relatedStartDate.value, relatedEndDate.value);

        if (days <= 0) return '';
        if (days < 7) return 'daily';
        if (days < 28) return 'weekly';

        return 'monthly';
    }

    /**
     * Updates the related-event hidden rate mode and visible rate label.
     */
    function updateRelatedAutomaticRateMode() {
        const mode = resolveRelatedRateModeByDuration();

        relatedRateMode.value = mode;
        relatedRateModeDisplay.value = rateModeLabel(mode);
    }

    /**
     * Converts a rate type value into its Spanish display label.
     *
     * @param {string} value - Rate mode value.
     * @returns {string} Spanish rate mode label.
     */
    function rateModeLabel(value) {
        if (value === 'daily') return 'Diario';
        if (value === 'weekly') return 'Semanal';
        if (value === 'monthly') return 'Mensual';
        return 'Se calculará automáticamente';
    }

    /**
     * Updates the add-event hidden rate mode and visible rate label.
     */
    function updateAutomaticRateMode() {
        const mode = resolveRateModeByDuration();

        if (rentalRangeType) {
            rentalRangeType.value = mode;
        }

        if (rentalRateModeDisplay) {
            rentalRateModeDisplay.value = rateModeLabel(mode);
        }
    }

    /**
     * Toggles add-event date range behavior.
     *
     * Shows the end-date row and warning message when the workflow requires
     * an end date. If end dates are not required, the end-date value and
     * validation error are cleared.
     */
    function toggleRentalDateRangeUI() {
        const showEndDate = requiresEndDate();

        rentalEndDateRow?.classList.toggle('d-none', !showEndDate);
        rentalRangeWarningRow?.classList.toggle('d-none', !showEndDate);

        if (rentalStartDateLabel) {
            rentalStartDateLabel.innerHTML = showEndDate
                ? 'Fecha inicial del evento <span class="text-danger">*</span>'
                : 'Fecha del evento <span class="text-danger">*</span>';
        }

        if (!showEndDate && rentalEndDate) {
            rentalEndDate.value = '';
            clearFieldError(rentalEndDate, $('rentalEndDateError'));
        }
    }

    /**
     * Validates the add-event date range.
     *
     * - start date is required
     * - start date cannot be in the past
     * - start date must match the selected period restrictions
     * - end date is required
     * - end date cannot be in the past
     * - end date must match the selected period restrictions
     * - end date must be the same as or after the start date
     *
     * @param {boolean} showError - Determines whether validation errors should be displayed.
     * @param {boolean} forceRequired - Determines whether missing dates should show required errors.
     * @returns {boolean} True when the rental dates are valid.
     */
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

    /**
     * Formats a numeric value as USD currency.
     *
     * @param {number|string} value - Numeric value to format.
     * @returns {string} Formatted currency value.
     */
    const formatMoney = (value) => Number(value).toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    /**
     * Converts formatted money text into a numeric value.
     *
     * @param {string|number} text - Money text or number to parse.
     * @returns {number} Parsed numeric value.
     */
    const parseMoney = (text) => Number(String(text).replace(/[^0-9.]/g, '')) || 0;

    /**
     * Converts formatted numeric text into a number.
     *
     * @param {string|number} value - Text or number to parse.
     * @returns {number} Parsed numeric value.
     */
    const toNumber = (value) => Number(String(value).replace(/[^0-9.]/g, '')) || 0;

    /**
     *
     * Maximum allowed configures price rate and area size.
     */
    const MAX_RATE_PRICE = 500;
    const MAX_AREA_SQFT = 25000000.00;


    /**
     * Converts a time value into minutes.
     *
     * Used to compare event schedules, validate time ranges,
     * and calculate event duration.
     *
     * @param {string} timeValue - Time value in HH:MM format.
     * @returns {number} Total minutes for the provided time.
     */
    function timeToMinutes(timeValue) {
        if (!timeValue) return 0;
        const [hour, minute] = timeValue.split(':').map(Number);
        return (hour * 60) + minute;
    }

    /**
     * Calculates the amount of hours between two time values.
     *
     * @param {string} start - Start time in HH:MM format.
     * @param {string} end - End time in HH:MM format.
     * @returns {number} Total hours between the start and end time.
     */
    function calculateHours(start, end) {
        const diff = timeToMinutes(end) - timeToMinutes(start);
        return diff > 0 ? diff / 60 : 0;
    }

    /**
     * Validates event start and end times.
     *
     * - Makes sure that both time fields have values ,the end time is after the start time, and
     *   laborable period times stay between 7:30 a.m. and 4:30 p.m. and, non-laborable period times
     *   stay between 8:00 a.m. and 9:30 p.m.
     *
     * Used by add-event, edit-event, related-event, and customize-days workflows.
     *
     * @param {Object} params - Time validation settings.
     * @param {HTMLSelectElement} params.startTime - Start time select element.
     * @param {HTMLSelectElement} params.endTime - End time select element.
     * @param {HTMLSelectElement} params.periodType - Selected period type element.
     * @param {HTMLInputElement} params.startDate - Date input connected to the selected schedule.
     * @param {HTMLElement} params.timeError - General time error message element.
     * @param {boolean} params.showError - Whether validation errors should be displayed.
     * @returns {boolean} True when the selected time range is valid.
     */
    function validateEventTimes({
                startTime,
                endTime,
                periodType,
                startDate,
                timeError,
                showError = true
            }) {
        let valid = true;

        if (showError) {
            startTime?.classList.remove('is-invalid');
            endTime?.classList.remove('is-invalid');

            if (timeError) timeError.textContent = '';
        }

        if (!startTime?.value || !endTime?.value) {
            return false;
        }

        const startMinutes = timeToMinutes(startTime.value);
        const endMinutes = timeToMinutes(endTime.value);

        if (endMinutes <= startMinutes) {
            valid = false;

            if (showError) {
                startTime.classList.add('is-invalid');
                endTime.classList.add('is-invalid');
                if (timeError) timeError.textContent = 'El horario final del evento debe ser mayor que la hora inicial del evento.';
            }

            return false;
        }

        const selectedDay = startDate?.value
            ? new Date(`${startDate.value}T00:00:00`).getDay()
            : null;

        if (periodType?.value === 'workday') {
            if (startMinutes < 450 || endMinutes > 990) {
                valid = false;

                if (showError) {
                    startTime.classList.add('is-invalid');
                    endTime.classList.add('is-invalid');
                    if (timeError) {
                        timeError.textContent = 'Para el período laborable solo se permiten horarios de 7:30 a.m. a 4:30 p.m.';
                    }
                }
            }
        }

        if (['non_workday_saturday', 'non_workday_sunday_holiday'].includes(periodType?.value)) {
            const minMinutes = 480;
            const maxMinutes = 1290;
            const message = 'Para períodos no laborables solo se permiten horarios de 8:00 a.m. a 9:30 p.m.';

            if (startMinutes < minMinutes || endMinutes > maxMinutes) {
                valid = false;

                if (showError) {
                    startTime.classList.add('is-invalid');
                    endTime.classList.add('is-invalid');
                    if (timeError) timeError.textContent = message;
                }
            }
        }

        return valid;
    }

    /**
     * Checks if the add-event selected period is laborable.
     *
     * @returns {boolean} True when the rental period type is laborable.
     */
    function isWorkdayPeriod() {
        return rentalPeriodType?.value === 'workday';
    }

    /**
     * Checks if the add-event selected period is non-laborable.
     *
     * @returns {boolean} True when the rental period type is Saturday or Sunday/holiday non-laborable.
     */
    function isNonWorkdayPeriod() {
        return ['non_workday_saturday', 'non_workday_sunday_holiday'].includes(rentalPeriodType?.value);
    }

    /**
     * Formats a Date object as YYYY-MM-DD.
     *
     * Used when date values need to match the format expected by
     * inputs, Flatpickr, and backend payloads. Used for filtering purposes.
     *
     * @param {Date} date - Date object to format.
     * @returns {string} Date formatted as YYYY-MM-DD.
     */
    function formatDateLocal(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    /**
     * Builds time dropdown options in 15-minute intervals.
     *
     * The generated options use HH:MM values for form submission and
     * 12-hour formatted labels for display.
     *
     * @param {HTMLSelectElement} select - Select element where options are inserted.
     * @param {number} startHour - Starting hour in 24-hour format.
     * @param {number} startMinute - Starting minute.
     * @param {number} endHour - Ending hour in 24-hour format.
     * @param {number} endMinute - Ending minute.
     * @param {string} placeholder - Placeholder option text.
     */
    function buildTimeOptions(select, startHour, startMinute, endHour, endMinute, placeholder) {
        if (!select) return;

        select.innerHTML = `<option value="" selected>${placeholder}</option>`;

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

    /**
     * Checks if the related-event selected period is laborable.
     *
     * @returns {boolean} True when the related-event period type is laborable.
     */
    function isRelatedWorkdayPeriod() {
        return relatedPeriodType?.value === 'workday';
    }

    /**
     * Updates the add-event start and end time options based on the selected period type.
     *
     * Laborable events use the laborable schedule.
     * Non-laborable events use the non-laborable schedule.
     *
     * Previously selected times are restored when they still exist
     * after rebuilding the dropdown options.
     */
    function updateRentalTimeOptions() {
        const previousStart = rentalStartTime.value;
        const previousEnd = rentalEndTime.value;

        if (isWorkdayPeriod()) {
            buildTimeOptions(rentalStartTime, 7, 30, 16, 30, 'Seleccionar el horario inicial del evento');
            buildTimeOptions(rentalEndTime, 7, 45, 16, 30, 'Seleccionar el horario final del evento');
        } else if (['non_workday_saturday', 'non_workday_sunday_holiday'].includes(rentalPeriodType?.value)) {
            buildTimeOptions(rentalStartTime, 8, 0, 21, 30, 'Seleccionar el horario inicial del evento');
            buildTimeOptions(rentalEndTime, 8, 15, 21, 30, 'Seleccionar el horario final del evento');
        } else {
            rentalStartTime.innerHTML = '<option value="" selected>Primero selecciona el tipo de período</option>';
            rentalEndTime.innerHTML = '<option value="" selected>Primero selecciona el tipo de período</option>';
            return;
        }

        if ([...rentalStartTime.options].some(option => option.value === previousStart)) {
            rentalStartTime.value = previousStart;
        }

        if ([...rentalEndTime.options].some(option => option.value === previousEnd)) {
            rentalEndTime.value = previousEnd;
        }
    }

    /**
     * Updates the related-event start and end time options based on the selected period type.
     *
     * Previously selected times are restored when they still exist
     * after rebuilding the dropdown options.
     */
    function updateRelatedTimeOptions() {
        const previousStart = relatedStartTime.value;
        const previousEnd = relatedEndTime.value;

        if (isRelatedWorkdayPeriod()) {
            buildTimeOptions(relatedStartTime, 7, 30, 16, 30, 'Seleccionar el horario inicial del evento');
            buildTimeOptions(relatedEndTime, 7, 45, 16, 30, 'Seleccionar el horario final del evento');
        } else if (['non_workday_saturday', 'non_workday_sunday_holiday'].includes(relatedPeriodType?.value)) {
            buildTimeOptions(relatedStartTime, 8, 0, 21, 30, 'Seleccionar el horario inicial del evento');
            buildTimeOptions(relatedEndTime, 8, 15, 21, 30, 'Seleccionar el horario final del evento');
        } else {
            relatedStartTime.innerHTML = '<option value="" selected>Primero selecciona el tipo de período</option>';
            relatedEndTime.innerHTML = '<option value="" selected>Primero selecciona el tipo de período</option>';
            return;
        }

        if ([...relatedStartTime.options].some(option => option.value === previousStart)) {
            relatedStartTime.value = previousStart;
        }

        if ([...relatedEndTime.options].some(option => option.value === previousEnd)) {
            relatedEndTime.value = previousEnd;
        }
    }


    /**
     * Checks if the edit-event selected period is laborable.
     *
     * @returns {boolean} True when the edit-event period type is laborable.
     */
    function isEditWorkdayPeriod() {
        return editPeriodType?.value === 'workday';
    }

    /**
     * Updates the edit-event start and end time options based on the selected period type.
     *
     * Laborable events use the laborable schedule.
     * Non-laborable events use the non-laborable schedule.
     *
     * Previously selected times are restored when they still exist
     * after rebuilding the dropdown options.
     */
    function updateEditTimeOptions() {
        const previousStart = editStartTime?.value || '';
        const previousEnd = editEndTime?.value || '';

        if (!editStartTime || !editEndTime) return;

        if (isEditWorkdayPeriod()) {
            buildTimeOptions(editStartTime, 7, 30, 16, 30, 'Seleccionar el horario inicial del evento');
            buildTimeOptions(editEndTime, 7, 45, 16, 30, 'Seleccionar el horario final del evento');
        } else if (['non_workday_saturday', 'non_workday_sunday_holiday'].includes(editPeriodType?.value)) {
            buildTimeOptions(editStartTime, 8, 0, 21, 30, 'Seleccionar el horario inicial del evento');
            buildTimeOptions(editEndTime, 8, 15, 21, 30, 'Seleccionar el horario final del evento');
        } else {
            editStartTime.innerHTML = '<option value="" selected>Primero selecciona el tipo de período</option>';
            editEndTime.innerHTML = '<option value="" selected>Primero selecciona el tipo de período</option>';
            return;
        }

        if ([...editStartTime.options].some(option => option.value === previousStart)) {
            editStartTime.value = previousStart;
        }

        if ([...editEndTime.options].some(option => option.value === previousEnd)) {
            editEndTime.value = previousEnd;
        }
    }


    /**
     * Validates that a period type has been selected in the customize-days modal.
     *
     * @param {boolean} showError - Determines whether validation errors should be displayed.
     * @returns {boolean} True when a customize period type is selected.
     */
    function validateCustomizePeriodType(showError = true) {
        if (!customizePeriodType) return true;

        if (showError) {
            clearFieldError(customizePeriodType, customizePeriodTypeError);
        }

        if (!customizePeriodType.value) {
            if (showError) {
                setFieldError(
                    customizePeriodType,
                    customizePeriodTypeError,
                    'Selecciona un tipo de período válido.'
                );
            }

            return false;
        }

        return true;
    }

    /**
     * Updates customize-days start and end time options based on the selected period type.
     *
     * Previously selected times are restored when they still exist
     * after rebuilding the dropdown options.
     */
    function updateCustomizeTimeOptions() {
        const previousStart = customizeStartTime.value;
        const previousEnd = customizeEndTime.value;
        const selectedPeriod = customizePeriodType?.value;

        if (!selectedPeriod) {
            customizeStartTime.innerHTML = '<option value="" selected>Primero selecciona el tipo de período</option>';
            customizeEndTime.innerHTML = '<option value="" selected>Primero selecciona el tipo de período</option>';
            return;
        }

        if (selectedPeriod === 'workday') {
            buildTimeOptions(customizeStartTime, 7, 30, 16, 30, 'Seleccionar horario inicial');
            buildTimeOptions(customizeEndTime, 7, 45, 16, 30, 'Seleccionar horario final');
        }

        if (['non_workday_saturday', 'non_workday_sunday_holiday'].includes(selectedPeriod)) {
            buildTimeOptions(customizeStartTime, 8, 0, 21, 30, 'Seleccionar horario inicial');
            buildTimeOptions(customizeEndTime, 8, 15, 21, 30, 'Seleccionar horario final');
        }

        if ([...customizeStartTime.options].some(option => option.value === previousStart)) {
            customizeStartTime.value = previousStart;
        }

        if ([...customizeEndTime.options].some(option => option.value === previousEnd)) {
            customizeEndTime.value = previousEnd;
        }
    }

    /**
     * Clears customize-days selected times and related validation messages.
     */
    function resetCustomizeTimesAndErrors() {
        customizeStartTime.value = '';
        customizeEndTime.value = '';

        clearFieldError(customizeStartTime, customizeStartTimeError);
        clearFieldError(customizeEndTime, customizeEndTimeError);

        if (customizeTimeError) {
            customizeTimeError.textContent = '';
        }
    }

    /**
     * Updates the related-event modal title.
     *
     * Used when the same modal changes between creating a related event
     * and editing an existing related/custom sub-event.
     *
     * @param {string} text - Title text to display in the modal header.
     */
    function setRelatedModalTitle(text) {
        const title = $('createRelatedModalLabel');
        if (title) title.textContent = text;
    }

    /**
     * Updates the related-event modal notice text.
     *
     * @param {string} text - Notice text shown inside the related-event modal.
     */
    function setRelatedModalNotice(text) {
        const notice = $('relatedModalNoticeText');

        if (notice) {
            notice.textContent = text;
        }
    }

    /**
     * Updates the related-event modal save button text.
     *
     * @param {string} text - Button text to display.
     */
    function setRelatedSaveButtonText(text) {
        if (saveRelatedEventBtn) {
            saveRelatedEventBtn.textContent = text;
        }
    }

    /**
     * Finds the princiapl event row that belongs to the same event group as a given row.
     *
     * @param {HTMLTableRowElement} row - Row used to read the event group key.
     * @returns {HTMLTableRowElement|null} Matching parent event row or null when unavailable.
     */
    function getParentRowForGroup(row) {
        const groupKey = row?.dataset.groupKey;
        if (!groupKey) return null;

        return facilityCostTableBody.querySelector(
            `tr.parent-event-row[data-group-key="${groupKey}"]`
        );
    }

    const classroomAddedAutoTrigger = document.getElementById('classroomAddedAutoTrigger');
    const classroomsDeletedAutoTrigger = document.getElementById('classroomsDeletedAutoTrigger');

    if (classroomAddedAutoTrigger && toasts.classroomAdded) {
        toasts.classroomAdded.show();
    }

    if (classroomsDeletedAutoTrigger && toasts.classroomsDeleted) {
        toasts.classroomsDeleted.show();
    }

    /**
     * Clears and resets the related-event modal date range from the principle event context.
     *
     * After clearing dates, this refreshes the automatic tariff mode,
     * summary, and save button state.
     *
     * @param {HTMLTableRowElement} parentRow - Principal event row connected to the related event.
     */
    function setRelatedModalDateRangeFromParent(parentRow) {
        if (!parentRow) return;

        relatedStartDate.min = '';
        relatedStartDate.max = '';
        relatedEndDate.min = '';
        relatedEndDate.max = '';

        relatedStartDate.value = '';
        relatedEndDate.value = '';

        updateRelatedAutomaticRateMode();
        updateRelatedSummary();
        updateRelatedSaveState();
    }

    /**
     * Fills the related-event modal using values stored in an existing table row.
     *
     * Used when editing a related/custom sub-event so the modal opens with
     * the current row values already selected.
     *
     * @param {HTMLTableRowElement} row - Related or custom sub-event row.
     */
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
        let pendingDeleteOutOfRangeCustomDays = false;
        let pendingDeleteCustomDaysOnAreaChange = false;
        let editingIsSubEvent = false;
        let editingIsCustomDay = false;

        let pendingCustomizePayload = null;
        let currentCustomizeParentRow = null;


    /**
     * Submits an edited event to the backend.
     *
     * The optional delete flags tell the backend whether custom-day records
     * affected by the principle edit should also be removed.
     *
     * @param {Object} payload - Edited event data.
     * @param {boolean} deleteOutOfRangeCustomDays - Whether to delete custom days outside the new parent date range.
     * @param {boolean} deleteCustomDaysOnAreaChange - Whether to delete custom days when the parent event area changes.
     */
        async function submitEditEvent(
            payload,
            deleteOutOfRangeCustomDays = false,
            deleteCustomDaysOnAreaChange = false
        ) {
            try {
                const url = payload.is_sub_event
                    ? `/facility/events/${payload.event_id}/sub-event`
                    : `/facility/events/${payload.event_id}`;

                await sendJson(url, 'PUT', {
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
                    delete_custom_days_on_area_change: deleteCustomDaysOnAreaChange,
                });

                editDirty = false;
                allowEditModalClose = true;
                bootstrap.Modal.getOrCreateInstance($('editEventModal')).hide();
                toasts.editSaved?.show();

                setTimeout(() => {
                    saveFacilityScroll();
                    window.location.reload();
                }, 2000);
            } catch (error) {
                alert(error.message);
            }
        }

    /**
     * Confirmation button used when editing a principle event may remove
     * affected custom-day rows.
     */
    const confirmParentRangeDeleteBtn = $('confirmParentRangeDeleteBtn');

    /**
     * Gets custom-day rows that fall outside a parent event's edited date range.
     * This is for the warning of the principle days affecting the modified days.
     *
     * @param {string|number} parentId - Parent event ID.
     * @param {string} newStart - New parent start date in YYYY-MM-DD format.
     * @param {string} newEnd - New parent end date in YYYY-MM-DD format.
     * @returns {HTMLTableRowElement[]} Custom-day rows outside the edited range.
     */
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

    /**
     * Gets every custom-day row connected to a principle event.
     *
     * @param {string|number} parentId - Parent event ID.
     * @returns {HTMLTableRowElement[]} Custom-day rows for the parent event.
     */
    function getCustomDaysForParent(parentId) {
        const parentRow = document.querySelector(`tr.parent-event-row[data-entry-id="${parentId}"]`);
        const groupKey = parentRow?.dataset.groupKey;

        if (!groupKey) return [];

        return [...document.querySelectorAll(
            `tr.sub-event-row[data-group-key="${groupKey}"][data-sub-event-type="custom_day"]`
        )];
    }

    /**
     * Checks whether the edited principle event area is different from
     * the currently rendered principle row area.
     *
     * @param {string|number} parentId - Parent event ID.
     * @param {string} newClassroom - Newly selected classroom/area name.
     * @returns {boolean} True when the parent area changed.
     */
    function parentAreaChanged(parentId, newClassroom) {
        const parentRow = document.querySelector(`tr.parent-event-row[data-entry-id="${parentId}"]`);

        if (!parentRow) return false;

        return parentRow.dataset.classroom !== newClassroom;
    }

    /**
     * Opens the principle edit warning modal with a custom message.
     *
     * Used when an edit may affect related custom-day records.
     *
     * @param {string} message - Warning message shown inside the modal.
     */
    function showParentEditWarning(message) {
        const text = $('parentRangeWarningText');

        if (text) {
            text.textContent = message;
        }

        bootstrap.Modal.getOrCreateInstance($('parentRangeWarningModal')).show();
    }

    /**
     * Confirms the principle event edit after the user accepts the warning.
     */
    confirmParentRangeDeleteBtn?.addEventListener('click', async () => {
        if (!pendingEditPayload) return;

        bootstrap.Modal.getOrCreateInstance($('parentRangeWarningModal')).hide();

        await submitEditEvent(
            pendingEditPayload,
            pendingDeleteOutOfRangeCustomDays,
            pendingDeleteCustomDaysOnAreaChange
        );

        pendingEditPayload = null;
        pendingDeleteOutOfRangeCustomDays = false;
        pendingDeleteCustomDaysOnAreaChange = false;
    });


    /**
     * Checks whether a selected date is allowed for the current add-event period type.
     *
     * Laborable events only allow Monday through Friday.
     * Non-laborable events allow the selected date because their specific
     * day restrictions are handled by the calendar disable rules and time validation.
     *
     * @param {string} dateString - Date value in YYYY-MM-DD format.
     * @returns {boolean} True when the date is allowed for the selected period.
     */
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

    /**
     * Gets the date restriction error message for the current add-event period type.
     *
     * @returns {string} Date restriction message.
     */
    function getDateRestrictionMessage() {
        if (isWorkdayPeriod()) {
            return 'Para el período laborable solo puedes seleccionar fechas de lunes a viernes.';
        }

        return '';
    }


    /**
     * Updates Flatpickr date restrictions based on the selected period type.
     *
     * This keeps the date pickers synchronized with business rules:
     * - laborable disables Saturdays and Sundays
     * - non-laborable Saturday disables Sundays
     * - non-laborable Sunday/holiday disables Saturdays
     *
     * If a currently selected date becomes invalid after changing the period type,
     * the date picker is cleared.
     *
     * @param {Object} params - Date restriction settings.
     * @param {HTMLSelectElement} params.periodType - Period type selector.
     * @param {HTMLInputElement} params.startDate - Start date input.
     * @param {HTMLInputElement} params.endDate - End date input.
     * @param {Function} params.updateTimeOptions - Callback that rebuilds time dropdown options.
     * @param {Function} params.updateSummary - Callback that refreshes the related summary.
     */
    function updateDateRestrictions({
            periodType,
            startDate,
            endDate,
            updateTimeOptions,
            updateSummary
        }) {

        const selectedPeriod = periodType?.value;

        const disableRules = [
            function (date) {
                const day = date.getDay();

                if (selectedPeriod === 'workday') {
                    return day === 0 || day === 6;
                }

                if (selectedPeriod === 'non_workday_saturday') {
                    return day === 0;
                }

                if (selectedPeriod === 'non_workday_sunday_holiday') {
                    return day === 6;
                }

                return false;
            }
        ];

        [startDate, endDate].forEach((dateInput) => {
            if (!dateInput?._flatpickr) return;

            dateInput._flatpickr.set('disable', selectedPeriod ? disableRules : []);

            if (dateInput.value && selectedPeriod) {
                const selectedDay = new Date(`${dateInput.value}T00:00:00`).getDay();
                const shouldClear =
                    (selectedPeriod === 'workday' && (selectedDay === 0 || selectedDay === 6)) ||
                    (selectedPeriod === 'non_workday_saturday' && selectedDay === 0) ||
                    (selectedPeriod === 'non_workday_sunday_holiday' && selectedDay === 6);

                if (shouldClear) {
                    dateInput._flatpickr.clear();
                }
            }
        });

        const baseMinDate = startDate?.dataset?.minDate || 'today';
        const baseMaxDate = startDate?.dataset?.maxDate || null;

        if (startDate?._flatpickr) {
            startDate._flatpickr.set('minDate', baseMinDate);
            startDate._flatpickr.set('maxDate', endDate?.value || baseMaxDate);
        }

        if (endDate?._flatpickr) {
            endDate._flatpickr.set('minDate', startDate?.value || baseMinDate);
            endDate._flatpickr.set('maxDate', baseMaxDate);
        }

        updateTimeOptions?.();
        updateSummary?.();
    }


    /**
     * Validates the customize-days scope when the user changes it.
     *
     * The scope determines whether the modification applies only to the
     * selected day or to the selected day and following event days.
     */
    customizeScope?.addEventListener('change', () => {
        clearFieldError(customizeScope, customizeScopeError);

        if (!customizeScope.value) {
            setFieldError(customizeScope, customizeScopeError, 'Selecciona el alcance de la modificación.');
        }

        updateCustomizeSaveState();
        validateCustomizeWorkdayRangeRule(true);
    });

    /**
     * Validates customize-days time selectors when either selected time changes.
     *
     * Clears previous time errors, validates the selected period type,
     * checks the start/end time combination, and updates the save state.
     */
    [customizeStartTime, customizeEndTime].forEach(input => {
        input?.addEventListener('change', () => {
            clearFieldError(customizeStartTime, customizeStartTimeError);
            clearFieldError(customizeEndTime, customizeEndTimeError);

            if (customizeTimeError) {
                customizeTimeError.textContent = '';
            }

            validateCustomizePeriodType(true);
            validateCustomizeWorkdayRangeRule(true);

            if (customizeStartTime.value && customizeEndTime.value) {
                validateEventTimes({
                    startTime: customizeStartTime,
                    endTime: customizeEndTime,
                    periodType: customizePeriodType,
                    startDate: customizeDate,
                    timeError: customizeTimeError,
                    showError: true
                });
            }

            updateCustomizeSaveState();
            customizeDirty = true;
        });
    });

    /**
     * Handles customize-days date changes.
     *
     * When the date changes, selected times and related errors are reset
     * because the valid schedule depends on the chosen date and period type.
     */
    customizeDate?.addEventListener('change', () => {
        clearFieldError(customizeDate, customizeDateError);
        clearFieldError(customizePeriodType, customizePeriodTypeError);

        resetCustomizeTimesAndErrors();
        updateCustomizeTimeOptions();

        if (!customizeDate.value) {
            setFieldError(customizeDate, customizeDateError, 'La fecha es requerida.');
        } else {
            validateCustomizeWorkdayRangeRule(true);
        }

        updateCustomizeSaveState();
        customizeDirty = true;
    });

    /**
     * Handles customize-days period type changes.
     *
     * Clears date and period errors, resets selected times, rebuilds the
     * time dropdown options, validates the new period type, and refreshes
     * the save button state.
     */
    customizePeriodType?.addEventListener('change', () => {
        clearFieldError(customizeDate, customizeDateError);
        clearFieldError(customizePeriodType, customizePeriodTypeError);

        resetCustomizeTimesAndErrors();
        updateCustomizeTimeOptions();

        validateCustomizePeriodType(true);
        validateCustomizeWorkdayRangeRule(true);

        updateCustomizeSaveState();
        customizeDirty = true;
    });

    /**
     * Validates the related-event area when the selected area changes.
     *
     * Also refreshes the related-event summary and save button state.
     */
    relatedArea?.addEventListener('change', () => {
        validateRequiredSelect(
            relatedArea,
            relatedAreaError,
            'Selecciona un área válida.'
        );

        updateRelatedSummary();
        updateRelatedSaveState();
    });

    /**
     * Attaches shared text validation behavior to edit-event text fields.
     *
     * Responsible uses the responsible-name behavior.
     * Description uses the description behavior.
     */
    [
        {
            input: editResponsible,
            error: editResponsibleError,
            validate: validateEditEventForm,
            save: updateEditSaveState,
            type: 'responsible'
        },
        {
            input: editDescription,
            error: editDescriptionError,
            validate: validateEditEventForm,
            save: updateEditSaveState,
            type: 'description'
        },
    ].forEach(({ input, error, validate, save, type }) => {
        if (type === 'responsible') {
            attachResponsibleBehavior(input, error, validate, save);
        } else {
            attachDescriptionBehavior(input, error, validate, save);
        }
    });


    /**
     * Handles related-event period type changes.
     *
     * When the period changes, the selected times are cleared because the
     * allowed time range depends on the period type. Date restrictions,
     * time options, summary values, and save state are then refreshed.
     */
    relatedPeriodType?.addEventListener('change', () => {
        validateRequiredSelect(
            relatedPeriodType,
            relatedPeriodTypeError,
            'Selecciona un tipo de período válido.'
        );

        relatedStartTime.value = '';
        relatedEndTime.value = '';

        clearFieldError(relatedStartTime, relatedStartTimeError);
        clearFieldError(relatedEndTime, relatedEndTimeError);
        if (relatedPeriodType.value) {
            clearFieldError(relatedPeriodType, relatedPeriodTypeError);
        }

        if (relatedTimeError) relatedTimeError.textContent = '';

        updateDateRestrictions({
            periodType: relatedPeriodType,
            startDate: relatedStartDate,
            endDate: relatedEndDate,
            updateTimeOptions: updateRelatedTimeOptions,
            updateSummary: updateRelatedSummary
        });

        updateRelatedSummary();
        updateRelatedSaveState();
    });


    /**
     * Validates related-event time selectors when either selected time changes.
     *
     * The changed selector is validated first. When both start and end times
     * are available, the full time range is validated against the selected
     * period type.
     */
    [relatedStartTime, relatedEndTime].forEach(input => {
        input?.addEventListener('change', () => {
            validateTimeSelectorChange({
                changedSelect: input,
                changedError: input === relatedStartTime
                    ? relatedStartTimeError
                    : relatedEndTimeError,
                changedMessage: input === relatedStartTime
                    ? 'Selecciona un horario inicial válido.'
                    : 'Selecciona un horario final válido.',
                startTime: relatedStartTime,
                endTime: relatedEndTime,
                periodType: relatedPeriodType,
                startDate: relatedStartDate,
                timeError: relatedStartTimeError
            });

            updateRelatedSummary();
            updateRelatedSaveState();
        });
    });

    /**
     * Handles related-event date changes.
     *
     * Clears date validation errors, reapplies date restrictions, recalculates
     * the automatic tariff mode, updates the summary, and refreshes the save state.
     */
    [relatedStartDate, relatedEndDate].forEach(input => {
        input?.addEventListener('change', () => {
            clearFieldError(relatedStartDate, relatedStartDateError);
            clearFieldError(relatedEndDate, relatedEndDateError);

            updateDateRestrictions({
                periodType: relatedPeriodType,
                startDate: relatedStartDate,
                endDate: relatedEndDate,
                updateTimeOptions: updateRelatedTimeOptions,
                updateSummary: updateRelatedSummary
            });

            updateRelatedAutomaticRateMode();
            updateRelatedSummary();
            updateRelatedSaveState();
        });
    });

    /**
     * Handles related-event service checkbox changes.
     *
     * At least one service must be selected before a related event can be saved.
     */
    relatedServiceChecks.forEach(input => {
        input?.addEventListener('change', () => {
            toggleRelatedServicesError(!hasRelatedServices());
            updateRelatedSummary();
            updateRelatedSaveState();
        });
    });

    /**
     * Attaches dirty-state tracking to a group of inputs.
     *
     * @param {Array<HTMLElement|null>} inputs - Inputs that should mark a modal as dirty.
     * @param {Function} setDirty - Callback used to update the matching dirty-state variable.
     */
    function markDirtyOnChange(inputs, setDirty) {
        inputs.forEach(input => {
            input?.addEventListener('input', setDirty);
            input?.addEventListener('change', setDirty);
        });
    }

    /**
     * Marks the edit-event modal as dirty when editable fields change.
     */
    markDirtyOnChange([
        editResponsible,
        editDescription,
        editClassroom,
        editUtilities,
        editElectricity,
        editWater,
        editStartDate,
        editEndDate,
        editStartTime,
        editEndTime,
        editPeriodType
    ], () => {
        editDirty = true;
    });

    /**
     * Marks the related-event modal as dirty when editable fields change.
     */
    markDirtyOnChange([
        relatedArea,
        relatedResponsible,
        relatedDescription,
        relatedUtilities,
        relatedElectricity,
        relatedWater,
        relatedPeriodType,
        relatedStartDate,
        relatedEndDate,
        relatedStartTime,
        relatedEndTime
    ], () => {
        relatedDirty = true;
    });

    /**
     * Marks the customize-days modal as dirty when editable fields change.
     */
    markDirtyOnChange([
        customizeScope,
        customizeDate,
        customizeStartTime,
        customizeEndTime
    ], () => {
        customizeDirty = true;
    });

    /**
     * Gets all selected area checkboxes from the configure-rates modal.
     *
     * @returns {string[]} Selected classroom/area names.
     */
    function getSelectedClassrooms() {
        return getConfigClassroomChecks()
            .filter(check => check.checked)
            .map(check => check.value);
    }

    function updateConfigureClassroomSelectionError() {
        if (!configClassroomSelectionError) return;

        const hasSelectedClassrooms = getSelectedClassrooms().length > 0;
        const shouldShowError = configClassroomSelectionTouched && !hasSelectedClassrooms;

        configClassroomSelectionError.classList.toggle('d-none', !shouldShowError);
    }

    /**
     * Enables or disables the discard selected classrooms button.
     *
     * The button is disabled until at least one classroom/area is selected.
     */
    function updateDiscardButtonState() {
        if (!openDiscardSelectedClassroomsBtn) return;

        const selected = getSelectedClassrooms();
        openDiscardSelectedClassroomsBtn.disabled = selected.length === 0;
    }

    getConfigClassroomChecks().forEach(check => {
        check.addEventListener('change', updateDiscardButtonState);
    });

    updateDiscardButtonState();


    /**
     * Elements used to show toast notifications after reloads.
     *
     * These hidden Blade-rendered elements exist only when the backend
     * completed a matching action successfully.
     */
    const ratesSavedAutoTrigger = document.getElementById('ratesSavedAutoTrigger');
    const rentalSavedAutoTrigger = document.getElementById('rentalSavedAutoTrigger');
    const mockImportAutoTrigger = document.getElementById('mockImportAutoTrigger');

    const deleteEntryAutoTrigger = document.getElementById('deleteEntryAutoTrigger');

    /**
     * Shows the delete-entry success toast after a successful delete action.
     */
    if (deleteEntryAutoTrigger && toasts.deleteEntry) {
        toasts.deleteEntry.show();
    }

    /**
     * Shows the rates-saved success toast after saving configured rates.
     */
    if (ratesSavedAutoTrigger && toasts.ratesSaved) {
        toasts.ratesSaved.show();
    }

    /**
     * Shows the mock-import success toast when the backend sends the
     * mock import trigger. This is for the EventFlow data.
     */
    if (mockImportAutoTrigger) {
        const mockImportToastEl = document.getElementById('mockImportToast');
        if (mockImportToastEl) {
            const mockImportToast = bootstrap.Toast.getOrCreateInstance(mockImportToastEl, { delay: 3000 });
            mockImportToast.show();
        }
    }

    /**
     * Shows the rental-saved success toast after saving a rental event.
     */
    if (rentalSavedAutoTrigger && toasts.rentalSaved) {
        toasts.rentalSaved.show();
    }

    /**
     * Clears all  filters and resets the visible table state.
     *
     * This resets:
     * - search text for responsible and dates
     * - report type
     * - month/year filters
     * - area filter
     * - period type filter
     * - tariff type filter
     * - service filter
     * - pagination page
     */
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

    /**
     * Opens the delete-classroom confirmation modal for selected areas.
     *
     * The selected classroom names are displayed inside the modal so the user
     * can review what will be deleted before confirming.
     */
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

    /**
     * Submits the selected classroom deletion form.
     *
     * Hidden inputs are created dynamically because multiple classrooms can be
     * selected and submitted in the same delete request.
     */
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
            saveFacilityScroll();
            deleteForm.submit();
        });
    }

    /**
     * Selects configure-rates classroom checkboxes from a provided list.
     *
     * Used when the user chooses a group of areas/classes and the interface
     * needs to update the selected checkboxes automatically.
     *
     * @param {string[]} list - Classroom/area names that should be selected.
     */
    function setSelectionByList(list, markTouched = true) {
        getConfigClassroomChecks().forEach(check => {
            check.checked = list.includes(check.value);
        });

        if (markTouched) {
            configClassroomSelectionTouched = true;
        }

        configureDirty = true;
        updateConfigureClassroomSelectionError();
        updateConfigureSaveState();
        updateDiscardButtonState();
    }

    /**
     * Loads configured rate values into the configure-rates form.
     *
     * If the selected classroom/area has no saved configuration,
     * the rate form is cleared instead.
     *
     * @param {string} classroomId - Classroom/area identifier used to read saved rate data.
     */
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

    /**
     * Updates the configure-rates preview totals.
     *
     * Each preview multiplies the configured classroom/area size by the
     * daily rate for its corresponding period type.
     */
    function updateConfigPreview() {
        const area = toNumber(configClassroomArea.value);
        configWorkdayPreview.textContent = formatMoney((area * toNumber(configDaily1.value)).toFixed(2));
        configSaturdayPreview.textContent = formatMoney((area * toNumber(configDaily2.value)).toFixed(2));
        configSundayHolidayPreview.textContent = formatMoney((area * toNumber(configDaily3.value)).toFixed(2));
    }

    /**
     * Validates the configure-rates form.
     *
     * The form is valid only when:
     * - at least one classroom/area is selected
     * - the area field is valid
     * - all money fields are valid
     *
     * @param {boolean} showError - Determines whether validation errors should be displayed.
     * @returns {boolean} True when the configure-rates form is valid.
     */
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

    /**
     * Enables or disables the configure-rates save button.
     */
    function updateConfigureSaveState() {
        saveRatesBtn.disabled = !isConfigureFormValid();
    }

    /**
     * Converts a period type value into its Spanish display label.
     *
     * @param {string} value - Period type value.
     * @returns {string} Spanish period label.
     */
    function periodLabelFromValue(value) {
        if (value === 'workday') return 'Laborable';
        if (value === 'non_workday_saturday') return 'No laborable sábado';
        if (value === 'non_workday_sunday_holiday') return 'No laborable domingo o festivo';
        return '—';
    }

    /**
     * Retrieves configured pricing for a classroom and period type.
     *
     * @param {string} classroomId - Classroom/area identifier.
     * @param {string} periodType - Operational period type.
     * @returns {{daily:number, weekly:number, monthly:number}} Matching configured rates.
     */
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

    /**
     * Checks whether at least one service is selected in the add-event form.
     *
     * @returns {boolean} True when one or more add-event services are selected.
     */
    function hasSelectedServices() {
        return rentalServiceChecks.some(input => input.checked);
    }

    /**
     * Checks whether at least one service is selected in the related-event form.
     *
     * @returns {boolean} True when one or more related-event services are selected.
     */
    function hasRelatedServices() {
        return relatedServiceChecks.some(input => input?.checked);
    }

    /**
     * Shows or hides the related-event services validation error.
     *
     * @param {boolean} show - Whether the related services error should be visible.
     */
    function toggleRelatedServicesError(show) {
        const error = $('relatedServicesError');
        if (!error) return;

        error.classList.toggle('d-none', !show);
        error.classList.toggle('d-block', show);
    }

    /**
     * Shows or hides the add-event services validation error.
     *
     * This message appears when the user tries to save an event without
     * selecting at least one operational service.
     *
     * @param {boolean} show - Whether the services error should be visible.
     */
    function toggleServicesError(show) {
        servicesRequiredMessage.classList.toggle('d-none', !show);
        servicesRequiredMessage.classList.toggle('d-block', show);
    }

    /**
     * Applies an invalid state and message to a form field.
     *
     * If the field belongs to a Flatpickr calendar, the visible alternate
     * input  (no calendar icon) also receives the invalid styling.
     *
     * @param {HTMLElement} field - Form field that should receive the invalid state.
     * @param {HTMLElement|null} errorElement - Element where the error message is displayed.
     * @param {string} message - Validation message to display.
     */
    function setFieldError(input, errorElement, message) {
        input?.classList.add('is-invalid');

        if (errorElement) {
            errorElement.textContent = message;
        }
    }
    /**
     * Removes an invalid state and message from a form field.
     *
     * If the field belongs to a Flatpickr calendar, the visible alternate
     * input is also cleared.
     *
     * @param {HTMLElement} field - Form field that should be cleared.
     * @param {HTMLElement|null} errorElement - Element where the error message is displayed.
     */
    function clearFieldError(field, errorElement) {
        if (!field) return;

        field.classList.remove('is-invalid');

        if (field._flatpickr?.altInput) {
            field._flatpickr.altInput.classList.remove('is-invalid');
        }

        if (errorElement) errorElement.textContent = '';
    }

    /**
     * Validates that a required select field has a selected value.
     *
     * Used by multiple modal workflows to avoid repeating the same required
     * select validation logic.
     *
     * @param {HTMLSelectElement} select - Select element being validated.
     * @param {HTMLElement|null} errorElement - Error element connected to the select.
     * @param {string} message - Message shown when the select is empty.
     * @param {boolean} showError - Whether validation errors should be displayed.
     * @returns {boolean} True when the select has a value.
     */
    function validateRequiredSelect(select, errorElement, message, showError = true) {
        if (!select) return true;

        if (showError) {
            clearFieldError(select, errorElement);
        }

        if (!select.value) {
            if (showError) {
                setFieldError(select, errorElement, message);
            }
            return false;
        }

        return true;
    }

    /**
     * Validates an individual time selector after it changes.
     *
     * First validates the changed selector as required. If both start and end
     * times are available, it then validates the full time range against the
     * selected period type and selected date.
     *
     * @param {Object} params - Time selector validation options.
     * @param {HTMLSelectElement} params.changedSelect - Time selector that changed.
     * @param {HTMLElement|null} params.changedError - Error element for the changed selector.
     * @param {string} params.changedMessage - Required-field message for the changed selector.
     * @param {HTMLSelectElement} params.startTime - Start time selector.
     * @param {HTMLSelectElement} params.endTime - End time selector.
     * @param {HTMLSelectElement} params.periodType - Period type selector.
     * @param {HTMLInputElement} params.startDate - Date input connected to the time range.
     * @param {HTMLElement|null} params.timeError - General time error element.
     * @returns {boolean} True when the changed selector is valid.
     */
    function validateTimeSelectorChange({
            changedSelect,
            changedError,
            changedMessage,
            startTime,
            endTime,
            periodType,
            startDate,
            timeError
        }) {
        const selectorOk = validateRequiredSelect(
            changedSelect,
            changedError,
            changedMessage,
            true
        );

        if (selectorOk && startTime?.value && endTime?.value) {
            validateEventTimes({
                startTime,
                endTime,
                periodType,
                startDate,
                timeError,
                showError: true
            });
        }

        return selectorOk;
    }

    /**
     * Attaches shared validation behavior for responsible-person fields.
     *
     * Responsible fields validation: only allow valid name characters, enforce minimum and maximum length rules,
     * automatically clear validation errors, and refresh the matching save button state.
     *
     * It is also used by: add-event modal,edit-event modal, and related-event modal.
     *
     * @param {HTMLInputElement} input - Responsible field input element.
     * @param {HTMLElement} errorElement - Validation error container.
     * @param {Function} validateFn - Validation function connected to the current workflow.
     * @param {Function} saveStateFn - Save-state updater connected to the current workflow.
     */
    function attachResponsibleBehavior(input, errorElement, validateFn, saveStateFn) {
        input?.addEventListener('input', () => {
            const value = input.value;

            if (value.length > 40) {
                input.value = value.slice(0, 40);

                setFieldError(
                    input,
                    errorElement,
                    'Has alcanzado el máximo de 40 caracteres. No puedes escribir más.'
                );

            } else if (value.length === 40) {

                setFieldError(
                    input,
                    errorElement,
                    'Has alcanzado el máximo de 40 caracteres. Puedes someter el texto tal como está.'
                );

            } else {
                validateFn(true);
            }

            saveStateFn();
        });

        input?.addEventListener('blur', () => {
            input.value = input.value.trim();

            validateFn(true);

            if (input.value.length === 40 && errorElement) {
                clearFieldError(input, errorElement);
            }

            saveStateFn();
        });
    }

    /**
     * Attaches shared validation behavior for description fields.
     *
     * Description fields validation: validate allowed characters,validate length restrictions,
     * refresh save button states dynamically, and sanitize pasted content.
     *
     * It is also used by: add-event modal, edit-event modal, and related-event modal.
     *
     * @param {HTMLTextAreaElement} input - Description textarea element.
     * @param {HTMLElement} errorElement - Validation error container.
     * @param {Function} validateFn - Validation function connected to the current workflow.
     * @param {Function} saveStateFn - Save-state updater connected to the current workflow.
     */
    function attachDescriptionBehavior(input, errorElement, validateFn, saveStateFn) {
        input?.addEventListener('input', () => {
            const value = input.value;

            if (value.length > 250) {
                input.value = value.slice(0, 250);

                setFieldError(
                    input,
                    errorElement,
                    'Has alcanzado el máximo de 250 caracteres. No puedes escribir más.'
                );

            } else if (value.length === 250) {

                setFieldError(
                    input,
                    errorElement,
                    'Has alcanzado el máximo de 250 caracteres. Puedes someter el texto tal como está.'
                );

            } else {
                validateFn(true);
            }

            saveStateFn();
        });

        input?.addEventListener('blur', () => {
            input.value = input.value.trim();

            validateFn(true);
            saveStateFn();
        });
    }


    /**
     * Initializes every Flatpickr calendar used by the facility management page.
     *
     * It does the following:
     * - applies Spanish localization
     * - enables the shared DD/MM/YYYY display format
     * - prevents manual invalid date typing
     * - synchronizes start/end date minimum restrictions
     * - opens calendars through custom calendar icon buttons
     * - applies operational business rules to event date selection
     *
     * It is also used by: add-event modal, edit-event modal,
     * related-event modal, and customize-days modal.
     */
    function initializeFacilityDatePickers() {

        /**
         * Shared Flatpickr configuration.
         *
         * Enables:
         * - Spanish locale
         * - alternate visible formatting
         * - backend-compatible YYYY-MM-DD values
         * - manual typing disabled
         */
        const sharedOptions = {
            locale: Spanish,
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'j F Y',
            allowInput: false,
            disableMobile: true,
            minDate: 'today',
            clickOpens: true,
        };


        /**
         * Creates a reusable Flatpickr date picker.
         *
         * Stops execution when the input does not exist,applies the shared calendar options,
         * allows custom options to override shared options,makes the original input readonly to prevent manual typing,
         * makes the alternate Flatpickr input readonly as well, and opens the calendar when the input or icon is clicked.
         *
         * @param {HTMLInputElement|null} input - Date input connected to Flatpickr.
         * @param {HTMLElement|null} icon - Calendar icon button that opens the picker.
         * @param {Object} options - Optional Flatpickr options for this specific picker.
         */
        const setupPicker = (input, icon, options = {}) => {
            if (!input) return;

            const picker = flatpickr(input, {
                ...sharedOptions,
                ...options
            });

            input.setAttribute('readonly', 'readonly');
            input.setAttribute('inputmode', 'none');

            if (picker.altInput) {
                picker.altInput.setAttribute('readonly', 'readonly');
                picker.altInput.setAttribute('inputmode', 'none');

                picker.altInput.addEventListener('click', () => {
                    picker.open();
                });
            }

            icon?.addEventListener('click', () => {
                picker.open();
            });
        };

        /**
         * Initializes date pickers.
         */
        setupPicker(rentalStartDate, rentalStartDateIcon);
        setupPicker(rentalEndDate, rentalEndDateIcon);

        setupPicker(relatedStartDate, relatedStartDateIcon);
        setupPicker(relatedEndDate, relatedEndDateIcon);

        setupPicker(editStartDate, editStartDateIcon, { minDate: null });
        setupPicker(editEndDate, editEndDateIcon, { minDate: null });

        setupPicker(customizeDate, customizeDateIcon);
    }

    /**
     * Validates a money input field used in the configure-rates modal.
     *
     * Rules are as follows: field is required,exponential notation and plus/minus symbols are not allowed,
     * value must be a valid number, leading zeros are not allowed except for zero itself
     * only up to two decimal digits are allowed, and negative values are not allowed.
     *
     * @param {HTMLInputElement|null} input - Money input field to validate.
     * @param {boolean} showError - Determines whether validation errors should be displayed.
     * @returns {boolean} True when the money field contains a valid value.
     */
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

        /**
         * Blocks exponential notation and explicit positive/negative symbols.
         */
        if (/[eE+\-]/.test(value)) {
            if (showError) {
                setFieldError(input, errorElement, 'Ingresa una cantidad válida usando solo números y hasta 2 dígitos después del punto decimal.');
                inputGroup?.classList.add('is-invalid');
            }
            return false;
        }

        if (!/^(\d+)(\.\d{1,2})?$/.test(value)) {
            if (showError) {
                setFieldError(input, errorElement, 'Ingresa una cantidad válida usando solo números y hasta 2 dígitos después del punto decimal.');
                inputGroup?.classList.add('is-invalid');
            }
            return false;
        }

        /**
         * Prevents negative pricing values.
         */
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

    /**
     * Validates the configured classroom area field.
     *
     * Rules: field is required,only valid numeric values are allowed,
     * exponential notation and signed values are not allowed,
     * only up to 2 decimal places are allowed,
     * negative values are not allowed,and area cannot exceed the configured maximum size.
     *
     * @param {boolean} showError - Determines whether validation errors should be displayed.
     * @returns {boolean} True when the area field is valid.
     */
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
            setFieldError(input, errorElement, 'Ingresa una cantidad válida usando solo números y hasta 2 dígitos después del punto decimal.');
            inputGroup?.classList.add('is-invalid');
        }
        return false;
    }

    if (!/^(\d+)(\.\d{1,2})?$/.test(value)) {
        if (showError) {
            setFieldError(input, errorElement, 'Ingresa una cantidad válida usando solo números y hasta 2 dígitos después del punto decimal.');
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

    /**
     * Validates a responsible person field.
     *
     * Rules as follows: value is required,only letters, Spanish accents, Ñ, and spaces are allowed,
     * value must contain at least 8 characters, and value cannot exceed 40 characters.
     *
     * This function is reusable.
     *
     * @param {boolean} showError - Determines whether validation errors should be displayed.
     * @param {HTMLInputElement} input - Responsible input field to validate.
     * @param {HTMLElement} errorElement - Error element where validation messages are displayed.
     * @returns {boolean} True when the responsible field is valid.
     */
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
                setFieldError(input, errorElement, 'Has alcanzado el máximo de 40 caracteres. No puedes escribir más.');
            }
            return false;
        }

        return true;
    }

    /**
     * Validates an event description field.
     *
     * Rules as follows: value is required,minimum length is 15 characters, and
     * maximum length is 150 characters.
     *
     * This function is reusable because it can validate multiple
     * description fields across different workflows.
     *
     * @param {boolean} showError - Determines whether validation errors should be displayed.
     * @param {HTMLTextAreaElement} input - Description textarea to validate.
     * @param {HTMLElement} errorElement - Error element where validation messages are displayed.
     * @returns {boolean} True when the description field is valid.
     */
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
                setFieldError(input, errorElement, 'Solo se permiten letras, números, espacios, puntos, comas y guiones.');
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

    /**
     * Gets billing units based on the selected rate mode.
     *
     * Daily events are charged per day.
     * Weekly events are grouped into 7-day billing blocks.
     * Monthly events are grouped into 28-day billing blocks.
     *
     * Used by rental, edit-event, and related-event operational
     * cost calculations.
     *
     * @param {string} startValue - Event start date in YYYY-MM-DD format.
     * @param {string} endValue - Event end date in YYYY-MM-DD format.
     * @param {string} rateMode - Selected tariff/rate mode.
     * @param {number} days - Total inclusive amount of event days.
     * @returns {number} Billing unit quantity used for calculations.
     */
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


    /**
     * Calculates how many calendar months are crossed between
     * two selected dates.
     *
     * Used for monthly operational billing calculations.
     *
     * @param {string} startValue - Event start date in YYYY-MM-DD format.
     * @param {string} endValue - Event end date in YYYY-MM-DD format.
     * @returns {number} Inclusive amount of crossed calendar months.
     */
    function getMonthsCrossed(startValue, endValue) {
        if (!startValue) return 1;

        const start = new Date(`${startValue}T00:00:00`);
        const end = new Date(`${endValue || startValue}T00:00:00`);

        const startMonthIndex = start.getFullYear() * 12 + start.getMonth();
        const endMonthIndex = end.getFullYear() * 12 + end.getMonth();

        return Math.max(1, endMonthIndex - startMonthIndex + 1);
    }

    /**
     * Calculates the estimated operational rental total
     * for the add-event workflow.
     *
     * Utility service costs are multiplied by the
     * calculated total event hours.
     *
     * @returns {number} Calculated operational rental estimate.
     */
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

    /**
     * Calculates and updates the related-event operational summary.
     *
     * Utility service costs are multiplied by the
     * calculated total event hours.
     *
     * @returns {number} Calculated related-event operational estimate.
     */
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

    /**
     * Checks whether the add-rental form has all required valid values.
     *
     * @returns {boolean} True when the rental form is valid.
     */
    function isRentalFormValid() {
        const validTimes = validateEventTimes({
            startTime: rentalStartTime,
            endTime: rentalEndTime,
            periodType: rentalPeriodType,
            startDate: rentalStartDate,
            timeError: document.getElementById('rentalTimeError'),
            showError: false
        });

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

    /**
     * Enables or disables the add-rental save button based on
     * the current rental form validation state.
     */
    function updateRentalSaveState() {
        saveRentalBtn.disabled = !isRentalFormValid();
    }

    /**
     * Shows or hides the monthly and yearly report filters based
     * on the selected report type.
     *
     * Monthly reports show both month and year filters.
     * Annual reports only show the year filter.
     * When no report type is selected, both dependent values are cleared.
     */
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

    /**
     * Renders local pagination controls for a visible list of items.
     *
     * @param {HTMLElement} container - Pagination container element.
     * @param {number} currentPage - Currently selected page number.
     * @param {number} totalItems - Total amount of items being paginated.
     * @param {number} itemsPerPage - Amount of items shown per page.
     * @param {Function} onPageChange - Callback executed with the new page number.
     */
    function renderLocalPagination(container, currentPage, totalItems, itemsPerPage, onPageChange) {
        if (!container) return;

        const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));
        const summary = container === facilityCostPagination ? facilityCostPaginationSummary : null;

        if (totalItems <= 0) {
            container.innerHTML = '';
            if (summary) summary.textContent = '';
            return;
        }

        if (summary) {
            const firstItem = ((currentPage - 1) * itemsPerPage) + 1;
            const lastItem = Math.min(currentPage * itemsPerPage, totalItems);
            summary.innerHTML = `
            Mostrando <strong>${firstItem}</strong>
            a <strong>${lastItem}</strong>
            de <strong>${totalItems}</strong> resultados
        `;
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


    /**
     * Applies all local filters to the facility operational cost table.
     *
     * Filters include:
     * - search text
     * - report type
     * - month
     * - year
     * - area
     * - period type
     * - rate type
     * - selected service
     *
     * The table is filtered by event groups. A group is shown when at least
     * one real event row inside that group matches all selected filters.
     *
     */
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
                const searchableText = [
                    row.dataset.responsible || '',
                    row.dataset.date || '',
                    row.dataset.endDate || '',
                    row.cells[0]?.textContent || '',
                    row.cells[1]?.textContent || '',
                    row.cells[2]?.textContent || '',
                ].join(' ').toLowerCase();

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
                    !searchValue || searchableText.includes(searchValue);

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

        // Pagination based on event groups, not individual rows.
        const totalPages = Math.max(1, Math.ceil(filteredGroups.length / FACILITY_COSTS_PER_PAGE));

        if (currentFacilityCostsPage > totalPages) {
            currentFacilityCostsPage = totalPages;
        }

        const start = (currentFacilityCostsPage - 1) * FACILITY_COSTS_PER_PAGE;
        const end = start + FACILITY_COSTS_PER_PAGE;
        const paginatedGroups = filteredGroups.slice(start, end);

        // Show selected groups: header & all real rows inside + spacer.
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

    /**
     * Handles search input behavior for the facility table.
     *
     * While typing, the search button state is refreshed.
     * When Enter is pressed with an enabled search button, the table
     * returns to the first page and applies the current filters.
     */
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

    /**
     * Applies the facility table search when the search button is clicked.
     *
     * The current page is reset so filtered results always begin
     * from the first page.
     */
    if (searchFacilityBtn) {
        searchFacilityBtn.addEventListener('click', () => {
            currentFacilityCostsPage = 1;
            applyTableFilters();
        });
    }


    /**
     * Opens a  modal by its element ID.
     *
     * @param {string} modalId - ID of the modal element to open.
     */
    function openModalById(modalId) {
        const modalEl = $(modalId);
        if (!modalEl) return;

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    /**
     * Clears all action radio button selections.
     *
     * Used after row actions so delete, edit, customize, or related-event
     * radio buttons do not remain visually selected.
     */
    function clearActionRadios() {
        document.querySelectorAll('.action-radio').forEach((radio) => {
            radio.checked = false;
        });
    }

    /**
     * Binds delete row buttons to the delete confirmation modal.
     *
     * When a delete option is selected, the matching delete URL is stored
     * and the delete confirmation modal is opened.
     */
    function bindDeleteButtons() {
        deleteButtons().forEach((btn) => {
            btn.onchange = () => {
                if (!btn.checked) return;

                selectedDeleteUrl = btn.dataset.deleteUrl;
                openModalById('deleteCostEntryModal');
            };
        });
    }

    /**
     * Handles edit button clicks for event cost rows.
     *
     * It does the following: detects whether the selected row is a parent event, custom day, or related area,
     * fills the edit modal with values stored in the row dataset, restores original date values into Flatpickr calendars,
     * restores selected services, enables editable fields, disables classroom changes for custom-day edits,
     * refreshes edit validation, summary, and save state, and
     * opens the edit modal.
     */
    document.addEventListener('click', (event) => {
        const btn = event.target.closest('.edit-cost-row-btn');
        if (!btn) return;

        btn.checked = true;

        const row = btn.closest('tr');
        if (!row) return;

        const editingSubEventType = row.dataset.subEventType || '';
        const editingIsParent = row.classList.contains('parent-event-row');
        editingIsSubEvent = !editingIsParent;
        editingIsCustomDay = editingSubEventType === 'custom_day';
        const editingIsRelatedArea = editingSubEventType === 'related_area';

        editEventId.value = btn.dataset.entryId;
        editClassroom.value = row.dataset.classroom || '';
        editResponsible.value = row.dataset.responsible || '';
        editDescription.value = row.dataset.description || '';
        const originalStartDate = row.dataset.date || '';
        const originalEndDate = row.dataset.endDate || originalStartDate;

        editStartDate.value = originalStartDate;
        editEndDate.value = originalEndDate;

        if (editStartDate?._flatpickr) {
            editStartDate._flatpickr.set('minDate', originalStartDate);
            editStartDate._flatpickr.setDate(originalStartDate, false);
            editStartDate._flatpickr.jumpToDate(originalStartDate);
        }

        if (editEndDate?._flatpickr) {
            editEndDate._flatpickr.set('minDate', originalStartDate);
            editEndDate._flatpickr.setDate(originalEndDate, false);
            editEndDate._flatpickr.jumpToDate(originalEndDate);
        }

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

        [
            editClassroom,
            editResponsible,
            editDescription,
            editUtilities,
            editElectricity,
            editWater,
            editStartDate,
            editEndDate,
            editStartTime,
            editEndTime,
            editPeriodType
        ].forEach((field) => {
            if (field) field.disabled = false;
        });

        if (editClassroom) editClassroom.disabled = editingIsCustomDay;

        if (editRateModeDisplay) {
            editRateModeDisplay.disabled = false;
            editRateModeDisplay.readOnly = true;
        }

        clearEditErrors();
        updateEditSummary();
        updateEditSaveState();

        openModalById('editEventModal');
        editDirty = false;
        allowEditModalClose = false;
    });

    /**
     * Gets every date between two selected dates, including both endpoints.
     *
     * If no end date is provided, the start date is used as the end date.
     * Invalid or missing dates return an empty array.
     *
     * @param {string} startValue - Start date in YYYY-MM-DD format.
     * @param {string} endValue - End date in YYYY-MM-DD format.
     * @returns {string[]} Array of dates formatted as YYYY-MM-DD.
     */
    function getDatesBetween(startValue, endValue = startValue) {
        const dates = [];

        if (!startValue) return dates;

        const start = new Date(`${startValue}T00:00:00`);
        const end = new Date(`${endValue || startValue}T00:00:00`);

        if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) {
            return dates;
        }

        const current = new Date(start);

        while (current <= end) {
            dates.push(formatDateLocal(current));
            current.setDate(current.getDate() + 1);
        }

        return dates;
    }

    /**
     * Gets all dates that already have custom-day modifications
     * inside a specific event group.
     *
     * @param {string} groupKey - Event group key used to find related custom-day rows.
     * @returns {string[]} Array of already modified dates formatted as YYYY-MM-DD.
     */
    function getAlreadyModifiedDatesForGroup(groupKey) {
        if (!groupKey) return [];

        const customDayRows = [
            ...document.querySelectorAll(
                `tr.sub-event-row[data-group-key="${groupKey}"][data-sub-event-type="custom_day"]`
            )
        ];

        const disabledDates = new Set();

        customDayRows.forEach((row) => {
            const startDate = row.dataset.date;
            const endDate = row.dataset.endDate || startDate;

            getDatesBetween(startDate, endDate).forEach((date) => {
                disabledDates.add(date);
            });
        });

        return [...disabledDates];
    }

    /**
     * Gets already modified custom dates that overlap a selected date range.
     *
     * @param {string} selectedDate - Selected start date in YYYY-MM-DD format.
     * @param {string} endDate - Selected end date in YYYY-MM-DD format.
     * @param {string} groupKey - Event group key used to find custom-day rows.
     * @returns {string[]} Modified dates that fall inside the selected range.
     */
    function getOverlappingModifiedDatesInRange(selectedDate, endDate, groupKey) {
        const allModified = getAlreadyModifiedDatesForGroup(groupKey);
        return allModified.filter(d => d >= selectedDate && d <= endDate);
    }

    /**
     * Shows the overwrite warning modal for customize-days changes.
     *
     * The warning explains that existing custom-day modifications in the
     * selected range will be removed if the user continues.
     *
     * @param {number} count - Number of existing modified days affected.
     */
    function showCustomizeOverwriteWarning(count) {
        const text = $('customizeOverwriteWarningText');
        if (text) {
            text.textContent = `El rango seleccionado incluye ${count} día(s) con modificaciones personalizadas existentes. Si continúas, esa(s) modificación(es) serán eliminadas para aplicar el nuevo período a todos los días del rango.`;
        }
        bootstrap.Modal.getOrCreateInstance($('customizeOverwriteWarningModal')).show();
    }

    /**
     * Applies date restrictions to the customize-days calendar.
     *
     * The calendar is limited to the principle event range and disables dates
     * that already have custom-day modifications.
     *
     * @param {HTMLTableRowElement} parentRow - Parent event row used to read date range and group data.
     */
    function applyCustomizeDateRestrictions(parentRow) {
        if (!customizeDate?._flatpickr || !parentRow) return;

        const startDate = parentRow.dataset.date || '';
        const endDate = parentRow.dataset.endDate || startDate;
        const groupKey = parentRow.dataset.groupKey || '';

        const alreadyModifiedDates = getAlreadyModifiedDatesForGroup(groupKey);

        customizeDate._flatpickr.set('minDate', startDate);
        customizeDate._flatpickr.set('maxDate', endDate);
        customizeDate._flatpickr.set('disable', alreadyModifiedDates);
        customizeDate._flatpickr.clear();

        if (startDate) {
            customizeDate._flatpickr.jumpToDate(startDate);
        }

        customizeDate.min = startDate;
        customizeDate.max = endDate;
    }

    /**
     * Binds customize-days, create-related-event, and edit-sub-event buttons.
     *
     * This function connects row action buttons to their matching modal
     * workflows and resets each modal before it opens.
     */
    function bindOptionThreeButtons() {

        customizeDaysButtons().forEach((btn) => {
            btn.onclick = () => {
                const row = btn.closest('tr');

                if (customizeEventId) {
                    customizeEventId.value = btn.dataset.entryId;
                }

                customizeBasePeriodType = row?.dataset.periodType || '';
                currentCustomizeParentRow = row || null;

                customizeScope.value = '';
                customizeDate.value = '';
                customizePeriodType.value = '';

                applyCustomizeDateRestrictions(row);

                customizeStartTime.value = '';
                customizeEndTime.value = '';

                clearFieldError(customizeScope, customizeScopeError);
                clearFieldError(customizeDate, customizeDateError);
                clearFieldError(customizePeriodType, customizePeriodTypeError);

                updateCustomizeTimeOptions();

                customizeStartTime.classList.remove('is-invalid');
                customizeEndTime.classList.remove('is-invalid');
                customizeTimeError.textContent = '';

                updateCustomizeSaveState();

                console.log('Personalizar días para evento:', btn.dataset.entryId);
                openModalById('customizeDaysModal');
                customizeDirty = false;
                allowCustomizeModalClose = false;
            };
        });

        /**
         * Opens the related-event modal in create mode.
         */
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

                relatedStartDate.min = '';
                relatedStartDate.max = '';
                relatedEndDate.min = '';
                relatedEndDate.max = '';

                relatedStartDate.dataset.minDate = 'today';
                relatedStartDate.dataset.maxDate = '';
                relatedEndDate.dataset.minDate = 'today';
                relatedEndDate.dataset.maxDate = '';

                relatedStartDate._flatpickr?.set('minDate', 'today');
                relatedStartDate._flatpickr?.set('maxDate', null);
                relatedStartDate._flatpickr?.clear();

                relatedEndDate._flatpickr?.set('minDate', 'today');
                relatedEndDate._flatpickr?.set('maxDate', null);
                relatedEndDate._flatpickr?.clear();

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
                relatedDirty = false;
                allowRelatedModalClose = false;
            };
        });

        /**
         * Opens the related-event modal in edit mode for an existing sub-event.
         */
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

    /**
     * Checks whether a date range contains days that are invalid
     * for the selected period type.
     *
     * @param {string} startValue - Start date in YYYY-MM-DD format.
     * @param {string} endValue - End date in YYYY-MM-DD format.
     * @param {string} periodType - Selected operational period type.
     * @returns {boolean} True when the range contains an invalid day.
     */
    function dateRangeHasInvalidDayForPeriod(startValue, endValue, periodType) {
        if (!startValue || !endValue || !periodType) return false;

        const current = new Date(`${startValue}T00:00:00`);
        const end = new Date(`${endValue}T00:00:00`);

        while (current <= end) {
            const day = current.getDay();

            if (periodType === 'workday' && (day === 0 || day === 6)) {
                return true;
            }

            if (periodType === 'non_workday_saturday' && day === 0) {
                return true;
            }

            if (periodType === 'non_workday_sunday_holiday' && day === 6) {
                return true;
            }

            current.setDate(current.getDate() + 1);
        }

        return false;
    }

    /**
     * Gets the ending date for the customize-days range.
     *
     * When the selected scope is "this day and following", the range ends
     * on the principle event's end date. Otherwise, the range only uses the
     * currently selected customize date.
     *
     * @returns {string} Customize range end date in YYYY-MM-DD format.
     */
    function getCustomizeRangeEndDate() {
        if (customizeScope?.value !== 'this_and_following' || !currentCustomizeParentRow) {
            return customizeDate.value;
        }

        const parentEndDate =
            currentCustomizeParentRow.dataset.endDate ||
            currentCustomizeParentRow.dataset.date ||
            customizeDate.value;

        if (!customizeDate.value || !parentEndDate) {
            return customizeDate.value;
        }

        const selectedDate = new Date(`${customizeDate.value}T00:00:00`);
        const parentEnd = new Date(`${parentEndDate}T00:00:00`);

        if (selectedDate >= parentEnd) {
            return customizeDate.value;
        }

        return parentEndDate;
    }

    /**
     * Validates whether the selected customize-days period type can be
     * applied to the selected date or selected date range.
     *
     * Prevents incompatible combinations such as:
     * - Sunday/holiday period on a Saturday
     * - Saturday period on a Sunday/holiday
     * - Applying a period to "this day and following" when the range includes
     *   days that are invalid for that period type
     *
     * @param {boolean} showError - Determines whether validation errors should be displayed.
     * @returns {boolean} True when the selected customize period is valid for the selected date range.
     */
    function validateCustomizeWorkdayRangeRule(showError = true) {
        if (!customizeDate?.value || !customizePeriodType?.value) {
            return true;
        }

        const selectedDay = new Date(`${customizeDate.value}T00:00:00`).getDay();
        const selectedPeriod = customizePeriodType.value;
        const customizeRangeEndDate = getCustomizeRangeEndDate();

        if ((selectedDay === 0 || selectedDay === 6) && selectedPeriod === 'workday') {
            if (showError) {
                const message = 'No puedes seleccionar Laborable para sábado o domingo. Usa un período no laborable.';

                setFieldError(customizePeriodType, customizePeriodTypeError, message);
                setFieldError(customizeDate, customizeDateError, message);
            }

            return false;
        }

        if (selectedDay === 6 && selectedPeriod === 'non_workday_sunday_holiday') {
            if (showError) {
                const message = 'No puedes seleccionar Domingo/Festivo para un sábado. Usa No laborable sábado.';

                setFieldError(customizePeriodType, customizePeriodTypeError, message);
                setFieldError(customizeDate, customizeDateError, message);
            }

            return false;
        }

        if (selectedDay === 0 && selectedPeriod === 'non_workday_saturday') {
            if (showError) {
                const message = 'No puedes seleccionar No laborable sábado para un domingo o festivo. Usa Domingo/Festivo.';

                setFieldError(customizePeriodType, customizePeriodTypeError, message);
                setFieldError(customizeDate, customizeDateError, message);
            }

            return false;
        }

        if (
            customizeScope?.value === 'this_and_following' &&
            customizeRangeEndDate &&
            dateRangeHasInvalidDayForPeriod(customizeDate.value, customizeRangeEndDate, selectedPeriod)
        ) {
            if (showError) {
                const message =
                    selectedPeriod === 'workday'
                        ? 'No puedes aplicar Laborable a este día y siguientes porque el rango incluye sábado o domingo.'
                        : selectedPeriod === 'non_workday_saturday'
                            ? 'No puedes aplicar No laborable sábado a este día y siguientes porque el rango incluye domingo.'
                            : 'No puedes aplicar Domingo/Festivo a este día y siguientes porque el rango incluye sábado.';

                setFieldError(customizePeriodType, customizePeriodTypeError, message);
                setFieldError(customizeScope, customizeScopeError, message);
            }

            return false;
        }

        return true;
    }

    /**
     * Validates the customize-days form and returns its submission data.
     *
     * @param {boolean} showError - Determines whether validation errors should be displayed.
     * @returns {Object|null} Customize-days payload when valid, otherwise null.
     */
    function validateCustomizeDaysForm(showError = true) {
        let valid = true;

        if (showError) {
            clearFieldError(customizeScope, customizeScopeError);
            clearFieldError(customizeDate, customizeDateError);
            clearFieldError(customizePeriodType, customizePeriodTypeError);

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

        if (!validateCustomizePeriodType(showError)) {
            valid = false;
        }

        if (!validateCustomizeWorkdayRangeRule(showError)) {
            valid = false;
        }

        if (!customizeStartTime?.value || !customizeEndTime?.value) {
            valid = false;

            if (showError && !customizeStartTime?.value && !customizeEndTime?.value) {
                customizeStartTime?.classList.add('is-invalid');
                customizeEndTime?.classList.add('is-invalid');
                if (customizeTimeError) customizeTimeError.textContent = 'Selecciona hora de inicio y hora de fin.';
            }
        }

        if (
            customizeStartTime?.value &&
            customizeEndTime?.value &&
            customizePeriodType?.value &&
            !validateEventTimes({
                startTime: customizeStartTime,
                endTime: customizeEndTime,
                periodType: customizePeriodType,
                startDate: customizeDate,
                timeError: customizeTimeError,
                showError
            })
        ) {
            valid = false;
        }

        if (!valid) return null;

        return {
            event_id: customizeEventId.value,
            scope: customizeScope.value,
            date: customizeDate.value,
            start_time: customizeStartTime.value,
            end_time: customizeEndTime.value,
            period_type: customizePeriodType.value
        };
    }

    /**
     * Enables or disables the customize-days save button based on the
     * current customize-days form validation state.
     */
    function updateCustomizeSaveState() {
        if (!saveCustomizeDaysBtn) return;
        saveCustomizeDaysBtn.disabled = !validateCustomizeDaysForm(false);
    }


    /**
     * Checks whether at least one edit-event service is selected.
     *
     * @returns {boolean} True when one or more edit services are checked.
     */
    function hasEditServices() {
        return editServiceChecks.some(input => input?.checked);
    }

    /**
     * Shows or hides the edit-event services validation error.
     *
     * @param {boolean} show - Whether the services error should be visible.
     */
    function toggleEditServicesError(show) {
        if (!editServicesError) return;

        editServicesError.classList.toggle('d-none', !show);
        editServicesError.classList.toggle('d-block', show);
    }

    /**
     * Clears all edit-event validation errors.
     */
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

    /**
     * Updates the edit-event automatic tariff/rate type.
     *
     * The selected type is calculated from the inclusive amount of days
     * between the edit start date and edit end date.
     * - 1 to 6 days use daily
     * - 7 to 27 days use weekly
     * - 28 or more days use monthly
     */
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

    /**
     * Calculates and displays the estimated total for the edit-event modal.
     *
     * @returns {number} Calculated edit-event estimate.
     */
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

    /**
     * Validates the edit-event form and builds the edit row.
     *
     * @param {boolean} showError - Determines whether validation errors should be displayed.
     * @returns {Object|null} Edit-event payload when valid, otherwise null.
     */
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
            if (showError) setFieldError(editResponsible, editResponsibleError, 'El responsable es un campo obligatorio para llenar.');
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
            if (showError) setFieldError(editDescription, editDescriptionError, 'Solo se permiten letras, números, espacios, puntos, comas y guiones.');
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

        const validTimes = validateEventTimes({
            startTime: editStartTime,
            endTime: editEndTime,
            periodType: editPeriodType,
            startDate: editStartDate,
            timeError: editTimeError,
            showError
        });

        if (!validTimes) {
            valid = false;
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
            is_sub_event: editingIsSubEvent,
            scope: 'whole_event'
        };
    }

    /**
     * Enables or disables the edit-event save button based on
     * the current edit form validation state.
     */
    function updateEditSaveState() {
        if (!saveEditEventBtn) return;
        saveEditEventBtn.disabled = !validateEditEventForm(false);
    }

    /**
     * Validates the related-event form and builds the related-event payload.
     *
     * @param {boolean} showError - Determines whether validation errors should be displayed.
     * @returns {Object|null} Related-event payload when valid, otherwise null.
     */
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
            if (showError) setFieldError(relatedResponsible, relatedResponsibleError, 'El responsable es un campo obligatorio para llenar.');
        } else if (!responsibleRegex.test(responsible)) {
            valid = false;
            if (showError) setFieldError(relatedResponsible, relatedResponsibleError, 'Solo se permiten letras y espacios.');
        } else if (responsible.length < 8) {
            valid = false;
            if (showError) setFieldError(relatedResponsible, relatedResponsibleError, 'El responsable debe tener al menos 8 caracteres.');
        } else if (responsible.length > 40) {
            valid = false;
            if (showError) setFieldError(relatedResponsible, relatedResponsibleError, 'Has alcanzado el máximo de 40 caracteres. No puedes escribir más.');
        }

        if (!description) {
            valid = false;
            if (showError) setFieldError(relatedDescription, relatedDescriptionError, 'La descripción es obligatoria.');
        } else if (!descriptionRegex.test(description)) {
            valid = false;
            if (showError) setFieldError(relatedDescription, relatedDescriptionError, 'Solo se permiten letras, números, espacios, puntos, comas y guiones.');
        } else if (description.length < 10) {
            valid = false;
            if (showError) setFieldError(relatedDescription, relatedDescriptionError, 'La descripción debe tener al menos 10 caracteres.');
        } else if (description.length > 250) {
            valid = false;
            if (showError) setFieldError(relatedDescription, relatedDescriptionError, 'Has alcanzado el máximo de 250 caracteres. No puedes escribir más.');
        }

        const periodTypeOk = validateRequiredSelect(
            relatedPeriodType,
            relatedPeriodTypeError,
            'Selecciona un tipo de período válido.',
            showError
        );

        if (!periodTypeOk) {
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

        const validTimes = validateEventTimes({
            startTime: relatedStartTime,
            endTime: relatedEndTime,
            periodType: relatedPeriodType,
            startDate: relatedStartDate,
            timeError: relatedTimeError,
            showError
        });

        if (!validTimes) {
            valid = false;
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

    /**
     * Enables or disables the related-event save button based on
     * the current related-event form validation state.
     */
    function updateRelatedSaveState() {
        if (!saveRelatedEventBtn) return;
        saveRelatedEventBtn.disabled = !validateRelatedEventForm(false);
    }

    /**
     * Submits customize-days changes to the backend.
     *
     * Uses the full schedule update endpoint when the entire event is being
     * updated. Otherwise, it uses the customize-days endpoint.
     *
     * @param {Object} payload - Validated customize-days payload.
     * @param {boolean} forceOverwrite - Whether existing custom-day changes should be overwritten.
     */
    async function submitCustomizeDays(payload, forceOverwrite = false) {
        const body = {
            scope: payload.scope,
            date: payload.date,
            start_time: payload.start_time,
            end_time: payload.end_time,
            period_type: payload.period_type,
            force_overwrite: forceOverwrite,
        };

        const isEntireEvent = payload.scope === 'entire_event';

        const url = isEntireEvent
            ? `/facility/events/${payload.event_id}/schedule`
            : `/facility/events/${payload.event_id}/customize-days`;

        const method = isEntireEvent ? 'PUT' : 'POST';

        try {
            await sendJson(url, method, body);

            customizeDirty = false;
            allowCustomizeModalClose = true;
            bootstrap.Modal.getOrCreateInstance($('customizeDaysModal')).hide();
            toasts.customizeSaved?.show();

            setTimeout(() => {
                saveFacilityScroll();
                window.location.reload();
            }, 2000);
        } catch (error) {
            alert(error.message);
        }
    }

    /**
     * Handles customize-days save button clicks.
     *
     * Validates the customize-days form, checks whether the selected range
     * overlaps existing custom-day modifications, and either opens the
     * overwrite warning modal or submits the changes directly.
     */
    if (saveCustomizeDaysBtn) {
        saveCustomizeDaysBtn.addEventListener('click', async () => {
            const payload = validateCustomizeDaysForm(true);

            if (!payload) {
                updateCustomizeSaveState();
                return;
            }

            if (payload.scope === 'this_and_following' && currentCustomizeParentRow) {
                const endDate = currentCustomizeParentRow.dataset.endDate || currentCustomizeParentRow.dataset.date || '';
                const groupKey = currentCustomizeParentRow.dataset.groupKey || '';
                const conflicting = getOverlappingModifiedDatesInRange(payload.date, endDate, groupKey);

                if (conflicting.length > 0) {
                    pendingCustomizePayload = payload;
                    showCustomizeOverwriteWarning(conflicting.length);
                    return;
                }
            }

            await submitCustomizeDays(payload, false);
        });
    }

    /**
     * Confirms overwrite of existing custom-day modifications.
     *
     * When confirmed, the pending customize-days payload is submitted with
     * force overwrite enabled.
     */
    $('confirmCustomizeOverwriteBtn')?.addEventListener('click', async () => {
        if (!pendingCustomizePayload) return;

        bootstrap.Modal.getOrCreateInstance($('customizeOverwriteWarningModal')).hide();

        const payload = pendingCustomizePayload;
        pendingCustomizePayload = null;

        await submitCustomizeDays(payload, true);
    });

    /**
     * Handles edit-event save button clicks.
     *
     * Validates the edit form, checks whether editing a parent event would
     * remove custom-day modifications, and either opens a warning modal or
     * submits the edit directly.
     */
    if (saveEditEventBtn) {
        saveEditEventBtn.addEventListener('click', async () => {
            const payload = validateEditEventForm(true);

            if (!payload) {
                updateEditSaveState();
                return;
            }

            if (!payload.is_sub_event) {
                const outOfRangeCustomDays = getCustomDaysOutsideEditedParentRange(
                    payload.event_id,
                    payload.event_date,
                    payload.event_end_date
                );

                const areaWasChanged = parentAreaChanged(
                    payload.event_id,
                    payload.classroom
                );

                const customDaysForParent = getCustomDaysForParent(payload.event_id);

                if (
                    outOfRangeCustomDays.length > 0 ||
                    (areaWasChanged && customDaysForParent.length > 0)
                ) {
                    pendingEditPayload = payload;
                    pendingDeleteOutOfRangeCustomDays = outOfRangeCustomDays.length > 0;
                    pendingDeleteCustomDaysOnAreaChange = areaWasChanged && customDaysForParent.length > 0;

                    let message = '';

                    if (pendingDeleteOutOfRangeCustomDays && pendingDeleteCustomDaysOnAreaChange) {
                        message = `Esta edición eliminará ${customDaysForParent.length} modificación(es), porque cambiaste el área del evento principal y/o algunas modificaciones quedan fuera del nuevo rango.`;
                    } else if (pendingDeleteCustomDaysOnAreaChange) {
                        message = `Cambiar el área del evento principal eliminará ${customDaysForParent.length} modificación(es), porque esas modificaciones fueron calculadas usando el área anterior.`;
                    } else {
                        message = `Esta edición deja ${outOfRangeCustomDays.length} modificación(es) fuera del rango del evento principal. Si continúas, esas modificaciones serán eliminadas.`;
                    }

                    showParentEditWarning(message);
                    return;
                }
            }

            await submitEditEvent(payload, false, false);
        });
    }

    /**
     * Handles related-event save button clicks.
     *
     * Validates the related-event form, builds the backend payload, selects
     * the correct create or update endpoint, submits the request, closes the
     * modal, shows a success toast, and reloads the page.
     */
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

                relatedDirty = false;
                allowRelatedModalClose = true;
                bootstrap.Modal.getOrCreateInstance($('createRelatedModal')).hide();
                toasts.relatedSaved?.show();

                setTimeout(() => {
                    saveFacilityScroll();
                    window.location.reload();
                }, 2000);
            } catch (error) {
                alert(error.message);
            }
        });
    }

    /**
     * Checks whether the configure-rates form has unsaved changes.
     *
     * Changes include selected areas or any filled rate/configuration
     * input values.
     *
     * @returns {boolean} True when configure-rates has unsaved changes.
     */
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

    /**
     * Checks whether the add-rental form has unsaved changes.
     *
     * Changes include selected values, typed text, selected dates/times,
     * selected period type, detected rate mode, and checked services.
     *
     * @returns {boolean} True when the rental form has unsaved changes.
     */
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

    /**
     * Resets the configure-rates form to its initial clean state.
     */
    function resetConfigureFormState() {
        configureRatesForm.reset();
        configClassroomSelectionTouched = false;
        setSelectionByList([], false);
        configClassroomSelectionError?.classList.add('d-none');
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

    /**
     * Resets the add-rental form to its initial clean state.
     */
    function resetRentalFormState() {
        addRentalForm.reset();
        rentalStartDate?._flatpickr?.clear();
        rentalEndDate?._flatpickr?.clear();

        if (rentalStartDate) rentalStartDate.value = '';
        if (rentalEndDate) rentalEndDate.value = '';
        addRentalForm.classList.remove('was-validated');
        rentalDirty = false;

        toggleServicesError(false);
        clearFieldError(rentalResponsible, rentalResponsibleError);
        clearFieldError(rentalDescription, rentalDescriptionError);
        clearFieldError(rentalStartDate, $('rentalStartDateError'));
        clearFieldError(rentalEndDate, $('rentalEndDateError'));
        clearFieldError(rentalClassroom, rentalClassroomError);
        clearFieldError(rentalPeriodType, rentalPeriodTypeError);
        clearFieldError(rentalStartTime, rentalStartTimeError);
        clearFieldError(rentalEndTime, rentalEndTimeError);
        toggleRentalDateRangeUI();

        rentalEstimatedTotal.textContent = formatMoney(0);
        rentalEstimatedTotalInput.value = '0.00';
        detectedPeriodLabel.textContent = '—';
        detectedHoursLabel.textContent = '0.00 horas';

        updateAutomaticRateMode();
        updateRentalTimeOptions();
        updateRentalSaveState();
    }

    /**
     * Selects all rendered areas in the configure-rates modal.
     */
    $('selectAllClassroomsBtn').addEventListener('click', () => {
        setSelectionByList(getAllRenderedClassrooms(), true);
    });

    /**
     * Selects only rendered academic classrooms in the configure-rates modal.
     */
    $('selectAcademicClassroomsBtn').addEventListener('click', () => {
        setSelectionByList(getAcademicRenderedClassrooms(), true);
    });

    /**
     * Selects only rendered lateral classrooms in the configure-rates modal.
     */
    $('selectLateralClassroomsBtn').addEventListener('click', () => {
        setSelectionByList(getLateralRenderedClassrooms(), true);
    });

    /**
     * Clears all selected classrooms in the configure-rates modal.
     */
    $('clearClassroomsSelectionBtn').addEventListener('click', () => {
        setSelectionByList([], false);

        configClassroomSelectionTouched = false;
        configClassroomSelectionError?.classList.add('d-none');

        clearRatesForm();

        [
            configClassroomArea,
            configUtilities,
            configElectricity,
            configWater,
            configDaily1,
            configWeekly1,
            configMonthly1,
            configDaily2,
            configWeekly2,
            configMonthly2,
            configDaily3,
            configWeekly3,
            configMonthly3,
        ].forEach((input) => {
            clearFieldError(input, document.getElementById(`${input.id}Error`));
            input.closest('.money-input-group')?.classList.remove('is-invalid');
        });

        updateConfigureSaveState();
        updateDiscardButtonState();
    });

    /**
     * Resets the edit-event modal.
     *
     * Clears editable text fields, removes validation errors, and refreshes
     * the edit save button state.
     */
    function resetEditEventState() {
        editDirty = false;
        allowEditModalClose = false;

        if (editResponsible) editResponsible.value = '';
        if (editDescription) editDescription.value = '';
        if (editClassroom) editClassroom.value = '';
        if (editPeriodType) editPeriodType.value = '';
        if (editRateMode) editRateMode.value = '';
        if (editRateModeDisplay) editRateModeDisplay.value = 'Se calculará automáticamente';

        if (editStartDate) editStartDate.value = '';
        if (editEndDate) editEndDate.value = '';

        editStartDate?._flatpickr?.set('disable', []);
        editStartDate?._flatpickr?.set('minDate', 'today');
        editStartDate?._flatpickr?.set('maxDate', null);
        editStartDate?._flatpickr?.clear();

        editEndDate?._flatpickr?.set('disable', []);
        editEndDate?._flatpickr?.set('minDate', 'today');
        editEndDate?._flatpickr?.set('maxDate', null);
        editEndDate?._flatpickr?.clear();

        if (editStartTime) {
            editStartTime.innerHTML = '<option value="" selected>Primero selecciona el tipo de período</option>';
        }

        if (editEndTime) {
            editEndTime.innerHTML = '<option value="" selected>Primero selecciona el tipo de período</option>';
        }

        editServiceChecks.forEach(input => {
            if (input) input.checked = false;
        });

        if (editEstimatedTotal) editEstimatedTotal.textContent = '$0.00';
        if (editDetectedPeriodLabel) editDetectedPeriodLabel.textContent = '—';
        if (editDetectedHoursLabel) editDetectedHoursLabel.textContent = '0.00 horas';

        clearFieldError(editResponsible, editResponsibleError);
        clearFieldError(editDescription, editDescriptionError);
        clearFieldError(editClassroom, editClassroomError);
        clearFieldError(editPeriodType, editPeriodTypeError);
        clearFieldError(editStartDate, editStartDateError);
        clearFieldError(editEndDate, editEndDateError);
        clearFieldError(editStartTime, editStartTimeError);
        clearFieldError(editEndTime, editEndTimeError);

        if (editTimeError) editTimeError.textContent = '';

        toggleEditServicesError(false);
        updateEditSaveState();
    }

    /**
     * Resets the customize-days modal.
     */
    function resetCustomizeDaysState() {
        if (customizeScope) customizeScope.value = '';
        if (customizeDate) customizeDate.value = '';
        if (customizeStartTime) customizeStartTime.value = '';
        if (customizeEndTime) customizeEndTime.value = '';

        if (customizeDate?._flatpickr) {
            customizeDate._flatpickr.set('disable', []);
            customizeDate._flatpickr.set('minDate', 'today');
            customizeDate._flatpickr.set('maxDate', null);
            customizeDate._flatpickr.clear();
        }

        clearFieldError(customizeScope, customizeScopeError);
        clearFieldError(customizeDate, customizeDateError);

        customizeStartTime?.classList.remove('is-invalid');
        customizeEndTime?.classList.remove('is-invalid');
        if (customizeTimeError) customizeTimeError.textContent = '';

        updateCustomizeSaveState();
    }

    /**
     * Resets the related-event modal.
     *
     * Returns the modal to create mode, clears fields, clears services,
     * resets automatic rate display values, clears validation errors,
     * and refreshes the related-event save button state.
     */
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

        relatedStartDate?.removeAttribute('data-min-date');
        relatedStartDate?.removeAttribute('data-max-date');
        relatedEndDate?.removeAttribute('data-min-date');
        relatedEndDate?.removeAttribute('data-max-date');

        relatedStartDate?._flatpickr?.set('disable', []);
        relatedStartDate?._flatpickr?.set('minDate', 'today');
        relatedStartDate?._flatpickr?.set('maxDate', null);
        relatedStartDate?._flatpickr?.clear();

        relatedEndDate?._flatpickr?.set('disable', []);
        relatedEndDate?._flatpickr?.set('minDate', 'today');
        relatedEndDate?._flatpickr?.set('maxDate', null);
        relatedEndDate?._flatpickr?.clear();

        if (relatedStartTime) {
            relatedStartTime.innerHTML = '<option value="" selected>Primero selecciona el tipo de período</option>';
        }

        if (relatedEndTime) {
            relatedEndTime.innerHTML = '<option value="" selected>Primero selecciona el tipo de período</option>';
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

    /**
     * Resets the edit-event modal after it is fully hidden.
     */
    $('editEventModal')?.addEventListener('hidden.bs.modal', resetEditEventState);

    /**
     * Resets the customize-days modal after it is fully hidden.
     */
    $('customizeDaysModal')?.addEventListener('hidden.bs.modal', resetCustomizeDaysState);

    /**
     * Resets the related-event modal after it is fully hidden.
     */
    $('createRelatedModal')?.addEventListener('hidden.bs.modal', resetRelatedEventState);


    /**
     * Handles configure-rates area selection changes.
     *
     * Loads existing rates when exactly one classroom is selected.
     * Clears the form when no classroom or multiple classrooms are selected.
     * Marks the configure modal as dirty and refreshes the save state.
     */
    function handleClassroomCheckboxChange() {
        configClassroomSelectionTouched = true;

        const selected = getSelectedClassrooms();

        if (selected.length === 1) {
            loadRatesIntoForm(selected[0]);
        } else {
            clearRatesForm();
        }

        configureDirty = true;
        updateConfigureClassroomSelectionError();
        updateConfigureSaveState();
    }
    /**
     * Binds configure-rates area checkboxes to the shared
     * area selection handler.
     */
    function bindClassroomCheckboxes() {
        getConfigClassroomChecks().forEach((check) => {
            check.removeEventListener('change', handleClassroomCheckboxChange);
            check.addEventListener('change', handleClassroomCheckboxChange);
        });
    }

    /**
     * Validates money inputs while typing and after losing focus.
     *
     * Also marks the configure form as dirty, updates preview totals,
     * and refreshes the configure save button state.
     */
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

    /**
     * Handles rental form field changes.
     *
     * Validates changed fields, updates date restrictions, rebuilds time
     * options when needed, recalculates the estimate, and refreshes the
     * rental save button state.
     */
    [rentalClassroom,rentalStartDate,rentalEndDate,rentalStartTime, rentalEndTime, rentalPeriodType].forEach((el) => {
        if (!el) return;

        el.addEventListener('change', () => {
            rentalDirty = true;

            if (el === rentalClassroom) {
                validateRequiredSelect(rentalClassroom, rentalClassroomError, 'Selecciona un área válida.');
            }

            if (el === rentalPeriodType) {
                validateRequiredSelect(rentalPeriodType, rentalPeriodTypeError, 'Selecciona un tipo de período válido.');
            }

            if (el === rentalStartTime || el === rentalEndTime) {
                validateTimeSelectorChange({
                    changedSelect: el,
                    changedError: el === rentalStartTime ? rentalStartTimeError : rentalEndTimeError,
                    changedMessage: el === rentalStartTime
                        ? 'Selecciona un horario inicial válido.'
                        : 'Selecciona un horario final válido.',
                    startTime: rentalStartTime,
                    endTime: rentalEndTime,
                    periodType: rentalPeriodType,
                    startDate: rentalStartDate,
                    timeError: rentalStartTimeError
                });
            }


            if (el === rentalPeriodType) {
                rentalStartTime.value = '';
                rentalEndTime.value = '';

                clearFieldError(rentalStartTime, rentalStartTimeError);
                clearFieldError(rentalEndTime, rentalEndTimeError);
                if (rentalPeriodType.value) {
                    clearFieldError(rentalPeriodType, rentalPeriodTypeError);
                }

                updateRentalTimeOptions();
                updateDateRestrictions({
                    periodType: rentalPeriodType,
                    startDate: rentalStartDate,
                    endDate: rentalEndDate,
                    updateTimeOptions: updateRentalTimeOptions,
                    updateSummary: calculateRentalEstimate
                });
            }

            if (el === rentalStartDate || el === rentalEndDate) {
                updateDateRestrictions({
                    periodType: rentalPeriodType,
                    startDate: rentalStartDate,
                    endDate: rentalEndDate,
                    updateTimeOptions: updateRentalTimeOptions,
                    updateSummary: calculateRentalEstimate
                });
            }
            updateAutomaticRateMode();
            calculateRentalEstimate();
            updateRentalSaveState();
            toggleRentalDateRangeUI();
        });
    });


    /**
     * Validates the rental description while the user types.
     *
     * Rules it follows: marks the rental form as dirty,enforces the 250 character limit,
     * displays the correct character-limit message,validates the description when under the limit,
     * and refreshes the rental save button state.
     */
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
                'Has alcanzado el máximo de 250 caracteres. Puedes someter el texto tal como está.'
            );
        } else {
            validateDescription(true);
        }

        updateRentalSaveState();
    });

    /**
     * Validates the rental description when the field loses focus.
     */
    rentalDescription.addEventListener('blur', () => {
        validateDescription(true);
        updateRentalSaveState();
    });

    /**
     * Handles rental service checkbox changes.
     *
     * Recalculates the estimate, shows the service validation error when
     * no service is selected, and refreshes the rental save button state.
     */
    rentalServiceChecks.forEach((el) => {
        el.addEventListener('change', () => {
            rentalDirty = true;
            calculateRentalEstimate();
            toggleServicesError(!hasSelectedServices());
            updateRentalSaveState();
        });
    });

    /**
     * Attaches shared responsible-name validation behavior to the rental
     * responsible field.
     */
    attachResponsibleBehavior(
        rentalResponsible,
        rentalResponsibleError,
        validateResponsible,
        updateRentalSaveState
    );

    /**
     * Attaches shared responsible-name validation behavior to the related-event
     * responsible field.
     */
    attachResponsibleBehavior(
        relatedResponsible,
        relatedResponsibleError,
        (showError) => validateResponsible(showError, relatedResponsible, relatedResponsibleError),
        updateRelatedSaveState
    );

    /**
     * Attaches shared description validation behavior to the related-event
     * description field.
     */
    attachDescriptionBehavior(
        relatedDescription,
        relatedDescriptionError,
        (showError) => validateDescription(showError, relatedDescription, relatedDescriptionError),
        updateRelatedSaveState
    );

    /**
     * Handles edit-event area changes.
     */
    editClassroom?.addEventListener('change', () => {
        validateRequiredSelect(
            editClassroom,
            editClassroomError,
            'Selecciona un área válida.'
        );

        updateEditSummary();
        updateEditSaveState();
    });

    /**
     * Handles edit-event period type changes.
     *
     * Clears selected times, updates date restrictions, rebuilds available
     * time options, recalculates the summary, and refreshes save state.
     */
    editPeriodType?.addEventListener('change', () => {
        validateRequiredSelect(
            editPeriodType,
            editPeriodTypeError,
            'Selecciona un tipo de período válido.'
        );

        if (editStartTime) editStartTime.value = '';
        if (editEndTime) editEndTime.value = '';

        clearFieldError(editStartTime, editStartTimeError);
        clearFieldError(editEndTime, editEndTimeError);
        if (editPeriodType.value) {
            clearFieldError(editPeriodType, editPeriodTypeError);
        }

        if (editTimeError) editTimeError.textContent = '';

        updateDateRestrictions({
            periodType: editPeriodType,
            startDate: editStartDate,
            endDate: editEndDate,
            updateTimeOptions: updateEditTimeOptions,
            updateSummary: updateEditSummary
        });

        updateEditSummary();
        updateEditSaveState();
    });

    /**
     * Handles edit-event time changes.
     *
     * Validates the changed time selector, recalculates the edit estimate,
     * and refreshes the save button state.
     */
    [editStartTime, editEndTime].forEach(input => {
        input?.addEventListener('change', () => {
            validateTimeSelectorChange({
                changedSelect: input,
                changedError: input === editStartTime ? editStartTimeError : editEndTimeError,
                changedMessage: input === editStartTime
                    ? 'Selecciona un horario inicial válido.'
                    : 'Selecciona un horario final válido.',
                startTime: editStartTime,
                endTime: editEndTime,
                periodType: editPeriodType,
                startDate: editStartDate,
                timeError: editStartTimeError
            });

            updateEditSummary();
            updateEditSaveState();
        });
    });

    /**
     * Handles edit-event date changes.
     *
     * Clears date errors, updates Flatpickr restrictions, recalculates the
     * edit estimate, and refreshes save state.
     */
    [editStartDate, editEndDate].forEach(input => {
        input?.addEventListener('change', () => {
            clearFieldError(editStartDate, editStartDateError);
            clearFieldError(editEndDate, editEndDateError);

            updateDateRestrictions({
                periodType: editPeriodType,
                startDate: editStartDate,
                endDate: editEndDate,
                updateTimeOptions: updateEditTimeOptions,
                updateSummary: updateEditSummary
            });

            updateEditSummary();
            updateEditSaveState();
        });
    });

    /**
     * Handles edit-event service checkbox changes.
     */
    editServiceChecks.forEach(input => {
        input?.addEventListener('change', () => {
            toggleEditServicesError(!hasEditServices());
            updateEditSummary();
            updateEditSaveState();
        });
    });


    /**
     * Handles operational cost table filter changes.
     *
     * Resets pagination to the first page, updates month/year visibility,
     * and reapplies the table filters.
     */
    [reportType, reportMonth, reportYear, filterClassroom, filterPeriodType, filterRateMode, filterServices].forEach((el) => {
        if (!el) return;

        el.addEventListener('change', () => {
            currentFacilityCostsPage = 1;
            toggleMonthFilter();
            applyTableFilters();
        });
    });

    /**
     * Handles configure-rates form submission.
     *
     * Validates the configure-rates form, stores the scroll target,
     * and submits the form when valid.
     */
    saveRatesBtn.addEventListener('click', () => {
        configureRatesForm.classList.add('was-validated');

        if (!isConfigureFormValid(true)) {
            updateConfigureSaveState();
            return;
        }

        sessionStorage.setItem('facilityScrollTarget', 'facilityActionButtons');
        configureRatesForm.submit();
    });

    /**
     * Handles add-rental form submission.
     *
     * Runs all visible validations, recalculates the estimate, checks required
     * selections, stores the scroll target, and submits the form when valid.
     */
    saveRentalBtn?.addEventListener('click', () => {
        addRentalForm.classList.add('was-validated');

        updateAutomaticRateMode();
        calculateRentalEstimate();

        const responsibleOk = validateResponsible(true);
        const descriptionOk = validateDescription(true);
        const servicesOk = hasSelectedServices();
        const datesOk = validateRentalDates(true, true);
        const classroomOk = validateRequiredSelect(
            rentalClassroom,
            rentalClassroomError,
            'Selecciona un área válida.'
        );

        const periodTypeOk = validateRequiredSelect(
            rentalPeriodType,
            rentalPeriodTypeError,
            'Selecciona un tipo de período válido.'
        );

        const startTimeOk = validateRequiredSelect(
            rentalStartTime,
            rentalStartTimeError,
            'Selecciona un horario inicial válido.'
        );

        const endTimeOk = validateRequiredSelect(
            rentalEndTime,
            rentalEndTimeError,
            'Selecciona un horario final válido.'
        );

        const formOk = isRentalFormValid();

        toggleServicesError(!servicesOk);

        if (!responsibleOk || !descriptionOk || !servicesOk ||
            !datesOk || !classroomOk || !periodTypeOk || !startTimeOk ||
            !endTimeOk || !formOk
        ) {
            updateRentalSaveState();
            return;
        }

        sessionStorage.setItem('facilityScrollTarget', 'facilityCostTable');
        addRentalForm.submit();
    });

    /**
     * Confirms deletion of a selected event cost row.
     *
     * Uses the previously stored delete URL, saves scroll position,
     * updates the delete form action, and submits the form.
     */
    if (confirmDeleteCostEntryBtn) {
        confirmDeleteCostEntryBtn.addEventListener('click', () => {
            if (selectedDeleteUrl) {
                const deleteForm = $('deleteCostEntryForm');
                if (!deleteForm) return;

                saveFacilityScroll();
                deleteForm.action = selectedDeleteUrl;
                deleteForm.submit();
            }
        });
    }


    /**
     * Handles configure-rates cancel and close buttons.
     *
     * Opens a confirmation modal when there are unsaved changes.
     * Otherwise, closes the configure-rates modal directly.
     */
    document.querySelectorAll('.configure-cancel-btn, .configure-close-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (configureHasChanges()) {
                bootstrap.Modal.getOrCreateInstance($('confirmCancelConfigureModal')).show();
            } else {
                bootstrap.Modal.getOrCreateInstance(configureRatesModal).hide();
            }
        });
    });

    /**
     * Handles add-rental cancel and close buttons.
     *
     * Opens a confirmation modal when there are unsaved changes.
     * Otherwise, closes the add-rental modal directly.
     */
    document.querySelectorAll('.rental-cancel-btn, .rental-close-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (rentalHasChanges()) {
                bootstrap.Modal.getOrCreateInstance($('confirmCancelRentalModal')).show();
            } else {
                allowRentalModalClose = true;
                bootstrap.Modal.getOrCreateInstance(addRentalModal).hide();
            }
        });
    });

    /**
     * Prevents the configure-rates modal from closing when unsaved
     * changes exist.
     */
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

    /**
     * Prevents the add-rental modal from closing when unsaved changes exist.
     *
     * If the modal is allowed to close programmatically, the close flag is
     * cleared and the modal closes normally. Otherwise, the user is shown a
     * confirmation modal before losing changes.
     */
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

    /**
     * Prevents the edit-event modal from closing when unsaved changes exist.
     */
    if ($('editEventModal')) {
        $('editEventModal').addEventListener('hide.bs.modal', (e) => {

            if (allowEditModalClose) {
                allowEditModalClose = false;
                return;
            }

            const confirmModalOpen =
                $('confirmCancelEditModal')?.classList.contains('show');

            if (confirmModalOpen) return;

            if (editDirty) {
                e.preventDefault();

                bootstrap.Modal
                    .getOrCreateInstance($('confirmCancelEditModal'))
                    .show();
            }
        });
    }

    /**
     * Prevents the related-event modal from closing when unsaved changes exist.
     */
    if ($('createRelatedModal')) {
        $('createRelatedModal').addEventListener('hide.bs.modal', (e) => {

            if (allowRelatedModalClose) {
                allowRelatedModalClose = false;
                return;
            }

            const confirmModalOpen =
                $('confirmCancelRelatedModal')?.classList.contains('show');

            if (confirmModalOpen) return;

            if (relatedDirty) {
                e.preventDefault();

                bootstrap.Modal
                    .getOrCreateInstance($('confirmCancelRelatedModal'))
                    .show();
            }
        });
    }

    /**
     * Prevents the customize-days modal from closing when unsaved changes exist.
     */
    if ($('customizeDaysModal')) {
        $('customizeDaysModal').addEventListener('hide.bs.modal', (e) => {

            if (allowCustomizeModalClose) {
                allowCustomizeModalClose = false;
                return;
            }

            const confirmModalOpen =
                $('confirmCancelCustomizeModal')?.classList.contains('show');

            if (confirmModalOpen) return;

            if (customizeDirty) {
                e.preventDefault();

                bootstrap.Modal
                    .getOrCreateInstance($('confirmCancelCustomizeModal'))
                    .show();
            }
        });
    }

    /**
     * Handles add-rental cancel and close buttons.
     *
     * If the rental form contains changes, a confirmation modal is shown.
     * Otherwise, the rental modal closes immediately.
     */
    document.querySelectorAll('.rental-cancel-btn, .rental-close-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (rentalHasChanges()) {
                bootstrap.Modal.getOrCreateInstance(confirmCancelRentalModal).show();
                return;
            }

            allowRentalModalClose = true;
            bootstrap.Modal.getOrCreateInstance(addRentalModal).hide();
        });
    });

    /**
     * Confirmation modal button and modal references for configure-rates
     * and add-rental cancel workflows.
     */
    const confirmCancelConfigureBtn = $('confirmCancelConfigureBtn');
    const confirmCancelRentalBtn = $('confirmCancelRentalBtn');
    const confirmCancelConfigureModal = $('confirmCancelConfigureModal');
    const confirmCancelRentalModal = $('confirmCancelRentalModal');

    /**
     * Confirms canceling configure-rates changes.
     *
     * Clears dirty state, allows the modal to close, resets the form,
     * closes the confirmation modal, and closes the configure-rates modal.
     */
    if (confirmCancelConfigureBtn && confirmCancelConfigureModal && configureRatesModal) {
        confirmCancelConfigureBtn.addEventListener('click', () => {
            configureDirty = false;
            allowConfigureModalClose = true;
            resetConfigureFormState();
            bootstrap.Modal.getOrCreateInstance(confirmCancelConfigureModal).hide();
            bootstrap.Modal.getOrCreateInstance(configureRatesModal).hide();
        });
    }

    /**
     * Confirms canceling add-rental changes.
     *
     * Clears dirty state, allows the modal to close, closes both modals,
     * and resets the rental form.
     */
    if (confirmCancelRentalBtn && confirmCancelRentalModal && addRentalModal) {
        confirmCancelRentalBtn.addEventListener('click', () => {
            rentalDirty = false;
            allowRentalModalClose = true;

            bootstrap.Modal.getOrCreateInstance(confirmCancelRentalModal).hide();
            bootstrap.Modal.getOrCreateInstance(addRentalModal).hide();

            resetRentalFormState();
        });
    }

    /**
     * Confirmation button references for edit, related-event, and
     * customize-days cancel flows.
     */
    const confirmCancelEditBtn = $('confirmCancelEditBtn');
    const confirmCancelRelatedBtn = $('confirmCancelRelatedBtn');
    const confirmCancelCustomizeBtn = $('confirmCancelCustomizeBtn');

    /**
     * Confirmation modal references for edit, related-event, and
     * customize-days cancel workflows.
     */
    const confirmCancelEditModal = $('confirmCancelEditModal');
    const confirmCancelRelatedModal = $('confirmCancelRelatedModal');
    const confirmCancelCustomizeModal = $('confirmCancelCustomizeModal');


    /**
     * Closes the edit-event modal when its cancel or close buttons are clicked.
     */
    document.querySelectorAll('.edit-close-btn, .edit-cancel-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            bootstrap.Modal.getOrCreateInstance($('editEventModal')).hide();
        });
    });

    /**
     * Closes the related-event modal when its cancel or close buttons are clicked.
     */
    document.querySelectorAll('.related-close-btn, .related-cancel-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            bootstrap.Modal.getOrCreateInstance($('createRelatedModal')).hide();
        });
    });

    /**
     * Closes the customize-days modal when its cancel or close buttons are clicked.
     */
    document.querySelectorAll('.customize-close-btn, .customize-cancel-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            bootstrap.Modal.getOrCreateInstance($('customizeDaysModal')).hide();
        });
    });

    /**
     * Confirms canceling edit-event changes.
     *
     * Clears dirty state, allows the edit modal to close, closes the
     * confirmation modal and edit modal, then resets edit state.
     */
    confirmCancelEditBtn?.addEventListener('click', () => {
        editDirty = false;
        allowEditModalClose = true;

        bootstrap.Modal.getOrCreateInstance(confirmCancelEditModal).hide();
        bootstrap.Modal.getOrCreateInstance($('editEventModal')).hide();

        resetEditEventState();
    });

    /**
     * Confirms canceling related-event changes.
     *
     * Clears dirty state, allows the related modal to close, closes the
     * confirmation modal and related modal, then resets related-event state.
     */
    confirmCancelRelatedBtn?.addEventListener('click', () => {
        relatedDirty = false;
        allowRelatedModalClose = true;

        bootstrap.Modal.getOrCreateInstance(confirmCancelRelatedModal).hide();
        bootstrap.Modal.getOrCreateInstance($('createRelatedModal')).hide();

        resetRelatedEventState();
    });

    /**
     * Confirms canceling customize-days changes.
     *
     * Clears dirty state, allows the customize modal to close, closes the
     * confirmation modal and customize modal, then resets customize state.
     */
    confirmCancelCustomizeBtn?.addEventListener('click', () => {
        customizeDirty = false;
        allowCustomizeModalClose = true;

        bootstrap.Modal.getOrCreateInstance(confirmCancelCustomizeModal).hide();
        bootstrap.Modal.getOrCreateInstance($('customizeDaysModal')).hide();

        resetCustomizeDaysState();
    });

    /**
     * Resets the configure-rates form every time the modal is opened.
     */
    if (configureRatesModal) {
        configureRatesModal.addEventListener('show.bs.modal', resetConfigureFormState);
    }

    /**
     * Resets and prepares the add-rental modal every time it is opened.
     */
    if (addRentalModal) {
        addRentalModal.addEventListener('show.bs.modal', () => {
            resetRentalFormState();
            servicesTouched = false;
            rentalDirty = false;
            allowRentalModalClose = false;

            toggleServicesError(false);
            updateRentalTimeOptions();
            updateDateRestrictions({
                periodType: rentalPeriodType,
                startDate: rentalStartDate,
                endDate: rentalEndDate,
                updateTimeOptions: updateRentalTimeOptions,
                updateSummary: calculateRentalEstimate
            });
            updateRentalSaveState();

            setTimeout(() => {
                rentalDirty = false;
            }, 0);
        });
    }

    /**
     * Builds an export URL using the currently selected table filters.
     *
     * The generated query string preserves the current search and filter
     * state for CSV and PDF downloads.
     *
     * @param {string} baseUrl - Base export endpoint URL.
     * @returns {string} Export URL with query parameters when filters exist.
     */
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
    /**
     * Handles CSV download clicks.
     *
     * Shows the download toast and redirects to the generated CSV export URL.
     */
    if (downloadCsvBtn) {
        downloadCsvBtn.addEventListener('click', (e) => {
            e.preventDefault();

            if (toasts.download) {
                toasts.download.show();
            }

            window.location.href = buildExportUrl(downloadCsvBtn.dataset.baseUrl);
        });
    }

    /**
     * Handles PDF download clicks.
     *
     * Shows the download toast and redirects to the generated PDF export URL.
     */
    if (downloadPdfBtn) {
        downloadPdfBtn.addEventListener('click', (e) => {
            e.preventDefault();

            if (toasts.download) {
                toasts.download.show();
            }

            window.location.href = buildExportUrl(downloadPdfBtn.dataset.baseUrl);
        });
    }

    /**
     * Initializes all dynamic page behaviors after setup.
     */
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
