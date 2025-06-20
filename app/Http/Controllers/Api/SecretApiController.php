<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SecretService;
use Illuminate\Http\Request;

class SecretApiController extends Controller
{
    protected $secretService;

    public function __construct(SecretService $secretService)
    {
        $this->secretService = $secretService;
    }

    public function store(Request $request)
    {
        $validationRules = array_merge(
            SecretService::getCommonValidationRules(),
            [
                'expires_in_minutes' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:' . (24 * 60 * 5), // Maximum 5 days
                ],
            ]
        );

        $request->validate($validationRules);

        $result = $this->secretService->createSecret(
            $request->content,
            $request->password,
            $request->expires_in_minutes
        );

        $secret = $result['secret'];
        $encryptionKey = $result['encryption_key'];
        $expiresInMinutes = $result['expires_in_minutes'];

        // Generate the sharing URL
        $encryptedData = $this->secretService->generateSharingData($secret->id, $encryptionKey);
        $shareUrl = $this->secretService->generateShareUrl($encryptedData);
        //make sure the share url is https, if not, update it
        if (strpos($shareUrl, 'http://') !== false) {
            $shareUrl = str_replace('http://', 'https://', $shareUrl);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'share_url' => $shareUrl,
                'expires_at' => $secret->expires_at->toISOString(),
                'expires_on' => $secret->expires_at->format('d/m/Y H:i'),
                'expires_in_minutes' => $expiresInMinutes,
                'requires_password' => $secret->requires_password,
            ]
        ], 201);
    }
} 