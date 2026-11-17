<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Modules\Admin\Http\Requests\ScheduleCommissionRequest;
use App\Modules\Admin\Services\ChangeCommissionSchedule;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\RedirectResponse;

final class AdminCommissionController extends Controller
{
    public function __construct(private ChangeCommissionSchedule $service) {}

    /**
     * Menjadwalkan versi komisi baru untuk toko grosir.
     */
    public function store(ScheduleCommissionRequest $request, Store $store): RedirectResponse
    {
        $data = $request->validated();

        try {
            $this->service->handle(
                store: $store,
                rate: $data['commission_rate'],
                effectiveAt: Carbon::parse($data['effective_at']),
            );
        } catch (DomainException $e) {
            return redirect()
                ->route('admin.stores.edit', $store)
                ->withInput()
                ->withErrors(['effective_at' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.stores.edit', $store)
            ->with('status', 'Versi komisi baru berhasil dijadwalkan tanpa tumpang tindih.');
    }
}
