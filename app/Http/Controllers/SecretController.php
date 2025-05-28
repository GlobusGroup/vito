<?php

namespace App\Http\Controllers;

use App\Crypt;
use App\Models\Secret;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
            'content' => 'required|string',
            'password' => 'nullable|string',
            'valid_for' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $encryptionKey = Str::uuid();

        // Encrypt the content with the provided password
        $encryptedContent = Crypt::encryptString($request->content, $encryptionKey, $request->password);

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
}
