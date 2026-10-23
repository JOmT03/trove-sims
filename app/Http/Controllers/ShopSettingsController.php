<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class ShopSettingsController extends Controller
{
    public const DEFAULT_TERMS =
        "1. A downpayment confirms the order. The remaining balance is due in full upon delivery or pickup.\n" .
        "2. Orders should be placed at least 3 days before the needed date.\n" .
        "3. If Trove fails to deliver on the agreed date, the downpayment is refunded in full.\n" .
        "4. Design references are a guide only; small variations in color and detail may occur.\n" .
        "5. Cancellations within 48 hours of the needed date forfeit the downpayment.";

    public function edit()
    {
        return view('settings.shop', [
            'shopName'    => Setting::get('shop_name', 'Trove - Food & Cake Shop'),
            'shopAddress' => Setting::get('shop_address', ''),
            'shopContact' => Setting::get('shop_contact', ''),
            'orderTerms'  => Setting::get('order_terms', self::DEFAULT_TERMS),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'shop_name'    => 'nullable|string|max:255',
            'shop_address' => 'nullable|string|max:255',
            'shop_contact' => 'nullable|string|max:255',
            'order_terms'  => 'nullable|string|max:5000',
        ]);
        foreach ($data as $k => $v) {
            Setting::set($k, $v ?? '');
        }
        return back()->with('success', 'Receipt settings saved.');
    }
}