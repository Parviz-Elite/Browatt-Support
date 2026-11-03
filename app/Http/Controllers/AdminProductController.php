<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Warranty\MehrsoftProductCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class AdminProductController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim($request->string('search')->limit(100, '')->toString());
        $status = $request->string('status')->toString();
        $sort = $request->string('sort')->toString() === 'name' ? 'name' : 'code';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $catalog = Product::query();
        $lastSync = (clone $catalog)->max('catalog_synced_at');

        return Inertia::render('Dashboard/Products/Index', [
            'products' => (clone $catalog)
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('code', 'like', '%'.Product::normalizeCode($search).'%')
                    ->orWhere('name', 'like', '%'.$search.'%')))
                ->when(in_array($status, ['enabled', 'disabled'], true), fn ($query) => $query
                    ->where('online_activation_enabled', $status === 'enabled'))
                ->orderBy($sort, $direction)
                ->orderBy('id')
                ->paginate(25, ['id', 'code', 'name', 'warranty_months', 'warranty_checked_at', 'online_activation_enabled'])
                ->withQueryString(),
            'filters' => compact('search', 'status', 'sort', 'direction'),
            'summary' => [
                'total' => (clone $catalog)->count(),
                'disabled' => (clone $catalog)->where('online_activation_enabled', false)->count(),
                'last_sync' => $lastSync ? Carbon::parse($lastSync)->toIso8601String() : null,
            ],
            'defaultWarrantyMonths' => Product::DEFAULT_WARRANTY_MONTHS,
        ]);
    }

    public function sync(MehrsoftProductCatalog $catalog): JsonResponse
    {
        try {
            $catalog->syncGoods();
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'دریافت فهرست کالاها از مهرپلاس انجام نشد.'], 502);
        }

        return response()->json(['total' => Product::query()->whereNotNull('catalog_synced_at')->count()]);
    }

    public function syncWarranty(Request $request, MehrsoftProductCatalog $catalog): JsonResponse
    {
        $validated = $request->validate(['after' => ['required', 'integer', 'min:0']]);

        try {
            return response()->json($catalog->syncWarrantyBatch((int) $validated['after']));
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'دریافت مدت گارانتی از مهرپلاس متوقف شد. دوباره تلاش کنید.'], 502);
        }
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate(['online_activation_enabled' => ['required', 'boolean']]);

        $product->forceFill($validated)->save();

        return back()->with('success', 'وضعیت فعال‌سازی آنلاین کالا ذخیره شد.');
    }
}
