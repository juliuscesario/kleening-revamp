<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // We need to fetch all service categories to populate the dropdown in the modal.
        $categories = ServiceCategory::all();
        return view('pages.services.index', compact('categories'));
    }
}
