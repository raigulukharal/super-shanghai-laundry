<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses
     */
    public function index(Request $request)
    {
        $query = Expense::with('category', 'creator');
        
        if ($request->category_id) {
            $query->where('expense_category_id', $request->category_id);
        }
        
        if ($request->start_date) {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }
        
        if ($request->end_date) {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }
        
        $expenses = $query->orderBy('expense_date', 'desc')->paginate(20);
        
        $stats = [
            'total' => Expense::sum('amount'),
            'this_month' => Expense::whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)
                ->sum('amount'),
            'this_week' => Expense::whereBetween('expense_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('amount'),
            'today' => Expense::whereDate('expense_date', today())->sum('amount'),
            'count' => Expense::count()
        ];
        
        $categories = ExpenseCategory::all();
        $categoryStats = ExpenseCategory::withSum('expenses', 'amount')->get();
        
        return view('admin.expenses.index', compact('expenses', 'stats', 'categories', 'categoryStats'));
    }

    /**
     * Show form for creating new expense
     */
    public function create()
    {
        $categories = ExpenseCategory::all();
        return view('admin.expenses.create', compact('categories'));
    }

    /**
     * Store a newly created expense
     */
    public function store(Request $request)
    {
        // Log request for debugging
        Log::info('Expense store request:', $request->all());
        
        $validator = validator($request->all(), [
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first()
            ], 422);
        }

        try {
            $expense = Expense::create([
                'expense_category_id' => $request->expense_category_id,
                'amount' => $request->amount,
                'expense_date' => $request->expense_date,
                'description' => $request->description,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expense added successfully',
                'redirect' => route('admin.expenses.index')
            ]);

        } catch (\Exception $e) {
            Log::error('Expense store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display expense details
     */
    public function show($id)
    {
        $expense = Expense::with('category', 'creator')->findOrFail($id);
        return view('admin.expenses.show', compact('expense'));
    }

    /**
     * Show form for editing expense
     */
    public function edit($id)
    {
        $expense = Expense::findOrFail($id);
        $categories = ExpenseCategory::all();
        return view('admin.expenses.edit', compact('expense', 'categories'));
    }

    /**
     * Update expense
     */
    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        
        $validator = validator($request->all(), [
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first()
            ], 422);
        }

        try {
            $expense->update([
                'expense_category_id' => $request->expense_category_id,
                'amount' => $request->amount,
                'expense_date' => $request->expense_date,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expense updated successfully',
                'redirect' => route('admin.expenses.index')
            ]);

        } catch (\Exception $e) {
            Log::error('Expense update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete expense
     */
    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        
        try {
            $expense->delete();
            return response()->json([
                'success' => true,
                'message' => 'Expense deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}