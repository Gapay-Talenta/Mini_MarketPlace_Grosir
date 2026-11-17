<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        $normalizedPhone = preg_replace('/[\s\-\.]+/', '', (string) ($input['phone'] ?? '')) ?? '';

        $normalized = [
            ...$input,
            'phone' => $normalizedPhone,
        ];

        Validator::make($normalized, [
            ...$this->profileRules(),
            'phone' => $this->phoneRules(),
            'password' => $this->passwordRules(),
            'terms' => ['accepted'],
        ], [
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'phone.regex' => 'Format nomor WhatsApp tidak valid. Gunakan awalan 08 atau 628 (contoh: 081234567890).',
            'phone.unique' => 'Nomor WhatsApp sudah terdaftar pada akun lain.',
            'terms.accepted' => 'Anda harus menyetujui syarat dan ketentuan platform.',
        ])->validate();

        return User::create([
            'name' => (string) $normalized['name'],
            'email' => (string) $normalized['email'],
            'phone' => $normalizedPhone,
            'role' => null,
            'status' => 'active',
            'password' => (string) $normalized['password'],
        ]);
    }
}
