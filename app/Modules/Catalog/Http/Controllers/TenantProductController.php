<?php

namespace App\Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller pembuktian isolasi tenant pada modul Catalog.
 * Dilindungi oleh: SetTenantContext + BelongsToTenant + Scoped Route Binding + ProductPolicy.
 */
final class TenantProductController extends Controller
{
    use AuthorizesRequests;

    public function index(Store $tenant): JsonResponse
    {
        // Global scope otomatis membatasi ke toko aktif
        $products = Product::query()
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'sales_unit', 'weight_gram', 'status']);

        return response()->json([
            'store' => $tenant->slug,
            'products' => $products,
        ]);
    }

    public function show(Store $tenant, Product $product): JsonResponse
    {
        // Policy memeriksa apakah product->store_id === context->id()
        $this->authorize('view', $product);

        return response()->json($product->only([
            'id', 'name', 'category', 'sales_unit', 'weight_gram', 'status',
        ]));
    }

    public function update(Request $request, Store $tenant, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:aktif,arsip,habis'],
        ]);

        $product->update(['status' => $data['status']]);

        return response()->json([
            'ok' => true,
            'status' => $product->status,
        ]);
    }
}
