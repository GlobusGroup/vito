<?php

namespace App\Http\Controllers\Api;

use App\Crypt;
use App\Http\Controllers\Controller;
use App\Models\Secret;
use Illuminate\Http\Request;
use Throwable;

class SecretController extends Controller
{
    public function decrypt(Request $request, Secret $secret)
    {
        $request->validate([
            's' => 'required|string|uuid',
            'password' => 'nullable|string',
        ]);

        // Delete secret if it's older than 30 days
        if ($secret->created_at->addDays(30) < now()) {
            $secret->delete();
            return response()->json(['error' => 'Secret expired'], 404);
        }

        // Delete secret if it's expired
        if ($secret->valid_until && $secret->valid_until < now()) {
            $secret->delete();
            return response()->json(['error' => 'Secret expired'], 404);
        }

        if ($secret->requires_password && !$request->password) {
            return response()->json(['error' => 'Password is required'], 401);
        }

        try {
            $decrypted_content = Crypt::decryptString($secret->encrypted_content, $request->s, $request->password ?? '');
        } catch (Throwable $th) {
            app('log')->error('Error decrypting Secret # ' . $secret->id . ' with error: ' . $th->getMessage());
            abort(500);
        }

        if ($decrypted_content === false) {
            app('log')->error('User provided an invalid password for Secret # ' . $secret->id);
            return response()->json(['error' => 'Invalid password'], 401);
        }

        if (is_null($secret->valid_until)) {
            $secret->delete();
        }

        return response()->json(['content' => $decrypted_content]);
    }
}
