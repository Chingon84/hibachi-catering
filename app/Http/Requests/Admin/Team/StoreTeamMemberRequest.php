<?php

namespace App\Http\Requests\Admin\Team;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeamMemberRequest extends FormRequest
{
    private const STAFF_TYPES    = ['Chef', 'Assistant', 'Server', 'Office', 'Manager', 'Fire Show', 'Driver', 'Fleet', 'Admin', 'Other'];
    private const EMPLOYEE_TYPES = ['Full Time', 'Part Time', 'Seasonal', 'Contractor', 'Temporary', 'Intern', 'Other'];
    private const ALLOWED_ROLES  = ['owner', 'admin', 'manager', 'staff', 'readonly', 'office'];

    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'max:255', 'unique:users,email'],
            'username'         => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'position'         => ['nullable', 'string', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:40'],
            'employee_number'  => ['nullable', 'string', 'max:40'],
            'employee_type'    => ['nullable', 'string', 'max:40', Rule::in(self::EMPLOYEE_TYPES)],
            'hire_date'        => ['nullable', 'date'],
            'staff_type'       => ['nullable', 'string', 'max:40', Rule::in(self::STAFF_TYPES)],
            'role'             => ['required', 'string', 'max:50', Rule::in(self::ALLOWED_ROLES)],
            'password'         => ['required', 'string', 'min:6', 'confirmed'],
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
