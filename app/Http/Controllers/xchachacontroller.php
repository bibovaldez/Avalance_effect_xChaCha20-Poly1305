<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class xchachacontroller extends Controller
{
    private const COMPRESSION_LEVEL = 9;
    private const HASH_ALGORITHM = 'sha256';

   
    private function validateEncryptRequest(Request $request): void
    {
        $request->validate([
            'file' => 'required|string',
            'key' => 'required|string',
            'nonce' => 'required|string',
            'additionalData' => 'required|string',
        ]);
    }
    private function prepareEncryptionData(Request $request): array
    {
        $key = base64_decode($request->input('key'));
        $nonce = base64_decode($request->input('nonce'));
        $additionalData = hash(self::HASH_ALGORITHM, $request->input('additionalData'), true);


        return [$key, $nonce, $additionalData, $request->input('file')];
    }
    private function encryptData(string $key, string $nonce, string $additionalData, string $data): ?string
    {
        try {
            $encrypted = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
                $data,
                $additionalData,
                $nonce,
                $key
            );
            return base64_encode(gzcompress($encrypted, self::COMPRESSION_LEVEL));
        } catch (\Exception $e) {
            Log::error('Encryption failed: ' . $e->getMessage());
            return null;
        }
    }
    private function errorResponse(string $message): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => $message], 400);
    }


    public function avalanche(Request $request): JsonResponse
    {
        // Retrieve input
        $originalText = $request->input('originaltext');
        $key = base64_decode($request->input('key'));
        $nonce = base64_decode($request->input('nonce'));
        $additionalData = hash(self::HASH_ALGORITHM, $request->input('additionalData'), true);

        // Calculate SAC and BIC
        $sac = $this->calculateSAC($originalText, $key, $nonce, $additionalData);
        $bic = $this->calculateBIC($originalText, $key, $nonce, $additionalData);

        // Return results as JSON
        return response()->json([
            'SAC' => $sac,
            'BIC' => $bic
        ]);
    }

    private function calculateSAC(string $originalText, string $key, string $nonce, string $additionalData): float
    {
        $totalBits = strlen($originalText) * 8;
        $changedBits = 0;

        for ($i = 0; $i < strlen($originalText); $i++) {
            for ($j = 0; $j < 8; $j++) {
                $modifiedText = $originalText;
                $modifiedText[$i] = chr(ord($modifiedText[$i]) ^ (1 << $j));

                $encryptedOriginal = $this->encryptForAvalanche($originalText, $key, $nonce, $additionalData);
                $encryptedModified = $this->encryptForAvalanche($modifiedText, $key, $nonce, $additionalData);

                if ($encryptedOriginal === null || $encryptedModified === null) {
                    // Handle encryption failure
                    Log::error('Encryption failed during SAC calculation');
                    return 0;
                }

                for ($k = 0; $k < strlen($encryptedOriginal); $k++) {
                    $diff = ord($encryptedOriginal[$k]) ^ ord($encryptedModified[$k]);
                    $changedBits += substr_count(decbin($diff), '1');
                }
            }
        }

        $averageChangedBits = $changedBits / $totalBits;
        return $averageChangedBits;
    }

    private function calculateBIC(string $originalText, string $key, string $nonce, string $additionalData): float
    {
        // Placeholder implementation for BIC calculation
        // Replace with actual logic
        return 0.5;
    }

    private function encryptForAvalanche(string $data, string $key, string $nonce, string $additionalData): ?string
    {
        try {
            return sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
                $data,
                $additionalData,
                $nonce,
                $key
            );
        } catch (\Exception $e) {
            Log::error('Encryption failed during avalanche calculation: ' . $e->getMessage());
            return null;
        }
    }
}
