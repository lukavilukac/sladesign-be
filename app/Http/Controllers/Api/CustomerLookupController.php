<?php

namespace App\Http\Controllers\Api;

use App\Models\Shipping\Shipping;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerLookupController
{
    public function __invoke(Request $request)
    {
        $data = $request->validate(['email' => 'required|string|email|max:255']);
        $email = mb_strtolower(trim($data['email']));
        $adminEmail = mb_strtolower(trim((string) config('mail.admin_address')));
        $user = $email === $adminEmail ? null : User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where(function ($query) {
                $query->where('is_admin', false)->orWhereNull('is_admin');
            })->first();

        $shipping = $user ? Shipping::query()
            ->whereHas('orders', fn ($query) => $query->where('user_id', $user->id))
            ->latest('created_at')->orderByDesc('id')->first() : null;

        return response()->json([
            'exists' => $user !== null,
            'user' => $user ? $user->only(['email', 'first_name', 'last_name', 'phone_number',
                'country', 'city', 'address', 'apartment']) : null,
            'shipping' => $shipping ? array_merge(
                $shipping->only(['country', 'city', 'address', 'apartment', 'phone_number']),
                ['postal_code' => null],
            ) : null,
        ])->header('Cache-Control', 'no-store, private');
    }
}
