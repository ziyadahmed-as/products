<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Authorize that the current user is a Super Admin or Admin.
     */
    protected function authorizeAccess(): void
    {
        if (!auth()->user() || !auth()->user()->hasAnyRole(['Super Admin', 'Admin'])) {
            abort(403, 'Unauthorized. Only Super Admin and Admin can manage system settings.');
        }
    }

    /**
     * Display the settings form.
     */
    public function index()
    {
        $this->authorizeAccess();

        $settings = [
            'site_name'        => Setting::get('site_name', config('app.name', 'Albareck')),
            'site_tagline'     => Setting::get('site_tagline', 'Integrated Stock & Manufacturing Management System'),
            'site_logo'        => Setting::get('site_logo'),
            'logo_url'         => Setting::getLogoUrl(),
            'contact_email'    => Setting::get('contact_email', 'info@albareck.com'),
            'contact_phone'    => Setting::get('contact_phone', '+251 900 000 000'),
            'contact_address'  => Setting::get('contact_address', 'Addis Ababa, Ethiopia'),
        ];

        return view('settings.index', compact('settings'));
    }

    /**
     * Update system settings and logo.
     */
    public function update(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'site_name'       => 'nullable|string|max:100',
            'site_tagline'    => 'nullable|string|max:255',
            'contact_email'   => 'nullable|email|max:150',
            'contact_phone'   => 'nullable|string|max:50',
            'contact_address' => 'nullable|string|max:255',
            'logo'            => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('settings', $filename, 'public');
            Setting::set('site_logo', $path);
        }

        if (isset($validated['site_name'])) {
            Setting::set('site_name', $validated['site_name']);
        }
        if (isset($validated['site_tagline'])) {
            Setting::set('site_tagline', $validated['site_tagline']);
        }
        if (isset($validated['contact_email'])) {
            Setting::set('contact_email', $validated['contact_email']);
        }
        if (isset($validated['contact_phone'])) {
            Setting::set('contact_phone', $validated['contact_phone']);
        }
        if (isset($validated['contact_address'])) {
            Setting::set('contact_address', $validated['contact_address']);
        }

        return redirect()->route('settings.index')->with('success', 'System settings and logo updated successfully!');
    }

    /**
     * Reset the logo to default.
     */
    public function resetLogo()
    {
        $this->authorizeAccess();

        Setting::set('site_logo', 'images/logo.jpg');

        return redirect()->route('settings.index')->with('success', 'Logo reset to default Albareck logo successfully!');
    }
}
