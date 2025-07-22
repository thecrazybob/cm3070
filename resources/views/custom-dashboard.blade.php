<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Identity & Profile Management Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="dashboardApp()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Welcome Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-bold mb-2">Welcome, {{ $user->name }}!</h3>
                    <p class="text-gray-600">Manage your contexts and profile attributes below.</p>
                </div>
            </div>

            <!-- Contexts Overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Your Contexts</h3>
                        <button @click="showCreateModal = true"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Create New Context
                        </button>
                    </div>

                    @if($contexts->count() > 0)
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            @foreach($contexts as $context)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-medium text-gray-900">{{ $context->name }}</h4>
                                        @if($context->is_default)
                                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Default</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">{{ $context->slug }}</p>
                                    @if($context->description)
                                        <p class="text-sm text-gray-500 mb-3">{{ $context->description }}</p>
                                    @endif

                                    <div class="text-xs text-gray-500 mb-3">
                                        {{ $context->profileValues->count() }} attributes
                                    </div>

                                    <div class="flex space-x-2">
                                        <button @click="viewContext({{ $context->id }})"
                                                class="text-blue-600 hover:text-blue-800 text-sm">View</button>
                                        <button @click="editContext({{ $context->id }})"
                                                class="text-green-600 hover:text-green-800 text-sm">Edit</button>
                                        @if(!$context->is_default)
                                            <button @click="deleteContext({{ $context->id }})"
                                                    class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-500 mb-4">You don't have any contexts yet.</div>
                            <button @click="showCreateModal = true"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">
                                Create Your First Context
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- API Demo Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">API Demo</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Test Profile Retrieval</label>
                            <div class="flex space-x-2">
                                <select x-model="testUserId" class="border border-gray-300 rounded px-3 py-2">
                                    <option value="{{ $user->id }}">Your Profile ({{ $user->name }})</option>
                                    @if($user->id != 1)
                                        <option value="1">Arda's Profile</option>
                                    @endif
                                    @if($user->id != 2)
                                        <option value="2">Elif's Profile</option>
                                    @endif
                                </select>
                                <select x-model="testContext" class="border border-gray-300 rounded px-3 py-2">
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
                        <div>
                            <pre x-show="apiResult" x-text="apiResult" class="bg-gray-100 p-4 rounded text-sm overflow-auto max-h-64 text-gray-800"></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GDPR Controls -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">GDPR Controls</h3>
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
                            <button @click="confirmDeleteAccount"
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                                Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- View Context Modal -->
        <div x-show="showViewModal" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-90"
             class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showViewModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showViewModal = false"></div>

                <div x-show="showViewModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4" x-text="viewingContext.name + ' Context'"></h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600">Slug: <span x-text="viewingContext.slug"></span></p>
                                <p x-show="viewingContext.description" class="text-sm text-gray-600">Description: <span x-text="viewingContext.description"></span></p>
                                <p class="text-sm text-gray-600">Default: <span x-text="viewingContext.is_default ? 'Yes' : 'No'"></span></p>
                                <p class="text-sm text-gray-600">Active: <span x-text="viewingContext.is_active ? 'Yes' : 'No'"></span></p>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="font-medium text-gray-900">Attributes</h4>
                                    <button @click="addAttribute"
                                            class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                                        Add Attribute
                                    </button>
                                </div>
                                <template x-if="viewingContext.attributes && viewingContext.attributes.length > 0">
                                    <ul class="space-y-2">
                                        <template x-for="attr in viewingContext.attributes" :key="attr.id">
                                            <li class="bg-gray-50 p-3 rounded flex justify-between items-center">
                                                <div>
                                                    <span class="font-medium" x-text="attr.attribute.display_name"></span>:
                                                    <span x-text="attr.value"></span>
                                                    <span class="text-xs text-gray-500">(<span x-text="attr.visibility"></span>)</span>
                                                </div>
                                                <div class="flex space-x-2">
                                                    <button @click="editAttribute(attr)"
                                                            class="text-xs text-blue-600 hover:text-blue-800">Edit</button>
                                                    <button @click="deleteAttribute(attr.id)"
                                                            class="text-xs text-red-600 hover:text-red-800">Delete</button>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                </template>
                                <template x-else>
                                    <p class="text-gray-500">No attributes defined</p>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="showViewModal = false"
                                class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Context Modal -->
        <div x-show="showEditModal" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-90"
             class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showEditModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showEditModal = false"></div>

                <div x-show="showEditModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="updateContext">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Context</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                    <input type="text" x-model="editingContext.name" required
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Slug</label>
                                    <input type="text" x-model="editingContext.slug" required
                                           pattern="[a-z0-9-]+"
                                           title="Only lowercase letters, numbers, and hyphens"
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea x-model="editingContext.description" rows="3"
                                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                                </div>
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" x-model="editingContext.is_active"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Active</span>
                                    </label>
                                </div>
                            </div>

                            <div x-show="editError" x-text="editError" class="mt-2 text-red-600 text-sm"></div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    :disabled="editLoading"
                                    :class="editLoading ? 'opacity-50 cursor-not-allowed' : ''"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                <span x-show="!editLoading">Update</span>
                                <span x-show="editLoading">Updating...</span>
                            </button>
                            <button type="button" @click="showEditModal = false"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Attribute Modal -->
        <div x-show="showAddAttributeModal" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-90"
             class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showAddAttributeModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showAddAttributeModal = false"></div>

                <div x-show="showAddAttributeModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="storeAttribute">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Attribute</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Key Name</label>
                                    <input type="text" x-model="newAttribute.key_name" required
                                           pattern="[a-z0-9_]+"
                                           title="Only lowercase letters, numbers, and underscores"
                                           placeholder="e.g., full_name, email, phone_number"
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Display Name</label>
                                    <input type="text" x-model="newAttribute.display_name" required
                                           placeholder="e.g., Full Name, Email Address, Phone Number"
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Value</label>
                                    <input type="text" x-model="newAttribute.value" required
                                           placeholder="Enter the attribute value"
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Data Type</label>
                                    <select x-model="newAttribute.data_type" required
                                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="string">String</option>
                                        <option value="email">Email</option>
                                        <option value="url">URL</option>
                                        <option value="phone">Phone</option>
                                        <option value="number">Number</option>
                                        <option value="date">Date</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Visibility</label>
                                    <select x-model="newAttribute.visibility" required
                                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="private">Private (only owner can see)</option>
                                        <option value="protected">Protected (authenticated users)</option>
                                        <option value="public">Public (everyone can see)</option>
                                    </select>
                                </div>
                            </div>

                            <div x-show="addAttributeError" x-text="addAttributeError" class="mt-2 text-red-600 text-sm"></div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    :disabled="addAttributeLoading"
                                    :class="addAttributeLoading ? 'opacity-50 cursor-not-allowed' : ''"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                <span x-show="!addAttributeLoading">Add Attribute</span>
                                <span x-show="addAttributeLoading">Adding...</span>
                            </button>
                            <button type="button" @click="cancelAddAttribute"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Create Context Modal -->
        <div x-show="showCreateModal" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-90"
             class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showCreateModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showCreateModal = false"></div>

                <div x-show="showCreateModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="createContext">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Context</h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                    <input type="text" x-model="newContext.name" required
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                                    <input type="text" x-model="newContext.slug" required
                                           pattern="[a-z0-9-]+"
                                           title="Only lowercase letters, numbers, and hyphens"
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description (optional)</label>
                                    <textarea x-model="newContext.description" rows="3"
                                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                                </div>
                            </div>

                            <div x-show="createError" x-text="createError" class="mt-2 text-red-600 text-sm"></div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    :disabled="createLoading"
                                    :class="createLoading ? 'opacity-50 cursor-not-allowed' : ''"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                <span x-show="!createLoading">Create</span>
                                <span x-show="createLoading">Creating...</span>
                            </button>
                            <button type="button" @click="cancelCreateContext"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        function dashboardApp() {
            return {
                // Modal states
                showCreateModal: false,
                showViewModal: false,
                showEditModal: false,
                showAddAttributeModal: false,
                showAuditLog: false,

                // Form data
                newContext: {
                    name: '',
                    slug: '',
                    description: ''
                },
                newAttribute: {
                    key_name: '',
                    display_name: '',
                    value: '',
                    data_type: 'string',
                    visibility: 'private'
                },

                // API demo
                testUserId: '{{ $user->id }}',
                testContext: '',
                apiResult: null,
                apiLoading: false,

                // Loading states
                createLoading: false,
                editLoading: false,
                exportLoading: false,
                addAttributeLoading: false,

                // Errors
                createError: null,
                editError: null,
                addAttributeError: null,
                currentContextId: null,

                // Methods
                async createContext() {
                    this.createLoading = true;
                    this.createError = null;

                    try {
                        const response = await fetch('/api/contexts', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Authorization': 'Bearer {{ session("api_token") }}'
                            },
                            body: JSON.stringify(this.newContext)
                        });

                        if (response.ok) {
                            window.location.reload();
                        } else {
                            const result = await response.json();
                            this.createError = result.message || 'Failed to create context';
                        }
                    } catch (error) {
                        this.createError = error.message;
                    } finally {
                        this.createLoading = false;
                    }
                },

                cancelCreateContext() {
                    this.showCreateModal = false;
                    this.newContext = { name: '', slug: '', description: '' };
                    this.createError = null;
                },

                async viewContext(id) {
                    try {
                        const response = await fetch(`/api/contexts/${id}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Authorization': 'Bearer {{ session("api_token") }}'
                            }
                        });
                        
                        if (response.ok) {
                            const result = await response.json();
                            this.viewingContext = result.context;
                            this.showViewModal = true;
                        } else {
                            alert('Failed to load context');
                        }
                    } catch (error) {
                        alert('Failed to view context: ' + error.message);
                    }
                },

                async editContext(id) {
                    try {
                        const response = await fetch(`/api/contexts/${id}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Authorization': 'Bearer {{ session("api_token") }}'
                            }
                        });
                        
                        if (response.ok) {
                            const result = await response.json();
                            this.editingContext = {
                                id: result.context.id,
                                name: result.context.name,
                                slug: result.context.slug,
                                description: result.context.description || '',
                                is_active: result.context.is_active
                            };
                            this.showEditModal = true;
                        } else {
                            alert('Failed to load context for editing');
                        }
                    } catch (error) {
                        alert('Failed to load context: ' + error.message);
                    }
                },

                async updateContext() {
                    this.editLoading = true;
                    this.editError = null;

                    try {
                        const response = await fetch(`/api/contexts/${this.editingContext.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Authorization': 'Bearer {{ session("api_token") }}'
                            },
                            body: JSON.stringify({
                                name: this.editingContext.name,
                                slug: this.editingContext.slug,
                                description: this.editingContext.description,
                                is_active: this.editingContext.is_active
                            })
                        });

                        if (response.ok) {
                            window.location.reload();
                        } else {
                            const result = await response.json();
                            this.editError = result.message || 'Failed to update context';
                        }
                    } catch (error) {
                        this.editError = error.message;
                    } finally {
                        this.editLoading = false;
                    }
                },

                async deleteContext(id) {
                    if (!confirm('Are you sure you want to delete this context?')) {
                        return;
                    }

                    try {
                        const response = await fetch(`/api/contexts/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Authorization': 'Bearer {{ session("api_token") }}'
                            }
                        });
                        
                        if (response.ok) {
                            window.location.reload();
                        } else {
                            alert('Failed to delete context');
                        }
                    } catch (error) {
                        alert('Failed to delete context: ' + error.message);
                    }
                },

                async testProfileRetrieval() {
                    this.apiLoading = true;
                    const url = `/api/view/profile/${this.testUserId}${this.testContext ? '?context=' + this.testContext : ''}`;

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

                async exportData() {
                    this.exportLoading = true;
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
                            a.download = 'my-data-export.json';
                            a.click();
                            URL.revokeObjectURL(url);
                        } else {
                            alert('Failed to export data');
                        }
                    } catch (error) {
                        alert('Failed to export data: ' + error.message);
                    } finally {
                        this.exportLoading = false;
                    }
                },

                async viewAuditLog() {
                    try {
                        const response = await fetch('/api/audit-log', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Authorization': 'Bearer {{ session("api_token") }}'
                            }
                        });
                        
                        if (response.ok) {
                            const result = await response.json();
                            alert('Audit log: ' + JSON.stringify(result, null, 2));
                        } else {
                            alert('Failed to retrieve audit log');
                        }
                    } catch (error) {
                        alert('Failed to retrieve audit log: ' + error.message);
                    }
                },

                confirmDeleteAccount() {
                    if (!confirm('Are you sure you want to permanently delete your account? This cannot be undone.')) {
                        return;
                    }

                    const password = prompt('Please enter your password to confirm:');
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
                            alert('Account deleted successfully');
                            window.location.href = '/login';
                        } else {
                            alert('Failed to delete account');
                        }
                    }).catch(error => {
                        alert('Failed to delete account: ' + error.message);
                    });
                },

                // Attribute management functions (for the view modal)
                addAttribute() {
                    this.currentContextId = this.viewingContext.id;
                    this.showAddAttributeModal = true;
                },

                async storeAttribute() {
                    this.addAttributeLoading = true;
                    this.addAttributeError = null;

                    try {
                        const response = await fetch(`/api/contexts/${this.currentContextId}/attributes`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Authorization': 'Bearer {{ session("api_token") }}'
                            },
                            body: JSON.stringify(this.newAttribute)
                        });

                        if (response.ok) {
                            // Refresh the view modal by reloading the context
                            await this.viewContext(this.currentContextId);
                            this.cancelAddAttribute();
                        } else {
                            const result = await response.json();
                            this.addAttributeError = result.message || 'Failed to add attribute';
                        }
                    } catch (error) {
                        this.addAttributeError = error.message;
                    } finally {
                        this.addAttributeLoading = false;
                    }
                },

                cancelAddAttribute() {
                    this.showAddAttributeModal = false;
                    this.newAttribute = {
                        key_name: '',
                        display_name: '',
                        value: '',
                        data_type: 'string',
                        visibility: 'private'
                    };
                    this.addAttributeError = null;
                    this.currentContextId = null;
                },

                editAttribute(attr) {
                    alert(`Edit attribute functionality would allow editing of "${attr.attribute.display_name}"`);
                },

                async deleteAttribute(id) {
                    if (!confirm('Are you sure you want to delete this attribute?')) {
                        return;
                    }
                    alert('Delete attribute functionality would remove this attribute from the context.');
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-app-layout>