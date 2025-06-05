<?php

namespace App\Http\Controllers;

use App\Models\Secret;
use App\Services\SecretService;
use App\Services\SecretNotFoundException;
use Throwable;

class SecretController extends Controller
{
    protected $secretService;

    public function __construct(SecretService $secretService)
    {
        $this->secretService = $secretService;
    }

    public function show()
    {
        request()->validate(['d' => 'required|string']);

        $decryptedData = $this->secretService->decryptPayload(request()->d);
        $secret = Secret::findOrFail($decryptedData['secret_id']);
        $this->secretService->checkIfSecretIsValidOrAbort($secret);

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
        request()->validate(SecretService::getCommonValidationRules());

        $result = $this->secretService->createSecret(
            request()->content,
            request()->password
        );

        $secret = $result['secret'];
        $encryptionKey = $result['encryption_key'];

        // Encrypt the data and flash it in the session
        $encryptedData = $this->secretService->generateSharingData($secret->id, $encryptionKey);
        session()->flash('encrypted_data', $encryptedData);

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

        $decryptedData = $this->secretService->decryptPayload(request()->d);
        $secret = Secret::findOrFail($decryptedData['secret_id']);

        if ($secret->requires_password && !request()->password) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // This method now handles expiration check, decryption, and deletion atomically
            $decryptedContent = $this->secretService->decryptSecretContent(
                $secret,
                $decryptedData['secret_key'],
                request()->password
            );
        } catch (SecretNotFoundException $th) {
            return response()->json(['error' => 'Not Found'], 404);
        } catch (Throwable $th) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['content' => $decryptedContent]);
    }
}
