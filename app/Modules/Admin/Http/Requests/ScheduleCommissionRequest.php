<?php

namespace App\Modules\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleCommissionRequest extends FormRequest
{
    /**
     * Otorisasi diperiksa melalui StorePolicy method scheduleCommission.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('scheduleCommission', $this->route('store')) ?? false;
    }

    /**
     * Aturan validasi jadwal komisi baru.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'commission_rate' => ['required', 'numeric', 'between:0,100'],
            'effective_at' => ['required', 'date', 'after:now'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'commission_rate' => 'tarif komisi baru',
            'effective_at' => 'tanggal berlaku',
        ];
    }
}
