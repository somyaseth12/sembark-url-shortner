<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies (SuperAdmin)
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->role->name !== 'SuperAdmin') {
            return redirect()->route('dashboard')->with('error', 'Unauthorized');
        }

        $query = Company::query();

        $filter = request('company_filter');
        if ($filter === 'today') {
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($filter === 'week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($filter === 'month') {
            $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        }

        $companies = $query->latest()->paginate(15)->withQueryString();

        return view('companies.index', compact('companies'));
    }
    /**
     * Show the specified company
     */
    public function show(Company $company)
    {
        // Authorization: only SuperAdmin can view companies
        $user = Auth::user();
        if ($user->role->name !== 'SuperAdmin') {
            return redirect()->route('dashboard')->with('error', 'Unauthorized');
        }

        return view('companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified company
     */
    public function edit(Company $company)
    {
        // Authorization: only SuperAdmin can edit companies
        $user = Auth::user();
        if ($user->role->name !== 'SuperAdmin') {
            return redirect()->route('dashboard')->with('error', 'Unauthorized');
        }

        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified company in storage
     */
    public function update(Request $request, Company $company)
    {
        // Authorization: only SuperAdmin can update companies
        $user = Auth::user();
        if ($user->role->name !== 'SuperAdmin') {
            return redirect()->route('dashboard')->with('error', 'Unauthorized');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:companies,domain,' . $company->id,
        ]);

        $company->update([
            'name' => $request->name,
            'domain' => $request->domain,
        ]);

        return redirect()->route('companies.show', $company)->with('success', 'Company updated successfully');
    }

    /**
     * Delete the specified company
     */
    public function destroy(Company $company)
    {
        // Authorization: only SuperAdmin can delete companies
        $user = Auth::user();
        if ($user->role->name !== 'SuperAdmin') {
            return redirect()->route('dashboard')->with('error', 'Unauthorized');
        }

        $companyName = $company->name;
        $company->delete();

        return redirect()->route('superadmin.dashboard')->with('success', "Company '{$companyName}' deleted successfully");
    }
}
