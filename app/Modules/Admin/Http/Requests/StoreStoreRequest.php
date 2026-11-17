<?php

namespace App\Modules\Admin\Http\Requests;

use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStoreRequest extends FormRequest
{
    /**
     * Otorisasi diperiksa melalui StorePolicy method create.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Store::class) ?? false;
    }

    /**
     * Aturan validasi pendaftaran toko grosir baru (UC-21: identitas, penanggung jawab, & rekening).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash', 'max:80', Rule::unique('stores', 'slug')],
            'description' => ['nullable', 'string', 'max:500'],
            'commission_rate' => ['required', 'numeric', 'between:0,100'],
            'rate_per_kg' => ['required', 'integer', 'min:0'],
            // UC-21 langkah 3: penanggung jawab (owner awal) & rekening bank toko
            'pic_name' => ['required', 'string', 'max:120'],
            'pic_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'bank_name' => ['required', 'string', 'max:60'],
            'account_holder_name' => ['required', 'string', 'max:120'],
            'account_number' => ['required', 'digits_between:6,20'],
        ];
    }

    /**
     * Nama atribut yang mudah dipahami untuk pesan validasi.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama toko',
            'slug' => 'slug URL toko',
            'description' => 'deskripsi toko',
            'commission_rate' => 'tarif komisi platform',
            'rate_per_kg' => 'tarif ongkir per kg',
            'pic_name' => 'nama penanggung jawab',
            'pic_email' => 'surel penanggung jawab',
            'bank_name' => 'nama bank',
            'account_holder_name' => 'nama pemilik rekening',
            'account_number' => 'nomor rekening',
        ];
    }
}
