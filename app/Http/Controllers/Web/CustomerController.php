<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Area;
use App\Models\Customer;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $areas = Area::all();
        return view('pages.customers.index', compact('areas'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\View\View
     */
    public function show(Customer $customer)
    {
        $customer->load('addresses.area');

        // Widget Data
        $totalOrders = $customer->serviceOrders()->count();
        $totalBilling = $customer->invoices()->sum('grand_total');
        $outstanding = $customer->invoices()->where('invoices.status', '!=', 'paid')->sum('grand_total');
        $lastOrderDate = $customer->last_order_date;

        return view('pages.customers.detail', compact(
            'customer', 
            'totalOrders', 
            'totalBilling', 
            'outstanding', 
            'lastOrderDate'
        ));
    }
}
