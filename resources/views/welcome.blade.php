<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avalance Effect</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">
    <div class="max-w-4xl w-full p-8 bg-gray-800 rounded-lg shadow-lg">

        <!-- Container for Two Columns -->
        <div class="flex l md:flex-row space-y-8 md:space-y-0 md:space-x-8">

            <!-- Avalanche Form -->

            <form id="avalanceForm" method="POST" action="{{ route('avalanche') }}" class="w-full ">
                @csrf
                <div class="text-center mb-6">
                    <p class="text-xl font-semibold text-gray-200 border-b-2 border-transparent hover:border-gray-400">
                        Avalanche Effect on xChaCha20-Poly1305 Encryption Algorithm
                    </p>
                </div>

                {{-- Text --}}
                <div class="mb-6">
                    <label for="file" class="block mb-2 text-sm font-medium">1. Input Text </label>
                    <textarea id="originaltext" name="originaltext" rows="2"
                        class="block w-full px-3 py-2 bg-gray-700 rounded-md text-white focus:outline-none focus:border-blue-300 transition duration-150"></textarea>
                </div>

                <div class="mb-6">
                    <label for="key" class="block mb-2 text-sm font-medium">2. Enter a key</label>
                    <div class="relative">
                        <input type="text" id="key" name="key" placeholder="Enter password"
                            class="block w-full px-3 py-2 bg-gray-700 rounded-md text-white focus:outline-none focus:border-blue-300 transition duration-150">
                        <button type="button"
                            onclick="document.getElementById('key').value = btoa(String.fromCharCode(...crypto.getRandomValues(new Uint8Array(32))))"
                            class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-white">
                            <i class="fas fa-random"></i>
                        </button>
                    </div>
                </div>
                {{-- <div class="mb-6">
                    <label for="nonce" class="block mb-2 text-sm font-medium">3. Enter a nonce</label>
                    <div class="relative">
                        <input type="text" id="nonce" name="nonce" placeholder="Enter nonce"
                            class="block w-full px-3 py-2 bg-gray-700 rounded-md text-white focus:outline-none focus:border-blue-300 transition duration-150">
                        <button type="button"
                            onclick="document.getElementById('nonce').value = btoa(String.fromCharCode(...crypto.getRandomValues(new Uint8Array(24))))"
                            class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-white">
                            <i class="fas fa-random"></i>
                        </button>
                    </div>
                </div> --}}

                <div class="mb-6">
                    <label for="additionalData" class="block mb-2 text-sm font-medium">4. Enter additional
                        data</label>
                    <div class="relative">
                        <input type="text" id="additionalData" name="additionalData"
                            placeholder="Enter additional data"
                            class="block w-full px-3 py-2 bg-gray-700 rounded-md text-white focus:outline-none focus:border-blue-300 transition duration-150">
                    </div>
                </div>

                <button type="submit"
                    class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-md text-white transition duration-150">
                    Test
                </button>
            </form>


        </div>
        <!-- Results Section -->
        <div class="mt-8 space-y-4">

        </div>
    </div>

</body>

</html>
