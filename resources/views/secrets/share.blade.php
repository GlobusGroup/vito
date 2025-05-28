@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-green-600">{{ $message }}</h2>
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
