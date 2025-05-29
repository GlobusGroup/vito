<?php

namespace App\Http\Controllers;

use App\Crypt;
use App\Models\Secret;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class SecretController extends Controller
{
    public function show(Secret $secret)
    {
        request()->validate([
            's' => 'required|string|uuid',
        ]);

        if ($secret->valid_until && $secret->valid_until < now()) {
            $secret->delete();
            abort(404);
        }

        return view('secrets.show', [
            'decryption_key' => request()->s,
            'secret' => [
                'id' => $secret->id,
                'requires_password' => $secret->requires_password,
                'valid_until' => $secret->valid_until,
            ],
        ]);
    }

    public function create()
    {
        return view('secrets.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:200000',
            'password' => 'nullable|string|max:255',
            'valid_for' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $encryptionKey = base64_encode(random_bytes(32));

        // Encrypt the content with the provided password
        $encryptedContent = Crypt::encryptString($request->content, $encryptionKey, $request->password ?? '');

        $secret = Secret::create([
            'encrypted_content' => $encryptedContent,
            'requires_password' => $request->password ? true : false,
            'valid_until' => $request->valid_for ? now()->addMinutes((int) $request->valid_for) : null,
        ]);

        return view('secrets.share', [
            'message' => 'Secret created successfully',
            'id' => $secret->id,
            'secret' => $encryptionKey,
            'url' => route('secrets.show', $secret->id) . '?s=' . $encryptionKey,
        ]);
    }

    public function decrypt(Request $request, Secret $secret)
    {
        $request->validate([
            's' => 'required|string|uuid',
            'password' => 'nullable|string|max:255',
        ]);

        // Delete secret if it's older than 30 days
        if ($secret->created_at->addDays(30) < now()) {
            $secret->delete();
            return response()->json(['error' => 'Not Found'], 404);
        }

        // Delete secret if it's expired
        if ($secret->valid_until && $secret->valid_until < now()) {
            $secret->delete();
            return response()->json(['error' => 'Not Found'], 404);
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
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (is_null($secret->valid_until)) {
            $secret->delete();
        }

        return response()->json(['content' => $decrypted_content]);
    }
}
