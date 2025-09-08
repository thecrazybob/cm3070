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
            <div x-show="showGDPRInfoModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div @click="showGDPRInfoModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                    
                    <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-3xl w-full max-h-[80vh] overflow-y-auto">
                        <div class="px-6 py-4 border-b dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">GDPR Compliance Information</h3>
                        </div>
                        <div class="p-6">
                            <pre x-text="gdprInfo" class="bg-gray-100 dark:bg-gray-900 p-4 rounded text-sm overflow-auto text-gray-800 dark:text-gray-200"></pre>
                        </div>
                        <div class="px-6 py-4 border-t dark:border-gray-700">
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
                            this.gdprInfo = JSON.stringify(result, null, 2);
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
</x-app-layout>