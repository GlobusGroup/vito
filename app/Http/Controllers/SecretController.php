<?php

namespace App\Http\Controllers;

use App\Crypt;
use App\Models\Secret;
use Illuminate\Support\Facades\Crypt as LaravelCrypt;
use Illuminate\Support\Facades\Validator;
use Throwable;

class SecretController extends Controller
{
    public function show()
    {
        request()->validate([
            'd' => 'required|string',
        ]);

        try {
            $decryptedData = LaravelCrypt::decryptString(request()->d);
            $decryptedData = json_decode($decryptedData, true);
        } catch (Throwable $th) {
            abort(404);
        }

        if (!$decryptedData) {
            abort(404);
        }

        $secret = Secret::findOrFail($decryptedData['secret_id']);

        if ($secret->valid_until && $secret->valid_until < now()) {
            $secret->delete();
            abort(404);
        }

        return view('secrets.show', [
            'decryption_key' => $decryptedData['secret_key'],
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
            'password' => 'nullable|string|max:100',
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
        ]);

        // Encrypt the data
        $data = json_encode(['secret_id' => $secret->id, 'secret_key' => $encryptionKey]);
        $encryptedData = LaravelCrypt::encryptString($data);

        return redirect()->route('secrets.share', ['d' => $encryptedData]);
    }

    public function share()
    {
        return view('secrets.share', [
            'message' => 'Secret created successfully',
            'url' => route('secrets.show') . '?d=' . request()->d,
        ]);
    }

    public function decrypt(Secret $secret)
    {
        request()->validate([
            's' => 'required|string|size:64',
            'password' => 'nullable|string|max:100',
        ]);

        if ($this->isExpired($secret)) {
            return response()->json(['error' => 'Not Found'], 404);
        }

        if ($secret->requires_password && !request()->password) {
            return response()->json(['error' => 'Unauthorized'], 401);
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
            return response()->json(['error' => 'Unauthorized'], 401);
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
        if ($secret->created_at->addMinutes((int) config('app.secrets_lifetime')) < now()) {
            $secret->delete();
            return true;
        }

        return false;
    }
}
