<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Area;
use App\Models\Address;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AddressController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('pages.addresses.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $this->authorize('create', Address::class);

        $customers = Customer::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        $selectedCustomerId = $request->query('customer');

        return view('pages.addresses.create', compact('customers', 'areas', 'selectedCustomerId'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', Address::class);

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'area_id' => 'required|exists:areas,id',
            'label' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:255',
            'full_address' => 'required|string',
            'google_maps_link' => 'nullable|url|max:1024',
        ]);

        Address::create($validated);

        return redirect()->route('web.customers.show', $validated['customer_id'])
                         ->with('success', 'Alamat baru berhasil ditambahkan.');
    }
}