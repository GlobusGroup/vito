<?php

namespace App\Http\Controllers;

use App\Crypt;
use App\Models\Secret;
use Illuminate\Support\Facades\Validator;
use Throwable;

class SecretController extends Controller
{
    public function show(Secret $secret)
    {
        request()->validate([
            's' => 'required|string',
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

    public function store()
    {
        $validator = Validator::make(request()->all(), [
            'content' => 'required|string|max:200000',
            'password' => 'nullable|string|max:255',
            'valid_for' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $encryptionKey = bin2hex(random_bytes(32));

        $encryptedContent = Crypt::encryptString(
            request()->content,
            $encryptionKey,
            request()->password ?? Crypt::DEFAULT_PASSWORD
        );

        $secret = Secret::create([
            'encrypted_content' => $encryptedContent,
            'requires_password' => !is_null(request()->password),
            'valid_until' => request()->valid_for ? now()->addMinutes((int) request()->valid_for) : null,
        ]);

        return redirect()->route('secrets.share', ['secret' => $secret->id, 's' => $encryptionKey]);
    }

    public function share(Secret $secret)
    {
        $key = request()->query('s');

        if (!$key) {
            abort(404);
        }

        return view('secrets.share', [
            'message' => 'Secret created successfully',
            'id' => $secret->id,
            'secret' => $key,
            'url' => route('secrets.show', $secret->id) . '?s=' . $key,
        ]);
    }

    public function decrypt(Secret $secret)
    {
        request()->validate([
            's' => 'required|string|size:64',
            'password' => 'nullable|string|max:255',
        ]);

        if ($this->isExpired($secret)) {
            return response()->json(['error' => 'Not Found'], 404);
        }

        if ($secret->requires_password && !request()->password) {
            return response()->json(['error' => 'Password is required'], 401);
        }

        // Slow down decryption to prevent brute force attacks
        usleep(random_int(400_000, 600_000));

        try {
            $decrypted_content = Crypt::decryptString(
                $secret->encrypted_content,
                request()->s,
                request()->password ?? Crypt::DEFAULT_PASSWORD
            );
        } catch (Throwable $th) {
            app('log')->error('Error decrypting Secret');
            abort(401);
        }

        if ($decrypted_content === false) {
            app('log')->error('User provided an invalid password');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (is_null($secret->valid_until)) {
            $secret->delete();
        }

        return response()->json(['content' => $decrypted_content]);
    }

    protected function isExpired(Secret $secret)
    {
        if ($secret->created_at->addDays(30) < now()) {
            $secret->delete();
            return true;
        }

        if ($secret->valid_until && $secret->valid_until < now()) {
            $secret->delete();
            return true;
        }

        return false;
    }
}
