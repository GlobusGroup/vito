@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-8">
            <div class="space-y-6">
                {{-- Secret Display --}}
                <div>
                    @if($requires_password)
                    <div id="password-section">
                        <div class="mb-4">
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" maxlength="100" id="password" class="px-2 py-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Enter password">
                        </div>
                    </div>
                    @endif
                    <div class="w-full">
                        <div id="secret-display" class="text-2xl font-mono tracking-wider text-center bg-gray-50 p-4 rounded border border-gray-200 flex justify-center">
                            <button id="decrypt-btn" class="inline-flex items-center px-4 py-2 bg-primary-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-600 focus:bg-primary-600 active:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Decrypt
                            </button>
                        </div>
                    </div>

                    <div id="action-buttons" class="hidden flex justify-center space-x-3 mt-6">
                        <button id="reveal-btn" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Reveal
                        </button>
                        <button id="copy-btn" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const secretDisplay = document.getElementById('secret-display');
        const decryptBtn = document.getElementById('decrypt-btn');
        const revealBtn = document.getElementById('reveal-btn');
        const copyBtn = document.getElementById('copy-btn');
        const actionButtons = document.getElementById('action-buttons');
        const passwordSection = document.getElementById('password-section');
        const passwordInput = document.getElementById('password');

        let isDecrypted = false;
        let isRevealed = false;
        let secretContent = null;
        let isDecrypting = false;

        if (@json($requires_password)) {
            document.getElementById('password').focus();
        }

        // Add Enter key event listener for password input
        if (passwordInput) {
            passwordInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (!isDecrypted) {
                        decryptBtn.click();
                    } else {
                        revealBtn.click();
                    }
                }
            });
        }

        decryptBtn.addEventListener('click', async function() {
            if (isDecrypting) {
                return; // Prevent multiple clicks while decrypting
            }

            if (@json($requires_password)) {
                const password = document.getElementById('password').value;
                if (!password) {
                    passwordSection.classList.remove('hidden');
                    return;
                }
            }

            try {
                // Start loading state
                isDecrypting = true;
                decryptBtn.disabled = true;
                decryptBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Decrypting...
                `;
                decryptBtn.classList.add('opacity-75', 'cursor-not-allowed');

                const response = await fetch(`/secrets/decrypt`, {
                    method: 'POST'
                    , headers: {
                        'Content-Type': 'application/json'
                        , 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    , }
                    , body: JSON.stringify({
                        d: @json($d)
                        , password: @json($requires_password) ? document.getElementById('password').value : null
                    })
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch secret');
                }

                const data = await response.json();
                secretContent = data.content;
                isDecrypted = true;

                // Replace decrypt button with dots and show action buttons
                secretDisplay.innerHTML = '<span class="text-2xl font-mono tracking-wider">••••••••••••••••••••••</span>';
                secretDisplay.classList.add('border-green-200', 'bg-green-50');
                secretDisplay.classList.remove('flex', 'justify-center');
                actionButtons.classList.remove('hidden');
                actionButtons.classList.add('flex');
                if (passwordSection) {
                    passwordSection.classList.add('hidden');
                }
            } catch (error) {
                console.log('error', error);
                alert('Failed to decrypt secret. Please try again.');
                window.location.reload();
            } finally {
                // Reset loading state
                isDecrypting = false;
                decryptBtn.disabled = false;
                decryptBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                if (!isDecrypted) {
                    decryptBtn.textContent = 'Decrypt';
                }
            }
        });

        revealBtn.addEventListener('click', function() {
            if (!isDecrypted) return;

            if (!isRevealed) {
                // Show the secret
                secretDisplay.innerHTML = `<span class="text-2xl font-mono tracking-wider">${secretContent}</span>`;
                revealBtn.textContent = 'Hide';
                isRevealed = true;
            } else {
                // Hide the secret
                secretDisplay.innerHTML = '<span class="text-2xl font-mono tracking-wider">••••••••••••••••••••••</span>';
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
