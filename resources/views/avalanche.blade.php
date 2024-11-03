<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avalanche Effect</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100 text-black flex items-center justify-center min-h-screen">
    <div class="w-full p-8 bg-gray-100 rounded-lg">
        <!-- Results Section -->
        <div class="mt-8 space-y-4">
            <!-- Check if response exists -->
            @if (isset($response))
                <h2 class="text-xl font-bold">Avalanche Effect Results on xChaCha20-Poly1305 Encryption Algorithm</h2>

                <!-- Display SAC, total characters, and total changed characters -->
                <div class="mt-4 text-lg">
                    <p>Avalanche Effect: <strong> {{ $response['SAC'] }}</strong></p>
                </div>

                <!-- Modifications Table -->
                <div class="mt-6">
                    <h3 class="text-lg font-semibold">Modifications</h3>
                    <table class="w-full table-auto border-collapse mt-4">
                        <thead>
                            <tr>
                                <th class="border px-4 py-2">Position</th>
                                <th class="border px-4 py-2">Original Text Char</th>
                                <th class="border px-4 py-2">Modified Text Char</th>
                                <th class="border px-4 py-2">Original Text Binary</th>
                                <th class="border px-4 py-2">Modified Text Binary</th>
                                <th class="border px-4 py-2">Original Text</th>
                                <th class="border px-4 py-2">Modified Text</th>
                                <th class="border px-4 py-2">Changed Characters</th>
                                <th class="border px-4 py-2">Original encrypted length</th>
                                <th class="border px-4 py-2">Original Entropy</th>
                                <th class="border px-4 py-2">Modified Entropy</th>
                                <th class="border px-4 py-2">Encrypted Original</th>
                                <th class="border px-4 py-2">Encrypted Modified</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($response['modifications'] as $modification)
                                <tr>
                                    <td class="border px-4 py-2">{{ $modification['position'] }}</td>
                                    <td class="border px-4 py-2">{{ $modification['original_char'] }}</td>
                                    <td class="border px-4 py-2">{{ $modification['modified_char'] }}</td>
                                    <td class="border px-4 py-2">{{ $modification['original_binary'] }}</td>
                                    <td class="border px-4 py-2">{{ $modification['modified_binary'] }}</td>
                                    <td class="border px-4 py-2">{{ $modification['original_text'] }}</td>
                                    <td class="border px-4 py-2">{{ $modification['modified_text'] }}</td>
                                    <td class="border px-4 py-2">{{ $modification['changed_chars'] }}</td>
                                    <td class="border px-4 py-2">{{ $modification['original_encrypted_length'] }}</td>
                                    <td class="border px-4 py-2">{{ $modification['original_entropy'] }}</td>
                                    <td class="border px-4 py-2">{{ $modification['modified_entropy'] }}</td>
                                    <td class="border px-4 py-2">{{ $modification['encrypted_original'] }}</td>
                                    <td class="border px-4 py-2">{{ $modification['encrypted_modified'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- BIC Matrix Table -->
                {{-- here --}}
            @else
                <p>No data available.</p>
            @endif
        </div>

        {{-- citation --}}
        <div class="mt-8">
            <p class="text-sm text-gray-500">
                A good encryption algorithm should always satisfy the following relation: Avalanche effect ≥ 50%
            </p>
            <a href="https://doi.org/10.3390/electronics11040613" class="text-blue-500 underline">
                Corona-Bermúdez E, Chimal-Eguía JC, Téllez-Castillo G. Cryptographic Services Based on Elementary
                and Chaotic Cellular Automata. Electronics. 2022
            </a>
        </div>
    </div>
</body>

</html>