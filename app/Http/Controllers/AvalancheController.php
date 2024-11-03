<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class avalanchecontroller extends Controller
{
    public function avalanche(Request $request)
    {
        $this->validateRequest($request);

        $originalText = $request->input('originaltext');
        $key = base64_decode($request->input('key'));
        $additionalData = $request->input('additionalData');

        $SACresult = $this->calculateAvalancheWithDetails($originalText, $key, $additionalData);

        // dd($SACresult);

        $response = [
            'SAC' => $SACresult['SAC'],
            'modifications' => $SACresult['modifications'],
        ];

        return view('avalanche', ['response' => $response]);
    }

    private function calculateAvalancheWithDetails(string $originalText, string $key, string $additionalData): array
    {
        $totalChars = 0; // Number of characters in ciphertext
        $changedChars = 0; // Number of changed characters in ciphertext
        $modifications = [];

        // Encrypt the original text
        $originalOutput = $this->encrypt($originalText, $key, $additionalData);
        if ($originalOutput === null) {
            Log::error('Encryption of original text failed during SAC calculation');
            return ['SAC' => 0, 'modifications' => []];
        }

        $totalChars = strlen($originalOutput);

        // Iterate through each character of the original text
        for ($charIndex = 0; $charIndex < strlen($originalText); $charIndex++) {
            // Flip one character of the original text
            $modifiedText = $originalText;
            $modifiedText[$charIndex] = chr(ord($modifiedText[$charIndex]) ^  (1 << ($charIndex % 8)));


            // Encrypt the modified text
            $encryptedModified = $this->encrypt($modifiedText, $key, $additionalData);

            if ($encryptedModified === null) {
                Log::error('Encryption with modified text failed during SAC calculation');
                continue;
            }

            // Count differing characters between original and modified outputs
            $charChanges = $this->countDifferingCharacters($originalOutput, $encryptedModified);
            $changedChars += $charChanges;

            // Calculate entropy
            $originalEntropy = $this->calculateEntropy($originalOutput);
            $modifiedEntropy = $this->calculateEntropy($encryptedModified);

            // Record details of this modification
            $modifications[] = $this->recordModification(
                $originalText,
                $modifiedText,
                $charIndex,
                $originalOutput,
                $encryptedModified,
                $charChanges,
                $originalEntropy,
                $modifiedEntropy
            );
        }

        // Calculate SAC
        $totalModifications = strlen($originalText);
        $sac = $changedChars / ($totalChars * $totalModifications);

        return [
            'SAC' => $sac,
            'total_chars' => $totalChars,
            'total_changed_chars' => $changedChars,
            'modifications' => $modifications,
        ];
    }

    private function countDifferingCharacters(string $str1, string $str2): int
    {
        $len = min(strlen($str1), strlen($str2));
        $differingCharacters = 0;

        for ($i = 0; $i < $len; $i++) {
            if ($str1[$i] !== $str2[$i]) {
                $differingCharacters++;
            }
        }

        return $differingCharacters;
    }


    private function recordModification(
        string $originalText,
        string $modifiedText,
        int $charIndex,
        string $originalOutput,
        string $encryptedModified,
        int $charChanges,
        float $originalEntropy,
        float $modifiedEntropy
    ): array {
        return [
            'position' => $charIndex,
            'original_char' => $originalText[$charIndex],
            'modified_char' => $modifiedText[$charIndex],
            'original_binary' => sprintf('%08b', ord($originalText[$charIndex])),
            'modified_binary' => sprintf('%08b', ord($modifiedText[$charIndex])),
            'original_text' => $originalText,
            'modified_text' => $modifiedText,
            'encrypted_original' => rtrim(base64_encode($originalOutput), '='),
            'encrypted_modified' => rtrim(base64_encode($encryptedModified), '='),
            'changed_chars' => $charChanges,
            'original_encrypted_length' => strlen($originalOutput),
            'original_entropy' => $originalEntropy,
            'modified_entropy' => $modifiedEntropy
        ];
    }

    private function calculateEntropy(string $data): float
    {
        $len = strlen($data);
        if ($len === 0) {
            return 0;
        }

        $frequencies = [];
        for ($i = 0; $i < $len; $i++) {
            $char = $data[$i];
            if (!isset($frequencies[$char])) {
                $frequencies[$char] = 0;
            }
            $frequencies[$char]++;
        }

        $entropy = 0;
        foreach ($frequencies as $count) {
            $probability = $count / $len;
            $entropy -= $probability * log($probability, 2);
        }

        return $entropy;
    }

    private function validateRequest(Request $request): void
    {
        $request->validate([
            'originaltext' => 'required|string',
            'key' => 'required|string',
            'additionalData' => 'required|string',
        ]);
    }

    private function encrypt(string $data, string $key, string $additionalData): ?string
    {
        // Generate nonce
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);

        if (!extension_loaded('sodium')) {
            Log::error('Sodium extension is not loaded.');
            throw new \RuntimeException('Sodium extension is required for encryption.');
        }

        if (strlen($key) !== SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES) {
            Log::error('Invalid key length for XChaCha20-Poly1305.');
            throw new \InvalidArgumentException('Invalid key length.');
        }

        try {
            $encrypted = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
                $data,
                $additionalData,
                $nonce,
                $key
            );

            if ($encrypted === false) {
                Log::error('Encryption failed.');
                return null;
            }

            // Prepend nonce to the encrypted data
            $encryptedWithNonce = $nonce . $encrypted;

            // dd(base64_encode($encryptedWithNonce),base64_encode($nonce),base64_encode($encrypted));

            return base64_encode($encryptedWithNonce);
        } catch (\SodiumException $e) {
            Log::error('Sodium encryption failed: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('Unexpected error during encryption: ' . $e->getMessage());
            return null;
        } finally {
            sodium_memzero($key);
        }
    }

    private function decrypt(string $encryptedData, string $key, string $additionalData): ?string
    {
        if (!extension_loaded('sodium')) {
            Log::error('Sodium extension is not loaded.');
            throw new \RuntimeException('Sodium extension is required for decryption.');
        }

        if (strlen($key) !== SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES) {
            Log::error('Invalid key length for XChaCha20-Poly1305.');
            throw new \InvalidArgumentException('Invalid key length.');
        }

        try {
            $decodedData = base64_decode($encryptedData, true);
            if ($decodedData === false) {
                Log::error('Invalid base64 encoding.');
                return null;
            }

            // Extract nonce
            $nonce = substr($decodedData, 0, SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
            $ciphertext = substr($decodedData, SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);

            $decrypted = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
                $ciphertext,
                $additionalData,
                $nonce,
                $key
            );

            if ($decrypted === false) {
                Log::error('Decryption failed.');
                return null;
            }

            return $decrypted;
        } catch (\SodiumException $e) {
            Log::error('Sodium decryption failed: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('Unexpected error during decryption: ' . $e->getMessage());
            return null;
        } finally {
            if (isset($decrypted)) {
                sodium_memzero($decrypted);
            }
            sodium_memzero($key);
        }
    }
}
