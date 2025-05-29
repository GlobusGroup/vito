@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header Section --}}
    <div class="flex mb-8 justify-center items-center">
        <img src="{{ asset('fav2.svg') }}" alt="Vito Logo" style="width: 120px;">
    </div>

    {{-- Main Content Card --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="px-8 py-10">
            {{-- Page Title --}}
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Terms of Use</h1>
                <p class="text-gray-600">Please read these terms carefully before using Vito</p>
            </div>

            {{-- Terms Content --}}
            <div class="prose prose-gray max-w-none space-y-8">
                {{-- Use at Your Own Risk --}}
                <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-red-900 mb-3">USE AT YOUR OWN RISK</h2>
                            <p class="text-sm text-red-800">Vito is provided "as is" without warranty of any kind, either express or implied, including but not limited to the implied warranties of merchantability, fitness for a particular purpose, or non-infringement. We make no guarantees regarding the availability, reliability, or security of the service.</p>
                        </div>
                    </div>
                </div>

                {{-- No Liability Section --}}
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900 mb-3 flex items-center">
                        <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        No Liability
                    </h2>
                    <p class="text-gray-700 leading-relaxed">Under no circumstances shall we be liable for any direct, indirect, incidental, special, consequential, or punitive damages arising out of or relating to your use of Vito, including but not limited to data loss, unauthorized access, service interruptions, or any other damages.</p>
                </div>

                {{-- User Responsibility Section --}}
                <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                    <h2 class="text-xl font-semibold text-blue-900 mb-3 flex items-center">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        User Responsibility
                    </h2>
                    <p class="text-blue-800 leading-relaxed">You acknowledge and agree that you use Vito entirely at your own risk. You are solely responsible for determining whether this service is appropriate for your specific use case and for ensuring compliance with applicable laws and regulations.</p>
                </div>

                {{-- No Guarantees Section --}}
                <div class="bg-yellow-50 rounded-lg p-6 border border-yellow-200">
                    <h2 class="text-xl font-semibold text-yellow-900 mb-3 flex items-center">
                        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        No Guarantees
                    </h2>
                    <p class="text-yellow-800 leading-relaxed">While we implement security measures designed to protect your data, we cannot guarantee absolute security. No system is completely secure, and we make no warranties regarding the prevention of unauthorized access, data breaches, or other security incidents.</p>
                </div>

                {{-- Service Availability Section --}}
                <div class="bg-purple-50 rounded-lg p-6 border border-purple-200">
                    <h2 class="text-xl font-semibold text-purple-900 mb-3 flex items-center">
                        <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Service Availability
                    </h2>
                    <p class="text-purple-800 leading-relaxed">We reserve the right to modify, suspend, or discontinue the service at any time without notice. We do not guarantee continuous availability or uptime.</p>
                </div>

                {{-- Limitation of Damages Section --}}
                <div class="bg-indigo-50 rounded-lg p-6 border border-indigo-200">
                    <h2 class="text-xl font-semibold text-indigo-900 mb-3 flex items-center">
                        <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                        Limitation of Damages
                    </h2>
                    <p class="text-indigo-800 leading-relaxed">In no event shall our total liability exceed the amount you paid for using this service (which is zero for all users).</p>
                </div>

                {{-- Agreement Section --}}
                <div class="bg-green-50 rounded-lg p-6 border border-green-200">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-green-900 mb-3">Agreement</h2>
                            <p class="text-green-800 leading-relaxed">By using Vito, you acknowledge that you have read, understood, and agree to be bound by this disclaimer.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Back to Home Link --}}
    <div class="text-center mt-8">
        <a href="{{ route('secrets.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-primary-600 bg-primary-50 hover:bg-primary-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Home
        </a>
    </div>
</div>
@endsection