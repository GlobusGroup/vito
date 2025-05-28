<?php

namespace App\Http\Controllers\Api;

use App\Crypt;
use App\Http\Controllers\Controller;
use App\Models\Secret;
use Illuminate\Http\Request;

class SecretController extends Controller
{
    public function decrypt(Request $request, Secret $secret)
    {
        $request->validate([
            's' => 'required|string|uuid',
            'password' => 'nullable|string',
        ]);

        if ($secret->requires_password && !$request->password) {
            return response()->json(['error' => 'Password is required'], 401);
        }

        if ($secret->valid_until && $secret->valid_until < now()) {
            $secret->delete();
            return response()->json(['error' => 'Secret expired'], 404);
        }

        $decrypted_content = Crypt::decryptString($secret->encrypted_content, $request->s, $request->password);

        if (is_null($secret->valid_until)) {
            $secret->delete();
        }

        return response()->json(['content' => $decrypted_content]);
    }
}
