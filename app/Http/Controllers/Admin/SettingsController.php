<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\CustomTaxRate;
use App\Models\Menu;
use App\Support\AdminMenuCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.settings', [
            'sections' => $this->sections($request),
        ]);
    }

    public function businessProfile(Request $request)
    {
        return view('admin.settings.business-profile', [
            'section' => $this->section($request, 'business-profile'),
            'backUrl' => route('admin.settings'),
            'profile' => AdminSetting::valuesForGroup('business_profile', $this->businessProfileDefaults()),
            'timezones' => $this->businessProfileTimezones(),
            'customTaxRates' => $this->customTaxRatePayload(),
            'canManageCustomTax' => $this->canManageCustomTax($request),
        ]);
    }

    public function updateBusinessProfile(Request $request)
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:120'],
            'legal_business_name' => ['nullable', 'string', 'max:160'],
            'dba_name' => ['nullable', 'string', 'max:120'],
            'hq_name' => ['nullable', 'string', 'max:120'],
            'business_phone' => ['nullable', 'string', 'max:32'],
            'business_email' => ['nullable', 'email:rfc', 'max:160'],
            'website' => ['nullable', 'url', 'max:255'],
            'business_address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:60'],
            'zip_code' => ['nullable', 'regex:/^\d{5}(-\d{4})?$/'],
            'country' => ['nullable', 'string', 'max:120'],
            'timezone' => ['required', 'timezone'],
            'default_tax_rate' => ['required', 'numeric', 'between:0,15'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ], [
            'zip_code.regex' => 'ZIP code must use a valid US ZIP format.',
        ]);

        $values = array_merge($this->businessProfileDefaults(), [
            'business_name' => trim((string) $validated['business_name']),
            'legal_business_name' => $this->nullableString($validated['legal_business_name'] ?? null),
            'dba_name' => $this->nullableString($validated['dba_name'] ?? null),
            'hq_name' => $this->nullableString($validated['hq_name'] ?? null),
            'business_phone' => $this->nullableString($validated['business_phone'] ?? null),
            'business_email' => $this->nullableString($validated['business_email'] ?? null),
            'website' => $this->nullableString($validated['website'] ?? null),
            'business_address' => $this->nullableString($validated['business_address'] ?? null),
            'city' => $this->nullableString($validated['city'] ?? null),
            'state' => $this->nullableString($validated['state'] ?? null),
            'zip_code' => $this->nullableString($validated['zip_code'] ?? null),
            'country' => $this->nullableString($validated['country'] ?? null),
            'timezone' => trim((string) $validated['timezone']),
            'default_tax_rate' => number_format((float) $validated['default_tax_rate'], 2, '.', ''),
            'admin_notes' => $this->nullableString($validated['admin_notes'] ?? null),
        ]);

        AdminSetting::storeGroupValues('business_profile', Arr::only($values, array_keys($this->businessProfileDefaults())));

        return redirect()
            ->route('admin.settings.business-profile')
            ->with('ok', 'Business profile updated.');
    }

    public function customTaxRates(Request $request): JsonResponse
    {
        return response()->json([
            'rates' => $this->customTaxRatePayload(),
            'can_manage' => $this->canManageCustomTax($request),
        ]);
    }

    public function storeCustomTaxRates(Request $request): JsonResponse
    {
        $this->authorizeCustomTaxManage($request);

        if ($request->has('rates')) {
            $rates = $this->validatedCustomTaxRows($request);
            $savedRates = DB::transaction(fn () => $this->syncCustomTaxRows($rates, $request));

            return response()->json([
                'ok' => true,
                'message' => 'Custom tax rates saved successfully.',
                'rates' => $savedRates,
            ]);
        }

        $validated = $request->validate([
            'city_name' => ['required', 'string', 'max:160'],
            'tax_rate' => ['required', 'numeric', 'between:0,100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $cityName = preg_replace('/\s+/', ' ', trim((string) $validated['city_name']));
        $cityKey = CustomTaxRate::cityKey($cityName);
        abort_if(CustomTaxRate::query()->where('city_key', $cityKey)->exists(), 422, 'City already has a custom tax rate.');

        $rate = CustomTaxRate::create([
            'city_name' => $cityName,
            'tax_rate' => number_format((float) $validated['tax_rate'], 2, '.', ''),
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Custom tax rate saved successfully.',
            'rate' => $this->customTaxRateRowPayload($rate),
        ], 201);
    }

    public function updateCustomTaxRate(Request $request, CustomTaxRate $customTaxRate): JsonResponse
    {
        $this->authorizeCustomTaxManage($request);

        $validated = $request->validate([
            'city_name' => ['required', 'string', 'max:160'],
            'tax_rate' => ['required', 'numeric', 'between:0,100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $cityName = preg_replace('/\s+/', ' ', trim((string) $validated['city_name']));
        $cityKey = CustomTaxRate::cityKey($cityName);
        abort_if(
            CustomTaxRate::query()
                ->where('city_key', $cityKey)
                ->whereKeyNot($customTaxRate->id)
                ->exists(),
            422,
            'City already has a custom tax rate.'
        );

        $customTaxRate->fill([
            'city_name' => $cityName,
            'tax_rate' => number_format((float) $validated['tax_rate'], 2, '.', ''),
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'updated_by' => $request->user()?->id,
        ])->save();

        return response()->json([
            'ok' => true,
            'message' => 'Custom tax rate saved successfully.',
            'rate' => $this->customTaxRateRowPayload($customTaxRate->fresh()),
        ]);
    }

    public function destroyCustomTaxRate(Request $request, CustomTaxRate $customTaxRate): JsonResponse
    {
        $this->authorizeCustomTaxManage($request);
        $customTaxRate->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Custom tax rate deleted.',
        ]);
    }

    public function reservationRules(Request $request)
    {
        return view('admin.settings.reservation-rules', [
            'section' => $this->section($request, 'reservation-rules'),
            'backUrl' => route('admin.settings'),
            'rules' => $this->reservationRulesValues(),
            'statusOptions' => $this->reservationStatusOptions(),
            'requiredFieldOptions' => $this->requiredBookingFieldOptions(),
        ]);
    }

    public function updateReservationRules(Request $request)
    {
        $allowedFields = array_keys($this->requiredBookingFieldOptions());

        $validated = $request->validate([
            'minimum_guests' => ['required', 'integer', 'min:1'],
            'maximum_guests' => ['required', 'integer', 'gt:minimum_guests'],
            'default_reservation_status' => ['required', 'string', 'in:' . implode(',', $this->reservationStatusOptions())],
            'reservation_cutoff_hours' => ['required', 'numeric', 'min:0'],
            'allow_same_day_booking' => ['required', 'in:0,1'],
            'allow_booking_without_deposit' => ['required', 'in:0,1'],
            'deposit_required' => ['required', 'in:0,1'],
            'deposit_percentage' => ['required', 'numeric', 'between:0,100'],
            'minimum_deposit_amount' => ['required', 'numeric', 'min:0'],
            'deposit_due_message' => ['nullable', 'string', 'max:2000'],
            'mark_confirmed_after_deposit' => ['required', 'in:0,1'],
            'base_zip_code' => ['required', 'regex:/^\d{5}(-\d{4})?$/'],
            'travel_fee_per_mile' => ['required', 'numeric', 'min:0'],
            'free_travel_radius_miles' => ['required', 'numeric', 'min:0'],
            'long_distance_threshold_miles' => ['required', 'numeric', 'min:0'],
            'long_distance_policy_note' => ['nullable', 'string', 'max:2000'],
            'auto_gratuity_enabled' => ['required', 'in:0,1'],
            'auto_gratuity_percentage' => ['required', 'numeric', 'between:0,100'],
            'auto_gratuity_minimum_guests' => ['required', 'integer', 'min:1'],
            'gratuity_label' => ['required', 'string', 'max:120'],
            'included_service_hours' => ['required', 'numeric', 'gt:0'],
            'extra_time_billing_increment_minutes' => ['required', 'integer', 'gt:0'],
            'extra_time_fee' => ['required', 'numeric', 'min:0'],
            'setup_time_note' => ['nullable', 'string', 'max:2000'],
            'late_customer_policy_note' => ['nullable', 'string', 'max:2000'],
            'required_booking_fields' => ['nullable', 'array'],
            'required_booking_fields.*' => ['string', 'in:' . implode(',', $allowedFields)],
            'reservation_received_message' => ['required', 'string', 'max:2000'],
            'deposit_required_message' => ['required', 'string', 'max:2000'],
            'confirmation_message_after_deposit' => ['required', 'string', 'max:2000'],
            'internal_admin_note' => ['nullable', 'string', 'max:2000'],
        ], [
            'base_zip_code.regex' => 'Base ZIP code must use a valid US ZIP format.',
            'maximum_guests.gt' => 'Maximum guests must be greater than minimum guests.',
        ]);

        $defaults = $this->reservationRulesDefaults();
        $values = array_merge($defaults, [
            'minimum_guests' => (string) $validated['minimum_guests'],
            'maximum_guests' => (string) $validated['maximum_guests'],
            'default_reservation_status' => trim((string) $validated['default_reservation_status']),
            'reservation_cutoff_hours' => $this->formatDecimal($validated['reservation_cutoff_hours'], 0),
            'allow_same_day_booking' => $this->booleanString($validated['allow_same_day_booking']),
            'allow_booking_without_deposit' => $this->booleanString($validated['allow_booking_without_deposit']),
            'deposit_required' => $this->booleanString($validated['deposit_required']),
            'deposit_percentage' => $this->formatDecimal($validated['deposit_percentage']),
            'minimum_deposit_amount' => $this->formatDecimal($validated['minimum_deposit_amount']),
            'deposit_due_message' => $this->nullableString($validated['deposit_due_message'] ?? null),
            'mark_confirmed_after_deposit' => $this->booleanString($validated['mark_confirmed_after_deposit']),
            'base_zip_code' => trim((string) $validated['base_zip_code']),
            'travel_fee_per_mile' => $this->formatDecimal($validated['travel_fee_per_mile']),
            'free_travel_radius_miles' => $this->formatDecimal($validated['free_travel_radius_miles'], 0),
            'long_distance_threshold_miles' => $this->formatDecimal($validated['long_distance_threshold_miles'], 0),
            'long_distance_policy_note' => $this->nullableString($validated['long_distance_policy_note'] ?? null),
            'auto_gratuity_enabled' => $this->booleanString($validated['auto_gratuity_enabled']),
            'auto_gratuity_percentage' => $this->formatDecimal($validated['auto_gratuity_percentage']),
            'auto_gratuity_minimum_guests' => (string) $validated['auto_gratuity_minimum_guests'],
            'gratuity_label' => trim((string) $validated['gratuity_label']),
            'included_service_hours' => $this->formatDecimal($validated['included_service_hours'], 0),
            'extra_time_billing_increment_minutes' => (string) $validated['extra_time_billing_increment_minutes'],
            'extra_time_fee' => $this->formatDecimal($validated['extra_time_fee']),
            'setup_time_note' => $this->nullableString($validated['setup_time_note'] ?? null),
            'late_customer_policy_note' => $this->nullableString($validated['late_customer_policy_note'] ?? null),
            'required_booking_fields' => $this->normalizeFieldList($validated['required_booking_fields'] ?? [], $allowedFields),
            'reservation_received_message' => trim((string) $validated['reservation_received_message']),
            'deposit_required_message' => trim((string) $validated['deposit_required_message']),
            'confirmation_message_after_deposit' => trim((string) $validated['confirmation_message_after_deposit']),
            'internal_admin_note' => $this->nullableString($validated['internal_admin_note'] ?? null),
        ]);

        AdminSetting::storeGroupValues('reservation_rules', Arr::only($values, array_keys($defaults)));

        return redirect()
            ->route('admin.settings.reservation-rules')
            ->with('ok', 'Reservation rules updated.');
    }

    public function resetReservationRules()
    {
        AdminSetting::storeGroupValues('reservation_rules', $this->reservationRulesDefaults());

        return redirect()
            ->route('admin.settings.reservation-rules')
            ->with('ok', 'Reservation rules reset to recommended defaults.');
    }

    public function menuPricingRules(Request $request)
    {
        return view('admin.settings.menu-pricing-rules', [
            'section' => $this->section($request, 'menu-pricing-rules'),
            'backUrl' => route('admin.settings'),
            'rules' => $this->menuPricingRulesValues(),
            'categoryOptions' => $this->menuCategoryOptions(),
            'approvalRoleOptions' => $this->menuApprovalRoleOptions(),
            'approvalStatusOptions' => $this->menuApprovalStatusOptions(),
            'bulkPreview' => session('menuBulkPreview'),
        ]);
    }

    public function updateMenuPricingRules(Request $request)
    {
        $categoryKeys = array_keys($this->menuCategoryOptions());
        $roleKeys = array_keys($this->menuApprovalRoleOptions());
        $statusKeys = array_keys($this->menuApprovalStatusOptions());

        $validator = Validator::make($request->all(), [
            'enable_menu_price_editing' => ['required', 'in:0,1'],
            'require_manager_approval_for_price_changes' => ['required', 'in:0,1'],
            'allow_category_editing' => ['required', 'in:0,1'],
            'allow_item_availability_changes' => ['required', 'in:0,1'],
            'allow_deleting_menu_items' => ['required', 'in:0,1'],
            'require_confirmation_before_deleting_item' => ['required', 'in:0,1'],
            'active_menu_categories' => ['nullable', 'array'],
            'active_menu_categories.*' => ['string', 'in:' . implode(',', $categoryKeys)],
            'hide_unavailable_items_from_booking_forms' => ['required', 'in:0,1'],
            'show_unavailable_items_in_admin' => ['required', 'in:0,1'],
            'allow_temporary_unavailable_status' => ['required', 'in:0,1'],
            'allow_seasonal_items' => ['required', 'in:0,1'],
            'require_unavailable_reason' => ['required', 'in:0,1'],
            'require_reason_for_price_changes' => ['required', 'in:0,1'],
            'store_price_change_history' => ['required', 'in:0,1'],
            'notify_admin_when_price_changes' => ['required', 'in:0,1'],
            'lock_prices_after_invoice_is_created' => ['required', 'in:0,1'],
            'lock_prices_after_reservation_is_confirmed' => ['required', 'in:0,1'],
            'allow_custom_price_override' => ['required', 'in:0,1'],
            'require_manager_approval_for_custom_price_override' => ['required', 'in:0,1'],
            'bulk_increase_type' => ['required', 'string', 'in:fixed,percentage'],
            'bulk_apply_to_category' => ['nullable', 'string', 'in:all,' . implode(',', $categoryKeys)],
            'bulk_price_amount' => ['nullable', 'numeric', 'gt:0'],
            'preview_changes_before_applying' => ['required', 'in:0,1'],
            'require_final_confirmation' => ['required', 'in:0,1'],
            'enable_catering_menu' => ['required', 'in:0,1'],
            'allow_per_person_pricing' => ['required', 'in:0,1'],
            'allow_add_on_pricing' => ['required', 'in:0,1'],
            'allow_package_pricing' => ['required', 'in:0,1'],
            'show_menu_item_descriptions' => ['required', 'in:0,1'],
            'show_menu_item_images' => ['required', 'in:0,1'],
            'price_changes_require_approval_from_role' => ['required', 'string', 'in:' . implode(',', $roleKeys)],
            'menu_item_deletion_requires_approval_from_role' => ['required', 'string', 'in:' . implode(',', $roleKeys)],
            'new_menu_item_requires_approval' => ['required', 'in:0,1'],
            'approval_status_options' => ['nullable', 'array'],
            'approval_status_options.*' => ['string', 'in:' . implode(',', $statusKeys)],
            'approval_note_field' => ['nullable', 'string', 'max:2000'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if ((string) $request->input('bulk_increase_type') === 'percentage') {
                $amount = $request->input('bulk_price_amount');
                if ($amount !== null && $amount !== '' && (float) $amount > 100) {
                    $validator->errors()->add('bulk_price_amount', 'Percentage bulk update amount must be between 0 and 100.');
                }
            }
        });

        $validated = $validator->validate();

        $defaults = $this->menuPricingRulesDefaults();
        $values = array_merge($defaults, [
            'enable_menu_price_editing' => $this->booleanString($validated['enable_menu_price_editing']),
            'require_manager_approval_for_price_changes' => $this->booleanString($validated['require_manager_approval_for_price_changes']),
            'allow_category_editing' => $this->booleanString($validated['allow_category_editing']),
            'allow_item_availability_changes' => $this->booleanString($validated['allow_item_availability_changes']),
            'allow_deleting_menu_items' => $this->booleanString($validated['allow_deleting_menu_items']),
            'require_confirmation_before_deleting_item' => $this->booleanString($validated['require_confirmation_before_deleting_item']),
            'active_menu_categories' => $this->normalizeFieldList($validated['active_menu_categories'] ?? [], $categoryKeys),
            'hide_unavailable_items_from_booking_forms' => $this->booleanString($validated['hide_unavailable_items_from_booking_forms']),
            'show_unavailable_items_in_admin' => $this->booleanString($validated['show_unavailable_items_in_admin']),
            'allow_temporary_unavailable_status' => $this->booleanString($validated['allow_temporary_unavailable_status']),
            'allow_seasonal_items' => $this->booleanString($validated['allow_seasonal_items']),
            'require_unavailable_reason' => $this->booleanString($validated['require_unavailable_reason']),
            'require_reason_for_price_changes' => $this->booleanString($validated['require_reason_for_price_changes']),
            'store_price_change_history' => $this->booleanString($validated['store_price_change_history']),
            'notify_admin_when_price_changes' => $this->booleanString($validated['notify_admin_when_price_changes']),
            'lock_prices_after_invoice_is_created' => $this->booleanString($validated['lock_prices_after_invoice_is_created']),
            'lock_prices_after_reservation_is_confirmed' => $this->booleanString($validated['lock_prices_after_reservation_is_confirmed']),
            'allow_custom_price_override' => $this->booleanString($validated['allow_custom_price_override']),
            'require_manager_approval_for_custom_price_override' => $this->booleanString($validated['require_manager_approval_for_custom_price_override']),
            'bulk_increase_type' => trim((string) $validated['bulk_increase_type']),
            'bulk_apply_to_category' => $this->nullableString($validated['bulk_apply_to_category'] ?? null) ?: 'all',
            'bulk_price_amount' => isset($validated['bulk_price_amount']) && $validated['bulk_price_amount'] !== null && $validated['bulk_price_amount'] !== ''
                ? $this->formatDecimal($validated['bulk_price_amount'])
                : null,
            'preview_changes_before_applying' => $this->booleanString($validated['preview_changes_before_applying']),
            'require_final_confirmation' => $this->booleanString($validated['require_final_confirmation']),
            'enable_catering_menu' => $this->booleanString($validated['enable_catering_menu']),
            'allow_per_person_pricing' => $this->booleanString($validated['allow_per_person_pricing']),
            'allow_add_on_pricing' => $this->booleanString($validated['allow_add_on_pricing']),
            'allow_package_pricing' => $this->booleanString($validated['allow_package_pricing']),
            'show_menu_item_descriptions' => $this->booleanString($validated['show_menu_item_descriptions']),
            'show_menu_item_images' => $this->booleanString($validated['show_menu_item_images']),
            'price_changes_require_approval_from_role' => trim((string) $validated['price_changes_require_approval_from_role']),
            'menu_item_deletion_requires_approval_from_role' => trim((string) $validated['menu_item_deletion_requires_approval_from_role']),
            'new_menu_item_requires_approval' => $this->booleanString($validated['new_menu_item_requires_approval']),
            'approval_status_options' => $this->normalizeFieldList($validated['approval_status_options'] ?? [], $statusKeys),
            'approval_note_field' => $this->nullableString($validated['approval_note_field'] ?? null),
        ]);

        AdminSetting::storeGroupValues('menu_pricing_rules', Arr::only($values, array_keys($defaults)));

        return redirect()
            ->route('admin.settings.menu-pricing-rules')
            ->with('ok', 'Menu & pricing rules saved. No live menu prices were changed.');
    }

    public function resetMenuPricingRules()
    {
        AdminSetting::storeGroupValues('menu_pricing_rules', $this->menuPricingRulesDefaults());

        return redirect()
            ->route('admin.settings.menu-pricing-rules')
            ->with('ok', 'Menu & pricing rules reset to recommended defaults.');
    }

    public function previewMenuPricingBulkUpdate(Request $request)
    {
        $payload = $this->validateBulkPriceUpdatePayload($request);
        $preview = $this->buildBulkPricePreview($payload);

        if (count($preview['rows']) === 0) {
            return back()
                ->withErrors(['bulk_apply_to_category' => 'No menu items were found for the selected category.'])
                ->withInput();
        }

        return redirect()
            ->route('admin.settings.menu-pricing-rules')
            ->withInput()
            ->with('menuBulkPreview', $preview)
            ->with('ok', 'Preview generated. No live menu prices were changed.');
    }

    public function applyMenuPricingBulkUpdate(Request $request)
    {
        $payload = $this->validateBulkPriceUpdatePayload($request, true);
        $preview = $this->buildBulkPricePreview($payload);

        if (count($preview['rows']) === 0) {
            return back()
                ->withErrors(['bulk_apply_to_category' => 'No menu items were found for the selected category.'])
                ->withInput();
        }

        $updated = $this->applyBulkPriceUpdates($payload, $preview);

        return redirect()
            ->route('admin.settings.menu-pricing-rules')
            ->with('ok', $updated > 0
                ? "Bulk price update applied successfully. {$updated} menu items were updated."
                : 'No live menu prices were changed.');
    }

    public function show(Request $request, string $section)
    {
        $sections = collect($this->sections($request))->keyBy('key');
        abort_unless($sections->has($section), 404);

        return view('admin.settings.placeholder', [
            'section' => $sections->get($section),
            'backUrl' => route('admin.settings'),
        ]);
    }

    private function sections(Request $request): array
    {
        $user = $request->user();

        $canViewReservations = (bool) ($user?->hasPermission('reservations.view') ?? false);
        $canViewMenu = (bool) ($user?->hasPermission('menu.view') ?? false);
        $canViewSchedule = (bool) ($user?->hasPermission('schedule.view') ?? false);
        $canViewTeam = (bool) ($user?->hasPermission('team.view') ?? false);
        $canManageTeam = (bool) ($user?->hasPermission('team.manage') ?? false);
        $canViewTrash = (bool) ($user?->hasPermission('trash.view') ?? false);

        return [
            [
                'key' => 'business-profile',
                'category' => 'Business',
                'title' => 'Business Profile',
                'description' => 'Company identity, contact details, brand assets, and location defaults used across the admin panel.',
                'priority' => true,
                'status' => [
                    ['label' => 'High Priority', 'tone' => 'accent'],
                    ['label' => 'Active', 'tone' => 'success'],
                    ['label' => 'Structure Ready', 'tone' => 'dark'],
                ],
                'highlights' => ['Business name', 'Main location / HQ', 'Business phone', 'Default tax rate'],
                'fields' => [
                    'Business name',
                    'Main location / HQ',
                    'Business phone',
                    'Business email',
                    'Business address',
                    'Logo placeholder',
                    'Time zone',
                    'Default tax rate',
                ],
                'primaryAction' => ['label' => 'Open Profile Workspace', 'url' => route('admin.settings.business-profile')],
                'secondaryAction' => null,
                'liveLinks' => [],
            ],
            [
                'key' => 'reservation-rules',
                'category' => 'Operations',
                'title' => 'Reservation Rules',
                'description' => 'Booking constraints, customer intake defaults, and event-handling rules that should be centralized before scale.',
                'priority' => true,
                'status' => [
                    ['label' => 'High Priority', 'tone' => 'accent'],
                    ['label' => 'Active', 'tone' => 'success'],
                    ['label' => 'Policy Ready', 'tone' => 'dark'],
                ],
                'highlights' => ['Minimum guests', 'Deposit percentage', 'Reservation cutoff time', 'Confirmation message'],
                'fields' => [
                    'Minimum guests',
                    'Maximum guests',
                    'Deposit percentage',
                    'Default reservation status',
                    'Reservation cutoff time',
                    'Extra time fee',
                    'Required booking fields',
                    'Confirmation message',
                ],
                'primaryAction' => ['label' => 'Open Reservation Rules', 'url' => route('admin.settings.reservation-rules')],
                'secondaryAction' => $canViewReservations ? ['label' => 'Open Reservations', 'url' => route('admin.reservations')] : null,
                'liveLinks' => [],
            ],
            [
                'key' => 'invoice-settings',
                'category' => 'Billing',
                'title' => 'Invoice Settings',
                'description' => 'Numbering, invoice messaging, display toggles, and payment automation defaults for admin-issued invoices.',
                'priority' => true,
                'status' => [
                    ['label' => 'High Priority', 'tone' => 'accent'],
                    ['label' => 'Coming Soon', 'tone' => 'muted'],
                    ['label' => 'Billing Policy', 'tone' => 'dark'],
                ],
                'highlights' => ['Invoice prefix', 'Next invoice number', 'Show tax', 'Auto mark paid at zero balance'],
                'fields' => [
                    'Invoice prefix',
                    'Next invoice number',
                    'Default invoice notes',
                    'Terms and conditions',
                    'Show tax',
                    'Show travel fee',
                    'Show gratuity',
                    'Auto mark paid when balance is zero',
                    'Invoice footer message',
                ],
                'primaryAction' => ['label' => 'Open Invoice Settings', 'url' => route('admin.settings.section', ['section' => 'invoice-settings'])],
                'secondaryAction' => $canViewReservations ? ['label' => 'Open Invoices', 'url' => route('admin.invoices')] : null,
                'liveLinks' => [],
            ],
            [
                'key' => 'menu-pricing-rules',
                'category' => 'Catalog',
                'title' => 'Menu & Pricing Rules',
                'description' => 'Central pricing policy controls for menu availability, approvals, and future bulk pricing tools.',
                'priority' => false,
                'status' => [
                    ['label' => 'Active', 'tone' => 'success'],
                    ['label' => 'Manager Review', 'tone' => 'warning'],
                    ['label' => 'Policy Ready', 'tone' => 'dark'],
                ],
                'highlights' => ['Menu price editing', 'Active categories', 'Hide unavailable items', 'Approval for price changes'],
                'fields' => [
                    'Enable menu price editing',
                    'Global price increase tool placeholder',
                    'Active menu categories',
                    'Hide unavailable items',
                    'Require manager approval for price changes',
                    'Restaurant menu rules',
                    'Catering menu rules',
                ],
                'primaryAction' => ['label' => 'Open Pricing Rules', 'url' => route('admin.settings.menu-pricing-rules')],
                'secondaryAction' => $canViewMenu ? ['label' => 'Open Menu', 'url' => route('admin.menu')] : null,
                'liveLinks' => [],
            ],
            [
                'key' => 'staff-scheduling',
                'category' => 'Staffing',
                'title' => 'Staff & Scheduling',
                'description' => 'Dispatch rules, staffing limits, and shift policy placeholders tied to daily scheduling workflows.',
                'priority' => false,
                'status' => [
                    ['label' => 'Coming Soon', 'tone' => 'muted'],
                    ['label' => 'Schedule Linked', 'tone' => 'success'],
                ],
                'highlights' => ['Default shift length', 'Max events per chef', 'Chef assignment rules', 'Notification settings'],
                'fields' => [
                    'Default shift length',
                    'Break rules',
                    'Max events per chef per day',
                    'Chef assignment rules',
                    'Staff booking settings',
                    'Manager approval for schedule changes',
                    'Staff notification settings',
                ],
                'primaryAction' => ['label' => 'Open Staffing Rules', 'url' => route('admin.settings.section', ['section' => 'staff-scheduling'])],
                'secondaryAction' => $canViewSchedule ? ['label' => 'Open Schedule', 'url' => route('admin.schedule.index')] : null,
                'liveLinks' => [],
            ],
            [
                'key' => 'roles-permissions',
                'category' => 'Security',
                'title' => 'Access Control & Permissions',
                'description' => 'Manage admin roles, module permissions, and access levels using the live Access Control workspace.',
                'priority' => true,
                'status' => [
                    ['label' => 'High Priority', 'tone' => 'accent'],
                    ['label' => 'Live', 'tone' => 'success'],
                    ['label' => 'Admin Access', 'tone' => 'dark'],
                ],
                'highlights' => ['Roles', 'Module permissions', 'Approval permission', 'Export permission'],
                'fields' => [
                    'Roles',
                    'Module permissions',
                    'Admin access',
                    'View/create/edit/delete permissions',
                    'Export permission',
                    'Approval permission',
                ],
                'primaryAction' => $canManageTeam
                    ? ['label' => 'Open Access Control', 'url' => route('admin.team.permissions')]
                    : null,
                'secondaryAction' => $canViewTeam ? ['label' => 'Open Team Directory', 'url' => route('admin.team.index')] : null,
                'liveLinks' => array_values(array_filter([
                    $canManageTeam ? ['label' => 'Access Control', 'url' => route('admin.team.permissions')] : null,
                    $canViewTeam ? ['label' => 'Team Directory', 'url' => route('admin.team.index')] : null,
                ])),
            ],
            [
                'key' => 'security',
                'category' => 'Security',
                'title' => 'Security',
                'description' => 'Credential policy, authentication hardening, and audit placeholders for admin account protection.',
                'priority' => true,
                'status' => [
                    ['label' => 'High Priority', 'tone' => 'accent'],
                    ['label' => 'Coming Soon', 'tone' => 'muted'],
                    ['label' => 'Admin Only', 'tone' => 'dark'],
                ],
                'highlights' => ['Change password', 'Strong password rules', 'Failed login attempts', 'Account lockout settings'],
                'fields' => [
                    'Change password',
                    'Strong password requirement',
                    'Two-factor authentication placeholder',
                    'Login history placeholder',
                    'Active sessions placeholder',
                    'Failed login attempts',
                    'Account lockout settings',
                    'Admin activity log link or placeholder',
                ],
                'primaryAction' => ['label' => 'Open Security Workspace', 'url' => route('admin.settings.section', ['section' => 'security'])],
                'secondaryAction' => null,
                'liveLinks' => [],
            ],
            [
                'key' => 'system-tools',
                'category' => 'System',
                'title' => 'System Tools',
                'description' => 'Operational maintenance tools, app health references, and deployment-safe placeholders for future admin automation.',
                'priority' => false,
                'status' => [
                    ['label' => 'Coming Soon', 'tone' => 'muted'],
                    ['label' => 'Admin Only', 'tone' => 'dark'],
                ],
                'highlights' => ['App version', 'Maintenance mode', 'Clear cache', 'System status'],
                'fields' => [
                    'App version',
                    'Maintenance mode placeholder',
                    'Clear cache button placeholder',
                    'Backup database placeholder',
                    'Export data placeholder',
                    'Import data placeholder',
                    'Session timeout',
                    'System status',
                ],
                'primaryAction' => ['label' => 'Open System Tools', 'url' => route('admin.settings.section', ['section' => 'system-tools'])],
                'secondaryAction' => null,
                'liveLinks' => [],
            ],
            [
                'key' => 'trash-restore-rules',
                'category' => 'Retention',
                'title' => 'Trash & Restore Rules',
                'description' => 'Deletion retention policy, restore behavior, and permanent-delete guardrails tied to the live Trash module.',
                'priority' => false,
                'status' => [
                    ['label' => 'Policy Placeholder', 'tone' => 'warning'],
                    ['label' => 'Trash Linked', 'tone' => 'success'],
                ],
                'highlights' => ['Retention period', 'Allow restore', 'Permanent delete restriction', 'Delete confirmation'],
                'fields' => [
                    'Keep deleted records for 30/60/90 days',
                    'Allow restore',
                    'Only admin can permanently delete',
                    'Log who deleted records',
                    'Require confirmation before permanent delete',
                    'Link to Trash module',
                ],
                'primaryAction' => ['label' => 'Open Trash Policy', 'url' => route('admin.settings.section', ['section' => 'trash-restore-rules'])],
                'secondaryAction' => $canViewTrash ? ['label' => 'Open Trash', 'url' => route('admin.trash')] : null,
                'liveLinks' => array_values(array_filter([
                    $canViewTrash ? ['label' => 'Trash Module', 'url' => route('admin.trash')] : null,
                ])),
            ],
        ];
    }

    private function section(Request $request, string $key): array
    {
        return collect($this->sections($request))->keyBy('key')->get($key, []);
    }

    private function businessProfileDefaults(): array
    {
        return [
            'business_name' => 'Hibachi Catering',
            'legal_business_name' => null,
            'dba_name' => 'Hibachi Catering',
            'hq_name' => 'Corona HQ',
            'business_phone' => null,
            'business_email' => null,
            'website' => null,
            'business_address' => null,
            'city' => null,
            'state' => 'CA',
            'zip_code' => null,
            'country' => 'United States',
            'timezone' => 'America/Los_Angeles',
            'default_tax_rate' => '10.25',
            'admin_notes' => null,
        ];
    }

    private function customTaxRatePayload(): array
    {
        return CustomTaxRate::query()
            ->orderBy('city_name')
            ->get()
            ->map(fn (CustomTaxRate $rate) => $this->customTaxRateRowPayload($rate))
            ->values()
            ->all();
    }

    private function customTaxRateRowPayload(CustomTaxRate $rate): array
    {
        return [
            'id' => $rate->id,
            'city_name' => $rate->city_name,
            'tax_rate' => number_format((float) $rate->tax_rate, 2, '.', ''),
            'is_active' => (bool) $rate->is_active,
        ];
    }

    private function canManageCustomTax(Request $request): bool
    {
        return (bool) $request->user()?->hasRole(['owner', 'admin']);
    }

    private function authorizeCustomTaxManage(Request $request): void
    {
        abort_unless($this->canManageCustomTax($request), 403);
    }

    private function validatedCustomTaxRows(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'rates' => ['required', 'array', 'max:1000'],
            'rates.*.id' => ['nullable', 'integer', 'exists:custom_tax_rates,id'],
            'rates.*.city_name' => ['required', 'string', 'max:160'],
            'rates.*.tax_rate' => ['required', 'numeric', 'between:0,100'],
            'rates.*.is_active' => ['nullable', 'boolean'],
        ], [
            'rates.max' => 'Custom Tax supports up to 1000 cities.',
            'rates.*.city_name.required' => 'City Name is required for every custom tax row.',
            'rates.*.tax_rate.required' => 'Tax % is required for every custom tax row.',
            'rates.*.tax_rate.numeric' => 'Tax % must be a valid decimal number.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $seen = [];
            foreach ((array) $request->input('rates', []) as $index => $row) {
                $cityName = preg_replace('/\s+/', ' ', trim((string) ($row['city_name'] ?? '')));
                if ($cityName === '') {
                    continue;
                }
                $cityKey = CustomTaxRate::cityKey($cityName);
                if (isset($seen[$cityKey])) {
                    $validator->errors()->add("rates.{$index}.city_name", 'City Name cannot be duplicated.');
                }
                $seen[$cityKey] = true;

                $existing = CustomTaxRate::query()
                    ->where('city_key', $cityKey)
                    ->when(!empty($row['id']), fn ($query) => $query->whereKeyNot((int) $row['id']))
                    ->first();

                if ($existing) {
                    $validator->errors()->add("rates.{$index}.city_name", 'City already has a custom tax rate.');
                }
            }
        });

        return $validator->validate()['rates'];
    }

    private function syncCustomTaxRows(array $rows, Request $request): array
    {
        $keptIds = [];

        foreach ($rows as $row) {
            $cityName = preg_replace('/\s+/', ' ', trim((string) $row['city_name']));
            $payload = [
                'city_name' => $cityName,
                'tax_rate' => number_format((float) $row['tax_rate'], 2, '.', ''),
                'is_active' => (bool) ($row['is_active'] ?? true),
                'updated_by' => $request->user()?->id,
            ];

            if (!empty($row['id'])) {
                $rate = CustomTaxRate::query()->findOrFail((int) $row['id']);
                $rate->fill($payload)->save();
            } else {
                $rate = CustomTaxRate::create($payload + [
                    'created_by' => $request->user()?->id,
                ]);
            }

            $keptIds[] = (int) $rate->id;
        }

        CustomTaxRate::query()
            ->when(!empty($keptIds), fn ($query) => $query->whereNotIn('id', $keptIds))
            ->delete();

        return $this->customTaxRatePayload();
    }

    private function reservationRulesValues(): array
    {
        $defaults = $this->reservationRulesDefaults();
        $values = AdminSetting::valuesForGroup('reservation_rules', $defaults);
        $values['required_booking_fields'] = $this->parseFieldList($values['required_booking_fields'] ?? '');

        return $values;
    }

    private function reservationRulesDefaults(): array
    {
        return [
            'minimum_guests' => '10',
            'maximum_guests' => '1000',
            'default_reservation_status' => 'Pending',
            'reservation_cutoff_hours' => '24',
            'allow_same_day_booking' => '0',
            'allow_booking_without_deposit' => '0',
            'deposit_required' => '1',
            'deposit_percentage' => '35.00',
            'minimum_deposit_amount' => '500.00',
            'deposit_due_message' => 'A deposit is required to secure your reservation. Your event is not confirmed until the deposit has been received.',
            'mark_confirmed_after_deposit' => '1',
            'base_zip_code' => '92562',
            'travel_fee_per_mile' => '3.00',
            'free_travel_radius_miles' => '0',
            'long_distance_threshold_miles' => '200',
            'long_distance_policy_note' => 'Events over 200 miles may require round-trip travel fees, hotel, and additional travel costs.',
            'auto_gratuity_enabled' => '1',
            'auto_gratuity_percentage' => '18.00',
            'auto_gratuity_minimum_guests' => '30',
            'gratuity_label' => 'Service Charge / Gratuity',
            'included_service_hours' => '3',
            'extra_time_billing_increment_minutes' => '30',
            'extra_time_fee' => '50.00',
            'setup_time_note' => 'Standard service includes setup, cooking service, and cleanup time.',
            'late_customer_policy_note' => 'Additional time may be charged if the event is delayed due to customer readiness or access issues.',
            'required_booking_fields' => implode(',', [
                'customer_name',
                'phone',
                'email',
                'event_date',
                'event_time',
                'guest_count',
                'event_address',
                'zip_code',
                'menu_selection',
            ]),
            'reservation_received_message' => 'Thank you. Your reservation request has been received. Our team will review availability and contact you within 24 hours.',
            'deposit_required_message' => 'A deposit is required to secure your reservation. Your event is not confirmed until the deposit has been received.',
            'confirmation_message_after_deposit' => 'Your reservation is confirmed. Thank you for choosing Hibachi Catering.',
            'internal_admin_note' => null,
        ];
    }

    private function reservationStatusOptions(): array
    {
        return ['Pending', 'Confirmed', 'Deposit Paid', 'Paid', 'Cancelled', 'Completed'];
    }

    private function menuPricingRulesValues(): array
    {
        $defaults = $this->menuPricingRulesDefaults();
        $values = AdminSetting::valuesForGroup('menu_pricing_rules', $defaults);
        $values['active_menu_categories'] = $this->parseFieldList($values['active_menu_categories'] ?? '');
        $values['approval_status_options'] = $this->parseFieldList($values['approval_status_options'] ?? '');

        return $values;
    }

    private function menuPricingRulesDefaults(): array
    {
        $categories = array_keys($this->menuCategoryOptions());

        return [
            'enable_menu_price_editing' => '1',
            'require_manager_approval_for_price_changes' => '1',
            'allow_category_editing' => '1',
            'allow_item_availability_changes' => '1',
            'allow_deleting_menu_items' => '0',
            'require_confirmation_before_deleting_item' => '1',
            'active_menu_categories' => implode(',', $categories),
            'hide_unavailable_items_from_booking_forms' => '1',
            'show_unavailable_items_in_admin' => '1',
            'allow_temporary_unavailable_status' => '1',
            'allow_seasonal_items' => '1',
            'require_unavailable_reason' => '1',
            'require_reason_for_price_changes' => '1',
            'store_price_change_history' => '1',
            'notify_admin_when_price_changes' => '1',
            'lock_prices_after_invoice_is_created' => '1',
            'lock_prices_after_reservation_is_confirmed' => '1',
            'allow_custom_price_override' => '1',
            'require_manager_approval_for_custom_price_override' => '1',
            'bulk_increase_type' => 'percentage',
            'bulk_apply_to_category' => 'all',
            'bulk_price_amount' => null,
            'preview_changes_before_applying' => '1',
            'require_final_confirmation' => '1',
            'enable_catering_menu' => '1',
            'allow_per_person_pricing' => '1',
            'allow_add_on_pricing' => '1',
            'allow_package_pricing' => '1',
            'show_menu_item_descriptions' => '1',
            'show_menu_item_images' => '1',
            'price_changes_require_approval_from_role' => 'admin',
            'menu_item_deletion_requires_approval_from_role' => 'admin',
            'new_menu_item_requires_approval' => '1',
            'approval_status_options' => implode(',', ['draft', 'pending_review', 'approved', 'rejected']),
            'approval_note_field' => null,
        ];
    }

    private function menuCategoryOptions(): array
    {
        $categories = array_keys(app(AdminMenuCatalog::class)->grouped());

        return collect($categories)
            ->filter(fn ($category) => trim((string) $category) !== '')
            ->mapWithKeys(fn ($category) => [$category => $category])
            ->all();
    }

    private function menuApprovalRoleOptions(): array
    {
        return collect(array_keys(config('permissions.roles', [])))
            ->filter(fn ($role) => in_array($role, ['owner', 'admin', 'manager', 'office'], true))
            ->mapWithKeys(fn ($role) => [$role => ucfirst($role)])
            ->all();
    }

    private function menuApprovalStatusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'pending_review' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];
    }

    private function requiredBookingFieldOptions(): array
    {
        return [
            'customer_name' => 'Customer name',
            'phone' => 'Phone',
            'email' => 'Email',
            'event_date' => 'Event date',
            'event_time' => 'Event time',
            'guest_count' => 'Guest count',
            'event_address' => 'Event address',
            'zip_code' => 'ZIP code',
            'menu_selection' => 'Menu selection',
            'notes' => 'Notes',
        ];
    }

    private function businessProfileTimezones(): array
    {
        return [
            'America/Los_Angeles' => 'Pacific Time (Los Angeles)',
            'America/Denver' => 'Mountain Time (Denver)',
            'America/Chicago' => 'Central Time (Chicago)',
            'America/New_York' => 'Eastern Time (New York)',
            'Pacific/Honolulu' => 'Hawaii Time (Honolulu)',
            'UTC' => 'UTC',
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function booleanString(mixed $value): string
    {
        return (string) ((string) $value === '1' ? '1' : '0');
    }

    private function formatDecimal(mixed $value, int $decimals = 2): string
    {
        return number_format((float) $value, $decimals, '.', '');
    }

    private function normalizeFieldList(array $values, array $allowed): string
    {
        $normalized = array_values(array_unique(array_filter(array_map(
            fn ($value) => (string) $value,
            $values
        ), fn ($value) => in_array($value, $allowed, true))));

        return implode(',', $normalized);
    }

    private function parseFieldList(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    private function validateBulkPriceUpdatePayload(Request $request, bool $requireConfirmation = false): array
    {
        $categoryKeys = array_keys($this->menuCategoryOptions());

        $rules = [
            'bulk_increase_type' => ['required', 'string', 'in:fixed,percentage'],
            'bulk_apply_to_category' => ['required', 'string', 'in:all,' . implode(',', $categoryKeys)],
            'bulk_price_amount' => ['required', 'numeric', 'gt:0'],
            'preview_changes_before_applying' => ['required', 'in:1'],
            'require_final_confirmation' => ['required', 'in:1'],
        ];

        if ($requireConfirmation) {
            $rules['bulk_apply_confirmation'] = ['accepted'];
        }

        $validated = $request->validate($rules, [
            'bulk_apply_confirmation.accepted' => 'You must confirm before applying live price updates.',
        ]);

        if (($validated['bulk_increase_type'] ?? 'fixed') === 'percentage' && (float) $validated['bulk_price_amount'] > 100) {
            Validator::make([], [])->after(function ($validator) {
                $validator->errors()->add('bulk_price_amount', 'Percentage amount must be between 0 and 100.');
            })->validate();
        }

        return $validated;
    }

    private function buildBulkPricePreview(array $payload): array
    {
        $catalog = app(AdminMenuCatalog::class)->grouped();
        $selectedCategory = (string) ($payload['bulk_apply_to_category'] ?? 'all');
        $categories = $selectedCategory === 'all'
            ? array_keys($catalog)
            : [$selectedCategory];

        $amount = round((float) ($payload['bulk_price_amount'] ?? 0), 2);
        $type = (string) ($payload['bulk_increase_type'] ?? 'fixed');
        $rows = [];

        foreach ($categories as $category) {
            foreach ((array) ($catalog[$category] ?? []) as $row) {
                $currentPrice = round((float) ($row['price'] ?? 0), 2);
                $difference = $type === 'percentage'
                    ? round($currentPrice * ($amount / 100), 2)
                    : $amount;
                $newPrice = round($currentPrice + $difference, 2);

                $rows[] = [
                    'category' => $category,
                    'key' => (string) ($row['key'] ?? ''),
                    'name' => (string) ($row['name'] ?? ''),
                    'current_price' => number_format($currentPrice, 2, '.', ''),
                    'new_price' => number_format($newPrice, 2, '.', ''),
                    'difference' => ($difference >= 0 ? '+' : '') . number_format($difference, 2, '.', ''),
                    'status' => 'Pending',
                ];
            }
        }

        return [
            'type' => $type,
            'category' => $selectedCategory,
            'amount' => number_format($amount, 2, '.', ''),
            'rows' => $rows,
            'count' => count($rows),
        ];
    }

    private function applyBulkPriceUpdates(array $payload, array $preview): int
    {
        $selectedCategory = (string) ($payload['bulk_apply_to_category'] ?? 'all');
        $type = (string) ($payload['bulk_increase_type'] ?? 'fixed');
        $amount = round((float) ($payload['bulk_price_amount'] ?? 0), 2);

        if (Schema::hasTable('menus') && Menu::query()->exists()) {
            $query = Menu::query();

            if (Schema::hasColumn('menus', 'is_active')) {
                $query->where('is_active', true);
            }

            if ($selectedCategory !== 'all') {
                $query->where('category', $selectedCategory);
            }

            $rows = $query->get();

            foreach ($rows as $row) {
                $currentPrice = round((float) $row->price, 2);
                $difference = $type === 'percentage'
                    ? round($currentPrice * ($amount / 100), 2)
                    : $amount;
                $row->price = round($currentPrice + $difference, 2);
                $row->save();
            }

            return $rows->count();
        }

        $grouped = app(AdminMenuCatalog::class)->grouped();
        $categories = $selectedCategory === 'all' ? array_keys($grouped) : [$selectedCategory];
        $updated = 0;

        foreach ($categories as $category) {
            foreach ((array) ($grouped[$category] ?? []) as $index => $row) {
                $currentPrice = round((float) ($row['price'] ?? 0), 2);
                $difference = $type === 'percentage'
                    ? round($currentPrice * ($amount / 100), 2)
                    : $amount;
                $grouped[$category][$index]['price'] = round($currentPrice + $difference, 2);
                $updated++;
            }
        }

        if ($updated > 0) {
            app(AdminMenuCatalog::class)->replaceFromGrouped($grouped);
        }

        return $updated;
    }
}
