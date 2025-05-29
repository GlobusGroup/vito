@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-8">Add New Secret</h1>

            <form action="{{ route('secrets.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700">Secret Content</label>
                    <textarea name="content" maxlength="200000" id="content" rows="4" class="px-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required></textarea>
                </div>

                <div class="flex items-center justify-between">
                    <label for="require_password" class="text-sm font-medium text-gray-700">Require Password <small class="text-gray-400">(optional)</small></label>

                    <label class="relative inline-block w-12 h-6">
                        <input type="checkbox" id="require_password" name="require_password" class="sr-only" onchange="togglePasswordFields()">
                        <div class="block w-12 h-6 bg-gray-300 rounded-full transition-colors"></div>
                        <div class="dot absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-transform duration-200 ease-in-out"></div>
                    </label>
                </div>

                <div id="password_fields" class="hidden space-y-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" maxlength="255" id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 px-2">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label for="has_expiration" class="text-sm font-medium text-gray-700">Set Expiration Time <small class="text-gray-400">(optional)</small></label>
                    <label class="relative inline-block w-12 h-6 cursor-pointer">
                        <input type="checkbox" id="has_expiration" name="has_expiration" class="sr-only" onchange="toggleExpirationField()">
                        <div class="block w-12 h-6 bg-gray-300 rounded-full transition-colors"></div>
                        <div class="dot absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-transform duration-200 ease-in-out"></div>
                    </label>
                </div>

                <div id="expiration_field" class="hidden">
                    <label for="valid_for" class="block text-sm font-medium text-gray-700">Valid for (minutes, default 30 days)</label>
                    <input type="number" name="valid_for" id="valid_for" min="1" max="42000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 px-2">
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-150">
                        Create Secret
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePasswordFields() {
        const checkbox = document.getElementById('require_password');
        const passwordFields = document.getElementById('password_fields');
        const passwordInput = document.getElementById('password');

        const dot = checkbox.nextElementSibling.nextElementSibling;
        const bg = checkbox.nextElementSibling;

        if (checkbox.checked) {
            passwordFields.classList.remove('hidden');
            dot.style.transform = 'translateX(24px)';
            bg.classList.replace('bg-gray-300', 'bg-primary-500');
            setTimeout(() => passwordInput.focus(), 100);
        } else {
            passwordFields.classList.add('hidden');
            passwordInput.value = '';
            dot.style.transform = 'translateX(0)';
            bg.classList.replace('bg-primary-500', 'bg-gray-300');
        }
    }

    function toggleExpirationField() {
        const checkbox = document.getElementById('has_expiration');
        const expirationField = document.getElementById('expiration_field');
        const validForInput = document.getElementById('valid_for');

        const bg = checkbox.nextElementSibling;
        const dot = checkbox.nextElementSibling.nextElementSibling;

        if (checkbox.checked) {
            expirationField.classList.remove('hidden');
            validForInput.setAttribute('required', '');
            dot.style.transform = 'translateX(24px)';
            bg.classList.replace('bg-gray-300', 'bg-primary-500');
            setTimeout(() => validForInput.focus(), 100);
        } else {
            expirationField.classList.add('hidden');
            validForInput.removeAttribute('required');
            validForInput.value = '';
            dot.style.transform = 'translateX(0)';
            bg.classList.replace('bg-primary-500', 'bg-gray-300');
        }
    }

    // Initialize toggle states on page load
    document.addEventListener('DOMContentLoaded', function() {
        const requirePasswordCheckbox = document.getElementById('require_password');
        const hasExpirationCheckbox = document.getElementById('has_expiration');

        if (requirePasswordCheckbox.checked) {
            togglePasswordFields();
        }

        if (hasExpirationCheckbox.checked) {
            toggleExpirationField();
        }
    });

</script>
@endpush
