<?php

namespace App\Http\Requests\Admin\Team;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamMemberRequest extends FormRequest
{
    /**
     * Sentinel value the form sends when the user wants to keep the existing password.
     * Must match TeamController::PASSWORD_PLACEHOLDER.
     */
    public const PASSWORD_PLACEHOLDER = '__KEEP_EXISTING_PASSWORD__';

    private const STAFF_TYPES    = ['Chef', 'Assistant', 'Server', 'Office', 'Manager', 'Fire Show', 'Driver', 'Fleet', 'Admin', 'Other'];
    private const EMPLOYEE_TYPES = ['Full Time', 'Part Time', 'Seasonal', 'Contractor', 'Temporary', 'Intern', 'Other'];
    private const ALLOWED_ROLES  = ['owner', 'admin', 'manager', 'staff', 'readonly', 'office'];

    /**
     * Only users who can update this specific team member are authorised.
     * TeamPolicy::update() enforces owner-only protection.
     */
    public function authorize(): bool
    {
        $target = User::findOrFail($this->route('id'));

        return $this->user()->can('update', $target);
    }

    /**
     * Replace the keep-password placeholder with an empty string before validation
     * so the nullable/min:6/confirmed rules work correctly.
     */
    protected function prepareForValidation(): void
    {
        if ((string) $this->input('password', '') === self::PASSWORD_PLACEHOLDER) {
            $this->merge(['password' => '', 'password_confirmation' => '']);
        }
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'username'         => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'position'         => ['nullable', 'string', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:40'],
            'employee_number'  => ['nullable', 'string', 'max:40'],
            'employee_type'    => ['nullable', 'string', 'max:40', Rule::in(self::EMPLOYEE_TYPES)],
            'hire_date'        => ['nullable', 'date'],
            'staff_type'       => ['nullable', 'string', 'max:40', Rule::in(self::STAFF_TYPES)],
            'role'             => ['required', 'string', 'max:50', Rule::in(self::ALLOWED_ROLES)],
            'password'         => ['nullable', 'string', 'min:6', 'confirmed'],
            'can_access_admin' => ['sometimes', 'boolean'],
            'is_active'        => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($this->input('role') === 'owner' && ! $this->user()->isOwner()) {
                $v->errors()->add('role', 'Only the owner can assign the owner role.');
            }
        });
    }
}
