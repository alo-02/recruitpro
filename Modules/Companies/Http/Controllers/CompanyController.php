<?php

namespace Modules\Companies\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Companies\Http\Requests\UpdateCompanyRequest;

class CompanyController extends Controller
{
    public function edit(): View
    {
        $company = auth()->user()->company;

        abort_unless($company, 404);

        return view('companies.profile', compact('company'));
    }

    public function update(UpdateCompanyRequest $request): RedirectResponse
    {
        $company = $request->user()->company;
        abort_unless($company, 404);

        $data = [
            'name' => $request->name,
            'website' => $request->website,
            'description' => $request->description,
        ];

        if ($request->name !== $company->name) {
            $data['slug'] = Str::slug($request->name.'-'.$company->id);
        }

        if ($request->hasFile('logo')) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $company->update($data);
        ActivityLogger::log('company.updated', $company);

        return back()->with('success', 'Company profile updated.');
    }
}
