<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CustomerFollowUpController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('viewAny', Customer::class);

        $baseQuery = Customer::whereNotNull('last_order_date')
            ->withoutGlobalScope(\App\Models\Scopes\AreaScope::class);

        // Summary counts (before filters)
        $summary = [
            'total'   => (clone $baseQuery)->count(),
            'over_30' => (clone $baseQuery)->where('last_order_date', '<=', now()->subDays(30))->count(),
            'over_60' => (clone $baseQuery)->where('last_order_date', '<=', now()->subDays(60))->count(),
            'over_90' => (clone $baseQuery)->where('last_order_date', '<=', now()->subDays(90))->count(),
        ];

        // Main query with filters
        $query = (clone $baseQuery)->with('addresses.area')
            ->orderBy('last_order_date', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('phone_number', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('min_days')) {
            $query->where('last_order_date', '<=', now()->subDays((int) $request->min_days));
        }

        $customers = $query->paginate(20);

        return view('pages.customers.follow-up', compact('customers', 'summary'));
    }
}
