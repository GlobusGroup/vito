@extends('layouts.app')

@section('title', 'Show Secret')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-8">
            <div class="space-y-6">
                {{-- Secret Display --}}
                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                    <div class="flex flex-col items-center justify-between">
                        <div class="flex-1 w-full">
                            <div id="secret-display" class="text-2xl font-mono tracking-wider text-center bg-white p-4 rounded border border-gray-200">
                                ••••••••••••••••••••••
                            </div>
                        </div>
                        <div class="flex space-x-3 mt-6">
                            <button id="reveal-btn" 
                                    class="inline-flex items-center px-4 py-2 bg-primary-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Reveal
                            </button>
                            <button id="copy-btn"
                                    class="hidden inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
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
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const secretDisplay = document.getElementById('secret-display');
        const revealBtn = document.getElementById('reveal-btn');
        const copyBtn = document.getElementById('copy-btn');
        const passwordSection = document.getElementById('password-section');
        let isRevealed = false;
        let secretContent = null;
        let decryptedContent = null;

        revealBtn.addEventListener('click', async function() {
            if (decryptedContent && !isRevealed) {
                secretDisplay.textContent = decryptedContent;
                revealBtn.textContent = 'Hide';
                copyBtn.classList.remove('hidden');
                isRevealed = true;
                return;
            }
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
                    decryptedContent = secretContent;
                    revealBtn.textContent = 'Hide';
                    copyBtn.classList.remove('hidden');
                    isRevealed = true;
                } catch (error) {
                    console.log('error', error);
                    alert('Failed to reveal secret. Please try again.');
                }
            } else {
                secretDisplay.textContent = '••••••••••••••';
                revealBtn.textContent = 'Show';
                isRevealed = false;
            }
        });

        copyBtn.addEventListener('click', function() {
            if (secretContent) {
                navigator.clipboard.writeText(secretContent).then(() => {
                    const originalText = copyBtn.textContent;
                    copyBtn.textContent = 'Copied!';
                    copyBtn.classList.add('bg-green-600', 'hover:bg-green-700', 'focus:bg-green-700');
                    copyBtn.classList.remove('bg-gray-600', 'hover:bg-gray-700', 'focus:bg-gray-700');
                    
                    setTimeout(() => {
                        copyBtn.textContent = originalText;
                        copyBtn.classList.remove('bg-green-600', 'hover:bg-green-700', 'focus:bg-green-700');
                        copyBtn.classList.add('bg-gray-600', 'hover:bg-gray-700', 'focus:bg-gray-700');
                    }, 2000);
                });
            }
        });
    });
</script>
@endpush
@endsection

