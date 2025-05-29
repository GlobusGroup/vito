@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-8">
            <div class="space-y-6">
                {{-- Secret Display --}}
                <div>
                    <div class="w-full">
                        <div id="secret-display" class="text-2xl font-mono tracking-wider text-center bg-gray-50 p-4 rounded border border-gray-200">
                            ••••••••••••••••••••••
                        </div>
                    </div>
                     @if($secret['requires_password'])
                        <div id="password-section">
                            <div class="mt-4">
                                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                <input type="password" 
                                    maxlength="255"
                                    id="password" 
                                    class="px-2 py-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="Enter password">
                            </div>
                        </div>
                    @endif
                    <div class="flex justify-center space-x-3 mt-6">
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
        let isDecrypting = false;

        if (@json($secret['requires_password'])) {
            document.getElementById('password').focus();
        }

        revealBtn.addEventListener('click', async function() {
            if (isDecrypting) {
                return; // Prevent multiple clicks while decrypting
            }

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
                    // Start loading state
                    isDecrypting = true;
                    revealBtn.disabled = true;
                    revealBtn.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Decrypting...
                    `;
                    revealBtn.classList.add('opacity-75', 'cursor-not-allowed');
                    secretDisplay.textContent = 'Decrypting...';

                    const response = await fetch(`/secrets/{{ $secret['id'] }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
                    window.location.reload();
                } finally {
                    // Reset loading state
                    isDecrypting = false;
                    revealBtn.disabled = false;
                    revealBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                    if (!isRevealed) {
                        revealBtn.textContent = 'Reveal';
                        secretDisplay.textContent = '••••••••••••••••••••••';
                    }
                }
            } else {
                secretDisplay.textContent = '••••••••••••••';
                revealBtn.textContent = 'Reveal';
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

