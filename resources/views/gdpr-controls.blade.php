<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('GDPR Controls') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="gdprControlsApp()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- GDPR Rights Overview -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Your GDPR Rights</h3>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Right to Access</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Access all your personal data stored in our system
                            </p>
                        </div>
                        <div class="border-l-4 border-green-500 pl-4">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Right to Portability</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Export your data in a structured, machine-readable format
                            </p>
                        </div>
                        <div class="border-l-4 border-yellow-500 pl-4">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Right to Rectification</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Correct or update your personal data at any time
                            </p>
                        </div>
                        <div class="border-l-4 border-red-500 pl-4">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Right to Erasure</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Delete your account and all associated data permanently
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Management Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Data Management</h3>
                    <div class="space-y-4">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button @click="exportData"
                                    :disabled="exportLoading"
                                    :class="exportLoading ? 'opacity-50 cursor-not-allowed' : ''"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                                <span x-show="!exportLoading">Export My Data</span>
                                <span x-show="exportLoading">Exporting...</span>
                            </button>
                            <button @click="viewAuditLog"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                View Access Log
                            </button>
                            <button @click="viewGDPRInfo"
                                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
                                GDPR Compliance Info
                            </button>
                        </div>
                        
                        <!-- Export Result -->
                        <div x-show="exportResult" class="mt-4">
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-4">
                                <p class="text-sm text-green-800 dark:text-green-200">
                                    Data export completed successfully. Your file has been downloaded.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Deletion -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border-2 border-red-200 dark:border-red-900">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-red-600 dark:text-red-400 mb-4">Danger Zone</h3>
                    <div class="space-y-4">
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4">
                            <h4 class="text-sm font-medium text-red-800 dark:text-red-200 mb-2">Delete Account</h4>
                            <p class="text-sm text-red-600 dark:text-red-400 mb-4">
                                Once you delete your account, all of your data will be permanently removed. This action cannot be undone.
                            </p>
                            <button @click="confirmDeleteAccount"
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                                Delete My Account Permanently
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GDPR Information Modal -->
            <div x-show="showGDPRInfoModal" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @keydown.escape.window="showGDPRInfoModal = false"
                 class="fixed inset-0 z-50 overflow-y-auto">
                
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Background overlay -->
                    <div x-show="showGDPRInfoModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         @click="showGDPRInfoModal = false"
                         class="fixed inset-0 bg-black/50 dark:bg-black/60"></div>
                    
                    <!-- This element is to trick the browser into centering the modal contents. -->
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    
                    <!-- Modal panel -->
                    <div x-show="showGDPRInfoModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                        
                        <div class="px-6 py-4 border-b dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">GDPR Compliance Information</h3>
                                <button @click="showGDPRInfoModal = false"
                                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="p-6 max-h-[60vh] overflow-y-auto">
                            <template x-if="gdprInfo">
                                <div class="space-y-6">
                                    <!-- Your GDPR Rights -->
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Your GDPR Rights</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <template x-for="(right, key) in gdprInfo.gdpr_rights" :key="key">
                                                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                                    <div class="flex items-start">
                                                        <div class="flex-shrink-0">
                                                            <template x-if="key === 'right_to_access'">
                                                                <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                                </svg>
                                                            </template>
                                                            <template x-if="key === 'right_to_portability'">
                                                                <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                                                                </svg>
                                                            </template>
                                                            <template x-if="key === 'right_to_erasure'">
                                                                <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                </svg>
                                                            </template>
                                                            <template x-if="key === 'right_to_rectification'">
                                                                <svg class="h-6 w-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                </svg>
                                                            </template>
                                                        </div>
                                                        <div class="ml-3 flex-1">
                                                            <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100 capitalize" x-text="key.replace(/_/g, ' ')"></h5>
                                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400" x-text="right.description"></p>
                                                            <div class="mt-2 flex items-center">
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" 
                                                                      :class="right.available ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'">
                                                                    <template x-if="right.available">
                                                                        <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                                        </svg>
                                                                    </template>
                                                                    <span x-text="right.available ? 'Available' : 'Not Available'"></span>
                                                                </span>
                                                            </div>
                                                            <code class="mt-2 text-xs text-gray-500 dark:text-gray-400 block" x-text="right.endpoint"></code>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Data Processing Information -->
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Data Processing Information</h4>
                                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-4">
                                            <div>
                                                <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300">Processing Purposes</h5>
                                                <ul class="mt-2 space-y-1">
                                                    <template x-for="purpose in gdprInfo.data_processing.purposes" :key="purpose">
                                                        <li class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                                            <svg class="h-4 w-4 text-blue-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                            </svg>
                                                            <span x-text="purpose"></span>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </div>
                                            
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300">Legal Basis</h5>
                                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400" x-text="gdprInfo.data_processing.legal_basis"></p>
                                                </div>
                                                <div>
                                                    <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300">Retention Period</h5>
                                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400" x-text="gdprInfo.data_processing.retention_period"></p>
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300">Data Categories</h5>
                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    <template x-for="category in gdprInfo.data_processing.data_categories" :key="category">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200" x-text="category"></span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Your Account Statistics -->
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Your Account Statistics</h4>
                                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Account Created</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100" x-text="new Date(gdprInfo.user_statistics.account_created).toLocaleDateString()"></dd>
                                                </div>
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Contexts</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100" x-text="gdprInfo.user_statistics.contexts_count"></dd>
                                                </div>
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Profile Attributes</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100" x-text="gdprInfo.user_statistics.total_profile_attributes"></dd>
                                                </div>
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Access Logs</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100" x-text="gdprInfo.user_statistics.access_logs_count"></dd>
                                                </div>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        <div class="px-6 py-4 border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                            <button @click="showGDPRInfoModal = false"
                                    class="w-full sm:w-auto px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Audit Log Modal -->
        <x-alpine-modal show="showAuditLog" max-width="6xl" title="Access Audit Log">
            <div class="p-4">
                <livewire:access-log-table />
            </div>
            <x-slot name="footer">
                <button type="button" @click="showAuditLog = false"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                    Close
                </button>
            </x-slot>
        </x-alpine-modal>

    </div>

    <script>
        function gdprControlsApp() {
            return {
                // Loading states
                exportLoading: false,
                
                // Modal states
                showAuditLog: false,
                showGDPRInfoModal: false,
                
                // Data
                exportResult: false,
                gdprInfo: null,

                // Methods
                async exportData() {
                    this.exportLoading = true;
                    this.exportResult = false;
                    
                    try {
                        const response = await fetch('/api/export-data', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Authorization': 'Bearer {{ session("api_token") }}'
                            }
                        });
                        
                        if (response.ok) {
                            const result = await response.json();
                            const blob = new Blob([JSON.stringify(result.data, null, 2)], { type: 'application/json' });
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = `gdpr-data-export-${new Date().toISOString().split('T')[0]}.json`;
                            a.click();
                            URL.revokeObjectURL(url);
                            this.exportResult = true;
                            
                            // Hide success message after 5 seconds
                            setTimeout(() => {
                                this.exportResult = false;
                            }, 5000);
                        } else {
                            alert('Failed to export data');
                        }
                    } catch (error) {
                        alert('Failed to export data: ' + error.message);
                    } finally {
                        this.exportLoading = false;
                    }
                },

                viewAuditLog() {
                    this.showAuditLog = true;
                },

                async viewGDPRInfo() {
                    try {
                        const response = await fetch('/api/gdpr-info', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Authorization': 'Bearer {{ session("api_token") }}'
                            }
                        });
                        
                        if (response.ok) {
                            const result = await response.json();
                            this.gdprInfo = result;
                            this.showGDPRInfoModal = true;
                        } else {
                            alert('Failed to load GDPR information');
                        }
                    } catch (error) {
                        alert('Failed to load GDPR information: ' + error.message);
                    }
                },

                confirmDeleteAccount() {
                    if (!confirm('Are you sure you want to permanently delete your account? This cannot be undone.')) {
                        return;
                    }

                    if (!confirm('This is your final warning. All your data will be permanently deleted. Are you absolutely sure?')) {
                        return;
                    }

                    const password = prompt('Please enter your password to confirm account deletion:');
                    if (!password) return;

                    fetch('/api/delete-account', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Authorization': 'Bearer {{ session("api_token") }}'
                        },
                        body: JSON.stringify({
                            password: password,
                            confirmation: true
                        })
                    }).then(response => {
                        if (response.ok) {
                            alert('Your account has been deleted successfully');
                            window.location.href = '/';
                        } else {
                            response.json().then(data => {
                                alert(data.message || 'Failed to delete account');
                            });
                        }
                    }).catch(error => {
                        alert('Failed to delete account: ' + error.message);
                    });
                }
            }
        }
    </script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-app-layout>