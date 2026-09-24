<?php

namespace App\Http\Requests;

use App\Enums\MemberStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberUsernameRequest extends FormRequest
{
    /**
     * Only approved, non-archived members get a public profile, so only
     * they can pick its address.
     */
    public function authorize(): bool
    {
        return $this->route('member')->status === MemberStatus::Active;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['username' => mb_strtolower(trim($this->string('username')->toString()))]);
    }

    /**
     * Latin letters, digits and underscores, with at least one non-digit so a
     * username can never collide with a numeric /u/{id} address.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^(?=.*[a-z_])[a-z0-9_]+$/',
                Rule::unique('members', 'username')->ignore($this->route('member')),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.regex' => __('public.username_format'),
            'username.unique' => __('public.username_taken'),
        ];
    }
}
