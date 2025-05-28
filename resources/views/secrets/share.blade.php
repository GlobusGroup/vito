@extends('layouts.app')

@section('title', 'Share Secret')

@section('content')
<div class="bg-gray-100 py-12 flex items-center justify-center">
    <div class="w-full max-w-3xl px-6">
        <div class="bg-white shadow-xl rounded-xl p-10">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-green-600">{{ $message }}</h2>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Share URL</label>
                <div class="bg-gray-50 p-4 rounded-md border border-gray-200 text-sm text-gray-800 break-words">
                    {{ $url }}
                </div>
            </div>

            <div class="flex justify-between">
                <div class="text-center">
                    <a href="{{ route('secrets.create') }}" class="text-sm text-gray-500 hover:text-gray-700">
                        Back
                    </a>
                </div>

                <div class="text-center">
                    <button onclick="copyToClipboard('{{ $url }}')" class="inline-flex items-center px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Copy URL to Clipboard
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            // show a success message, but not in a popup
            const successMessage = document.createElement('div');
            successMessage.textContent = 'URL copied to clipboard!';
            successMessage.classList.add('text-green-500');
            successMessage.classList.add('text-sm');
            successMessage.classList.add('font-medium');
            successMessage.classList.add('rounded-md');
            successMessage.classList.add('bg-green-50');
            successMessage.classList.add('p-2');
            successMessage.classList.add('mt-2');
            successMessage.classList.add('text-center');
            successMessage.classList.add('absolute');
            successMessage.classList.add('top-0');
            successMessage.classList.add('left-0');
            successMessage.classList.add('right-0');
            successMessage.classList.add('z-50');
            successMessage.classList.add('bg-white');
            successMessage.classList.add('shadow-lg');
            document.body.appendChild(successMessage);
            setTimeout(() => {
                successMessage.remove()
            }, 2000);

        }).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    }

</script>
@endsection
