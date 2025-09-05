<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Identity & Profile Management API</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <!-- Navigation -->
            <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center justify-between">
                        <div class="flex items-center">
                            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                                Identity Management API
                            </h1>
                        </div>
                        @if (Route::has('login'))
                            <div class="flex items-center space-x-4">
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                        Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                        Login
                                    </a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                                            Register
                                        </a>
                                    @endif
                                @endauth
                            </div>
                        @endif
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
                <!-- Project Overview -->
                <div class="mb-12">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                        Contextual Identity & Profile Management System
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-2">
                        CM3070 Computer Science Final Project
                    </p>
                    <p class="text-gray-600 dark:text-gray-400 max-w-3xl">
                        This project implements a RESTful API for managing contextual digital identities. 
                        Users can create multiple identity profiles and control information visibility based on 
                        context and requester identity.
                    </p>
                </div>

                <!-- System Features -->
                <div class="mb-12">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">
                        System Features
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                Multiple Identity Contexts
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Users can create and manage multiple identity profiles (e.g., professional, personal, academic) 
                                with different information sets for each context.
                            </p>
                        </div>

                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                Granular Privacy Controls
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Each profile attribute can have individual visibility settings (public, authenticated, private) 
                                allowing precise control over information sharing.
                            </p>
                        </div>

                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                Token-Based Authentication
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Secure API access using Laravel Sanctum with personal access tokens for authentication 
                                and authorization.
                            </p>
                        </div>

                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                Comprehensive Audit Logging
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                All profile access attempts are logged with details including requester identity, 
                                timestamp, and response status for security monitoring.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Technical Implementation -->
                <div class="mb-12">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">
                        Technical Implementation
                    </h2>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                        <dl class="space-y-4">
                            <div>
                                <dt class="font-medium text-gray-900 dark:text-white">Backend Framework:</dt>
                                <dd class="text-gray-600 dark:text-gray-400">Laravel 12.x with PHP 8.4</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-900 dark:text-white">Database:</dt>
                                <dd class="text-gray-600 dark:text-gray-400">MySQL with Eloquent ORM</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-900 dark:text-white">Authentication:</dt>
                                <dd class="text-gray-600 dark:text-gray-400">Laravel Sanctum for API token management</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-900 dark:text-white">Frontend:</dt>
                                <dd class="text-gray-600 dark:text-gray-400">Blade templates with Tailwind CSS v4, Livewire v3, and Alpine.js</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-900 dark:text-white">API Documentation:</dt>
                                <dd class="text-gray-600 dark:text-gray-400">OpenAPI/Swagger specification with Scramble</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- API Endpoints -->
                <div class="mb-12">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">
                        Core API Endpoints
                    </h2>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded">GET</span>
                                <code class="text-sm text-gray-600 dark:text-gray-400">/api/contexts</code>
                                <span class="text-sm text-gray-500">- List all user contexts</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded">POST</span>
                                <code class="text-sm text-gray-600 dark:text-gray-400">/api/contexts</code>
                                <span class="text-sm text-gray-500">- Create new context</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded">GET</span>
                                <code class="text-sm text-gray-600 dark:text-gray-400">/api/contexts/{id}</code>
                                <span class="text-sm text-gray-500">- Get context details</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded">PUT</span>
                                <code class="text-sm text-gray-600 dark:text-gray-400">/api/contexts/{id}</code>
                                <span class="text-sm text-gray-500">- Update context</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded">DELETE</span>
                                <code class="text-sm text-gray-600 dark:text-gray-400">/api/contexts/{id}</code>
                                <span class="text-sm text-gray-500">- Delete context</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded">GET</span>
                                <code class="text-sm text-gray-600 dark:text-gray-400">/api/profile/{token}</code>
                                <span class="text-sm text-gray-500">- Access profile via sharing token</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Getting Started -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-blue-900 dark:text-blue-100 mb-4">
                        Getting Started
                    </h2>
                    <ol class="list-decimal list-inside space-y-2 text-blue-800 dark:text-blue-200">
                        <li>Register for an account using the registration link above</li>
                        <li>Log in to access your dashboard</li>
                        <li>Create your first identity context</li>
                        <li>Add profile attributes with appropriate visibility settings</li>
                        <li>Generate API tokens for programmatic access</li>
                        <li>View the API documentation at <a href="/docs/api" class="underline">/docs/api</a></li>
                    </ol>
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-12">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                        University of London | Computer Science BSc | {{ date('Y') }}
                    </p>
                </div>
            </footer>
        </div>
    </body>
</html>