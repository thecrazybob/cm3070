<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('API Demo') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="apiDemoApp()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- API Demo Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Test Profile Retrieval API</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Test Parameters</label>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <select x-model="testUserId" class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded px-3 py-2">
                                    <option value="{{ $user->id }}">Your Profile ({{ $user->name }})</option>
                                    @if($user->id != 1)
                                        <option value="1">Arda's Profile</option>
                                    @endif
                                    @if($user->id != 2)
                                        <option value="2">Elif's Profile</option>
                                    @endif
                                </select>
                                <select x-model="testContext" class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded px-3 py-2">
                                    <option value="">Default Context</option>
                                    <option value="university">University</option>
                                    <option value="gaming">Gaming</option>
                                    <option value="work">Work</option>
                                    <option value="formal">Formal</option>
                                </select>
                                <button @click="testProfileRetrieval"
                                        :disabled="apiLoading"
                                        :class="apiLoading ? 'opacity-50 cursor-not-allowed' : ''"
                                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
                                    <span x-show="!apiLoading">Test API</span>
                                    <span x-show="apiLoading">Loading...</span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- API Endpoint Display -->
                        <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API Endpoint:</p>
                            <code class="text-sm text-blue-600 dark:text-blue-400" x-text="apiEndpoint"></code>
                        </div>
                        
                        <!-- API Response -->
                        <div x-show="apiResult">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API Response:</label>
                            <pre x-text="apiResult" class="bg-gray-100 dark:bg-gray-900 p-4 rounded text-sm overflow-auto max-h-96 text-gray-800 dark:text-gray-200"></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- API Documentation -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">API Documentation</h3>
                    <div class="space-y-4">
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Profile Retrieval Endpoint</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                <strong>GET</strong> <code>/api/view/profile/{userId}?context={contextSlug}</code>
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                Retrieves public profile information for a user within the specified context.
                            </p>
                        </div>

                        <div class="border-l-4 border-green-500 pl-4">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Parameters</h4>
                            <ul class="text-sm text-gray-600 dark:text-gray-400 mt-2 space-y-1">
                                <li><code>userId</code> - The ID of the user whose profile to retrieve</li>
                                <li><code>context</code> (optional) - The context slug to filter profile attributes</li>
                            </ul>
                        </div>

                        <div class="border-l-4 border-purple-500 pl-4">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Response Format</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                Returns a JSON object containing the user's name and profile attributes visible within the requested context.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function apiDemoApp() {
            return {
                // API demo
                testUserId: '{{ $user->id }}',
                testContext: '',
                apiResult: null,
                apiLoading: false,
                apiEndpoint: '',

                // Methods
                async testProfileRetrieval() {
                    this.apiLoading = true;
                    const url = `/api/view/profile/${this.testUserId}${this.testContext ? '?context=' + this.testContext : ''}`;
                    this.apiEndpoint = window.location.origin + url;

                    try {
                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        
                        const result = await response.json();
                        this.apiResult = JSON.stringify(result, null, 2);
                    } catch (error) {
                        this.apiResult = 'Error: ' + error.message;
                    } finally {
                        this.apiLoading = false;
                    }
                },

                // Initialize endpoint display
                init() {
                    this.$watch('testUserId', () => this.updateEndpoint());
                    this.$watch('testContext', () => this.updateEndpoint());
                    this.updateEndpoint();
                },

                updateEndpoint() {
                    const url = `/api/view/profile/${this.testUserId}${this.testContext ? '?context=' + this.testContext : ''}`;
                    this.apiEndpoint = window.location.origin + url;
                }
            }
        }
    </script>
</x-app-layout>