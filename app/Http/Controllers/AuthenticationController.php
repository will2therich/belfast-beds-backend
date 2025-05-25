<?php

namespace App\Http\Controllers;

use App\Models\Ecom\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticationController
{

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $secondsTillEndOfDay = Carbon::now()->addDays(7)->secondsUntilEndOfDay();
        $minutesTillEndOfDay = ceil($secondsTillEndOfDay/60);

        $expiry = Carbon::now()->addMinutes($minutesTillEndOfDay)->timestamp;
        $token = auth()->guard('vue')->setTTL($minutesTillEndOfDay)->attempt($credentials, ['exp' => $expiry]);

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated',
            ], 401);
        }

        $customer = auth()->guard('vue')->user();

        $allowLogin = true;

        if (!$customer->active) $allowLogin = false;

        if ($allowLogin) {
            return response()->json([
                'status' => 'success',
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                    'expires' => $expiry
                ],
                'customer' => $customer->toArray()
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated',
            ], 401);
        }
    }


    public function register(Request $request)
    {
        $data = $request->all();
        $userExists = Customer::where('email', $data['email'])->exists();

        if ($userExists) {
            return response()->json([
                'success' => false,
                'message' => 'User with email already exists'
            ], 200);
        } else {
            $customer = new Customer();
            $customer->full_name = $data['full_name'];
            $customer->email = $data['email'];
            $customer->password = Hash::make($data['password']);
            $customer->save();

            return response()->json([
                'success' => true,
                'message' => 'Account Created'
            ]);
        }

    }

    public function me()
    {
        if (auth('vue')->check()) {
            $user = auth('vue')->user()->toArray();
            unset($user['password']);
            return response()->json($user);
        }


        return response()->json([], 401);
    }

}
