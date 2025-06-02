<?php

namespace App\Http\Controllers\Api;

use App\Crypt;
use App\Http\Controllers\Controller;
use App\Models\Secret;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt as LaravelCrypt;

class SecretApiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:200000',
            'password' => 'nullable|string|max:100',
            'expires_in_minutes' => [
                'nullable',
                'integer',
                'min:1',
                'max:' . (24 * 60 * 5), // Maximum 5 days
            ],
        ]);

        $encryptionKey = bin2hex(random_bytes(32));

        $encryptedContent = Crypt::encryptString(
            $request->content,
            $encryptionKey,
            $request->password ?? Crypt::DEFAULT_PASSWORD
        );

        // Calculate expiry time
        $expiresInMinutes = $request->expires_in_minutes ?? (int) config('app.secrets_lifetime');
        $expiresAt = now()->addMinutes($expiresInMinutes);

        $secret = Secret::create([
            'encrypted_content' => $encryptedContent,
            'requires_password' => !is_null($request->password),
            'expires_at' => $expiresAt,
        ]);

        // Create the sharing URL data
        $data = json_encode(['secret_id' => $secret->id, 'secret_key' => $encryptionKey]);
        $encryptedData = LaravelCrypt::encryptString($data);
        
        // Generate the full sharing URL
        $shareUrl = url('/secrets/show?d=' . urlencode($encryptedData));

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $secret->id,
                'share_url' => $shareUrl,
                'expires_at' => $secret->expires_at->toISOString(),
                'expires_in_minutes' => $expiresInMinutes,
                'requires_password' => $secret->requires_password,
            ]
        ], 201);
    }
} 