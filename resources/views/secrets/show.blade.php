@extends('layouts.app')

@section('title', 'Show Secret')

@section('content')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="space-y-6">
                        {{-- Secret Display --}}
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex flex-col items-center justify-between">
                                <div class="flex-1">
                                    <div id="secret-display" class="text-2xl font-mono tracking-wider">
                                        ••••••••••••••••••••••
                                    </div>
                                </div>
                                <div class="flex space-x-2 mt-5">
                                    <button id="reveal-btn" 
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Reveal
                                    </button>
                                    <button id="copy-btn"
                                            class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Copy
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Password Input (if required) --}}
                        @if($secret['requires_password'])
                        <div id="password-section" class="hidden">
                            <div class="mt-4">
                                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                <input type="password" 
                                       id="password" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Enter password">
                            </div>
                        </div>
                        @endif

                        {{-- Expiration Info --}}
                        @if($secret['valid_until'])
                        <div class="text-sm text-gray-500">
                            This secret will expire on: {{ \Carbon\Carbon::parse($secret['valid_until'])->format('F j, Y g:i A') }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const secretDisplay = document.getElementById('secret-display');
            const revealBtn = document.getElementById('reveal-btn');
            const copyBtn = document.getElementById('copy-btn');
            const passwordSection = document.getElementById('password-section');
            let isRevealed = false;
            let secretContent = null;

            revealBtn.addEventListener('click', async function() {
                if (!isRevealed) {
                    if (@json($secret['requires_password'])) {
                        const password = document.getElementById('password').value;
                        if (!password) {
                            passwordSection.classList.remove('hidden');
                            return;
                        }
                    }

                    try {
                        const response = await fetch(`/api/secrets/{{ $secret['id'] }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                s: @json($decryption_key),
                                password: @json($secret['requires_password']) ? document.getElementById('password').value : null
                            })
                        });

                        if (!response.ok) {
                            throw new Error('Failed to fetch secret');
                        }

                        const data = await response.json();
                        secretContent = data.content;
                        secretDisplay.textContent = secretContent;
                        revealBtn.textContent = 'Hide';
                        isRevealed = true;
                    } catch (error) {
                        console.log('error', error);
                        alert('Failed to reveal secret. Please try again.');
                    }
                } else {
                    secretDisplay.textContent = '••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••';
                    revealBtn.textContent = 'Reveal';
                    isRevealed = false;
                }
            });

            copyBtn.addEventListener('click', function() {
                if (secretContent) {
                    navigator.clipboard.writeText(secretContent).then(() => {
                        const originalText = copyBtn.textContent;
                        copyBtn.textContent = 'Copied!';
                        setTimeout(() => {
                            copyBtn.textContent = originalText;
                        }, 2000);
                    });
                }
            });
        });
    </script>
    @endpush
@endsection

