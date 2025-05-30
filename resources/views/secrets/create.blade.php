@extends('layouts.app')

@section('content')
  <div class="max-w-2xl mx-auto">
    {{-- Header Section --}}
    <div class="flex mb-4 justify-center items-center">
      <img src="{{ asset('fav2.svg') }}" alt="Vito Logo" style="width: 120px;">
    </div>

    {{-- Main Form Card --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
      <div class="px-8 pt-2 pb-10">
        <form class="space-y-8" action="/secrets" method="POST">
          @csrf

          {{-- Secret Content Section --}}
          <div class="space-y-2">
            <label class="block text-sm font-semibold text-gray-700" for="content">Secret Content</label>
            <p class="text-xs text-gray-500 mb-3">Enter the sensitive information you want to share securely</p>
            <textarea
              class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-colors resize-none"
              id="content" name="content" maxlength="190000" rows="6" placeholder="Enter your secret content here..."
              required></textarea>
            <div class="text-right">
              <span class="text-xs text-gray-400" id="char-count">0 / 190,000 characters</span>
            </div>
          </div>

          {{-- Password Protection Section --}}
          <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
            <div class="flex items-center justify-between mb-4">
              <div>
                <label class="text-sm font-semibold text-gray-700" for="require_password">Password Protection</label>
                <p class="text-xs text-gray-500 mt-1">Add an extra layer of security (optional)</p>
              </div>
              <label class="relative inline-block w-14 h-7">
                <input class="sr-only" id="require_password" name="require_password" type="checkbox"
                  onchange="togglePasswordFields()">
                <div class="block w-14 h-7 bg-gray-300 rounded-full transition-colors duration-200"></div>
                <div
                  class="dot absolute top-0.5 left-0.5 w-6 h-6 bg-white rounded-full transition-transform duration-200 ease-in-out shadow-sm">
                </div>
              </label>
            </div>

            <div class="hidden" id="password_fields">
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700" for="password">Password</label>
                <input
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-colors"
                  id="password" name="password" type="password" maxlength="100" placeholder="Enter a secure password">
                <p class="text-xs text-gray-500">Recipients will need this password to view the secret</p>
              </div>
            </div>
          </div>

          {{-- Submit Button --}}
          <div class="pt-4">
            <div class="text-center mb-3">
              <p class="text-xs text-gray-500">
                By creating your secret you agree to our
                <a class="text-primary-600 hover:text-primary-800 underline transition-colors duration-200"
                  href="{{ route('terms') }}">Terms of Use</a>
              </p>
            </div>
            <button
              class="w-full flex justify-center items-center py-4 px-6 border border-transparent rounded-lg shadow-sm text-base font-semibold text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200 hover:shadow-lg transform hover:scale-[1.02]"
              type="submit">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              Create Secure Secret
            </button>
          </div>
        </form>
      </div>
    </div>

    {{-- Security Information Section --}}
    <div class="mt-12 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-200 overflow-hidden">
      <div class="px-8 py-8">
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">How Vito Protects Your Secrets</h2>
          <p class="text-gray-600">Your security is our top priority</p>
        </div>

        <div class="grid md:grid-cols-2 gap-6 mb-8">
          {{-- End-to-End Encryption --}}
          <div class="bg-white rounded-lg p-6 border border-blue-100">
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 mb-2">End-to-End Encryption</h3>
                <p class="text-sm text-gray-600">Your secret is encrypted using industry-standard encryption before being
                  stored. The encryption key is embedded directly into the unique sharing URL, ensuring that even our
                  administrators cannot access your secret's contents.</p>
              </div>
            </div>
          </div>

          {{-- Single-Use Protection --}}
          <div class="bg-white rounded-lg p-6 border border-blue-100">
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0 w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 mb-2">Single-Use Protection</h3>
                <p class="text-sm text-gray-600">Each secret can only be accessed once. The moment someone opens your
                  sharing link and views the secret, it is permanently destroyed from our servers.</p>
              </div>
            </div>
          </div>

          {{-- Automatic Expiration --}}
          <div class="bg-white rounded-lg p-6 border border-blue-100">
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 mb-2">Automatic Expiration</h3>
                <p class="text-sm text-gray-600">All secrets automatically expire and are permanently deleted after one
                  hour, regardless of whether they have been accessed.</p>
              </div>
            </div>
          </div>

          {{-- Database Security --}}
          <div class="bg-white rounded-lg p-6 border border-blue-100">
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 mb-2">Database Security</h3>
                <p class="text-sm text-gray-600">Even in the unlikely event of a database breach, attackers would find
                  only encrypted data that cannot be decrypted without the corresponding URLs.</p>
              </div>
            </div>
          </div>
        </div>

        {{-- Critical Security Notice --}}
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-6 mb-6">
          <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
              <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-amber-900 mb-2">Critical Security Notice</h3>
              <h4 class="font-medium text-amber-800 mb-2">Important: Save Your Link Immediately</h4>
              <div class="text-sm text-amber-700 space-y-2">
                <p>Once you create a secret, we will display a unique sharing URL. <strong>This is the only time you will
                    ever see this link.</strong> We do not store the complete URL anywhere in our system – the encryption
                  key exists only in that link.</p>
                <p>If you navigate away from this page, refresh your browser, or close the tab before copying the link,
                  <strong>it will be lost forever and cannot be recovered.</strong> We cannot regenerate the link or
                  provide access to your secret by any other means.
                </p>
              </div>
            </div>
          </div>
        </div>

        {{-- Best Practices --}}
        <div class="bg-white rounded-lg border border-blue-100 p-6">
          <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
            </svg>
            Best Practices
          </h3>
          <ul class="text-sm text-gray-600 space-y-2">
            <li class="flex items-start space-x-2">
              <span class="flex-shrink-0 w-1.5 h-1.5 bg-blue-400 rounded-full mt-2"></span>
              <span>Copy and securely share the URL immediately after creation</span>
            </li>
            <li class="flex items-start space-x-2">
              <span class="flex-shrink-0 w-1.5 h-1.5 bg-blue-400 rounded-full mt-2"></span>
              <span>Verify the link works before sharing it with the intended recipient (remember not to decrypt the link
                or it will be destroyed)</span>
            </li>
            <li class="flex items-start space-x-2">
              <span class="flex-shrink-0 w-1.5 h-1.5 bg-blue-400 rounded-full mt-2"></span>
              <span>Remember that the secret will be destroyed after the first view or one hour, whichever comes
                first</span>
            </li>
            <li class="flex items-start space-x-2">
              <span class="flex-shrink-0 w-1.5 h-1.5 bg-blue-400 rounded-full mt-2"></span>
              <span>Do not bookmark or save the link – it will only work once</span>
            </li>
          </ul>
        </div>

        <div class="text-center mt-6 text-sm text-gray-600">
          <p class="italic">Your privacy and security are our top priorities. Vito is designed to ensure that sensitive
            information can be shared safely while maintaining complete confidentiality.</p>
        </div>
      </div>
    </div>

    <div class="text-center mt-6">
      {{-- Terms of Use Link --}}
      <a class="text-sm text-gray-500 hover:text-gray-700 underline transition-colors duration-200"
        href="{{ route('terms') }}">
        Terms of Use
      </a>
      {{-- github link --}}&nbsp;
      <a class="text-sm text-gray-500 hover:text-gray-700 underline transition-colors duration-200"
        href="https://github.com/GlobusGroup/vito">
        GitHub
      </a>
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
        dot.style.transform = 'translateX(28px)';
        bg.classList.replace('bg-gray-300', 'bg-primary-500');
        setTimeout(() => passwordInput.focus(), 100);
      } else {
        passwordFields.classList.add('hidden');
        passwordInput.value = '';
        dot.style.transform = 'translateX(0)';
        bg.classList.replace('bg-primary-500', 'bg-gray-300');
      }
    }

    // Character counter
    function updateCharCount() {
      const textarea = document.getElementById('content');
      const counter = document.getElementById('char-count');
      const count = textarea.value.length;
      counter.textContent = `${count.toLocaleString()} / 190,000 characters`;

      if (count > 180000) {
        counter.classList.add('text-red-500');
      } else if (count > 150000) {
        counter.classList.add('text-yellow-500');
      } else {
        counter.classList.remove('text-red-500', 'text-yellow-500');
      }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      const requirePasswordCheckbox = document.getElementById('require_password');
      const contentTextarea = document.getElementById('content');

      if (requirePasswordCheckbox.checked) {
        togglePasswordFields();
      }

      // Add character counter listener
      contentTextarea.addEventListener('input', updateCharCount);

      // Focus on content textarea
      contentTextarea.focus();
    });
  </script>
@endpush
