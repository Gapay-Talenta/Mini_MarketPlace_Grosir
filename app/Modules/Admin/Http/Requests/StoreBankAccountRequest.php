<?php

namespace App\Modules\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankAccountRequest extends FormRequest
{
    /**
     * Otorisasi diperiksa melalui StorePolicy::manageBank.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manageBank', $this->route('store')) ?? false;
    }

    /**
     * Aturan validasi tambah rekening bank toko.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:60'],
            'account_holder_name' => ['required', 'string', 'max:120'],
            'account_number' => ['required', 'digits_between:6,20'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'bank_name' => 'nama bank',
            'account_holder_name' => 'nama pemilik rekening',
            'account_number' => 'nomor rekening',
        ];
    }
}
