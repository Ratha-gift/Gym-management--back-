<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return Setting::orderBy('setting_key')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'setting_key' => ['required', 'string', 'max:100', 'unique:settings,setting_key'],
            'setting_value' => ['nullable', 'string'],
        ]);

        return response()->json(Setting::create($data), 201);
    }

    public function show(Setting $setting)
    {
        return $setting;
    }

    public function update(Request $request, Setting $setting)
    {
        $data = $request->validate([
            'setting_value' => ['nullable', 'string'],
        ]);

        $setting->update($data);

        return $setting;
    }

    public function destroy(Setting $setting)
    {
        $setting->delete();

        return response()->json(null, 204);
    }
}
