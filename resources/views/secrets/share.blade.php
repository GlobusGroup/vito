@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-green-600">{{ $message }}</h2>
                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-800">
                                <strong>Important:</strong> This link is single use, and will only be shown once. If you reload or leave this page, you won't be able to see it again. Make sure to copy it now!
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Share URL</label>
                <div class="bg-gray-50 p-4 rounded-md border border-gray-200 text-sm text-gray-800 break-words">
                    {{ $url }}
                </div>
            </div>

            <div class="flex justify-between items-center">
                <a href="{{ route('secrets.create') }}" class="text-sm text-gray-500 hover:text-gray-700 transition-colors duration-150">
                    Back
                </a>

                <button id="copy-url-btn" onclick="copyToClipboard('{{ $url }}')" class="inline-flex items-center px-6 py-2.5 bg-primary-500 text-white text-sm font-medium rounded-md hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-150">
                    Copy URL to Clipboard
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(text) {
        const copyBtn = document.getElementById('copy-url-btn');
        
        navigator.clipboard.writeText(text).then(() => {
            const originalText = copyBtn.textContent;
            copyBtn.textContent = 'Copied!';
            copyBtn.classList.add('bg-green-600', 'hover:bg-green-700', 'focus:bg-green-700');
            copyBtn.classList.remove('bg-primary-500', 'hover:bg-primary-600', 'focus:bg-primary-600');
            
            setTimeout(() => {
                copyBtn.textContent = originalText;
                copyBtn.classList.remove('bg-green-600', 'hover:bg-green-700', 'focus:bg-green-700');
                copyBtn.classList.add('bg-primary-500', 'hover:bg-primary-600', 'focus:bg-primary-600');
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    }
</script>
@endsection
