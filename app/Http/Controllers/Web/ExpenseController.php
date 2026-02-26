<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('category', 'user')->latest('date')->paginate(10);
        return view('pages.expenses.index', compact('expenses'));
    }

    public function create()
    {
        $categories = ExpenseCategory::all();
        // For autofill, we can get unique names from existing expenses
        $expenseNames = Expense::distinct()->pluck('name');
        return view('pages.expenses.create', compact('categories', 'expenseNames'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'category_id' => 'required|exists:expense_categories,id',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:10240', // Max 10MB
        ]);

        $path = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('expenses', 'public');
        }

        Expense::create([
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'name' => $request->name,
            'amount' => $request->amount,
            'date' => $request->date,
            'description' => $request->description,
            'photo_path' => $path,
        ]);

        return redirect()->route('web.expenses.index')->with('success', 'Pengeluaran berhasil ditambahkan.');
    }

    // Category Management (Owner Only)
    public function categories()
    {

        $categories = ExpenseCategory::all();
        return view('pages.expenses.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {


        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories',
        ]);

        ExpenseCategory::create([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function destroyCategory(ExpenseCategory $category)
    {


        if ($category->expenses()->exists()) {
            return redirect()->back()->with('error', 'Kategori tidak dapat dihapus karena sedang digunakan.');
        }

        $category->delete();
        return redirect()->back()->with('success', 'Kategori berhasil dihapus.');
    }
}
