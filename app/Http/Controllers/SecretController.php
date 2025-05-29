<?php

namespace App\Http\Controllers;

use App\Crypt;
use App\Models\Secret;
use Illuminate\Support\Facades\Crypt as LaravelCrypt;
use Throwable;

class SecretController extends Controller
{
    public function show()
    {
        request()->validate(['d' => 'required|string']);

        $decryptedData = $this->decryptPayload(request()->d);
        $secret = Secret::findOrFail($decryptedData['secret_id']);
        $this->checkIfSecretIsValidOrAbort($secret);

        return view('secrets.show', [
            'd' => request()->d,
            'requires_password' => $secret->requires_password,
        ]);
    }

    public function create()
    {
        return view('secrets.create');
    }

    public function store()
    {
        request()->validate([
            'content' => 'required|string|max:200000',
            'password' => 'nullable|string|max:100',
        ]);

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

        // Encrypt the data and flash it in the session
        $data = json_encode(['secret_id' => $secret->id, 'secret_key' => $encryptionKey]);
        session()->flash('encrypted_data', LaravelCrypt::encryptString($data));

        return redirect()->route('secrets.share');
    }

    public function share()
    {
        if (!session()->has('encrypted_data')) {
            abort(404);
        }

        return view('secrets.share');
    }

    public function decrypt()
    {
        request()->validate([
            'd' => 'required|string',
            'password' => 'nullable|string|max:100',
        ]);

        $decryptedData = $this->decryptPayload(request()->d);
        $secret = Secret::findOrFail($decryptedData['secret_id']);
        $this->checkIfSecretIsValidOrAbort($secret);

        if ($secret->requires_password && !request()->password) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Slow down decryption to prevent brute force attacks
        usleep(random_int(400_000, 600_000));

        try {
            $decrypted_content = Crypt::decryptString(
                $secret->encrypted_content,
                $decryptedData['secret_key'],
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

    protected function checkIfSecretIsValidOrAbort(Secret $secret)
    {
        if ($secret->isExpired()) {
            $secret->delete();
            abort(404);
        }
    }

    protected function decryptPayload(string $payload)
    {
        try {
            $decryptedData = LaravelCrypt::decryptString($payload);
            $decryptedData = json_decode($decryptedData, true);
        } catch (Throwable $th) {
            abort(404);
        }

        if (!$decryptedData) {
            abort(404);
        }
        return $decryptedData;
    }
}
