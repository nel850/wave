<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <nav>
                <div class="flex space-x-4">
                    <x-nav-link :href="route('recipients')" :active="request()->routeIs('recipients')" class="px-4 py-2 rounded-lg hover:bg-gray-100">
                        {{ __('Recipients') }}
                    </x-nav-link>
                    <x-nav-link :href="route('chat.index')" :active="request()->routeIs('chat.index')" class="px-4 py-2 rounded-lg hover:bg-gray-100">
                        {{ __('Chats') }}
                    </x-nav-link>
                </div>
            </nav>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Section -->
            <div class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="text-gray-100 text-sm mb-2">Total Recipients</div>
                    <div class="text-gray-100 text-3xl font-bold">{{ \App\Models\Recipient::count() }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="text-gray-100 text-sm mb-2">Active Conversations</div>
                    <div class="text-gray-100 text-3xl font-bold">{{ \App\Models\Conversation::count() }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <div class="text-gray-100 text-sm mb-2">Total Messages</div>
                    <div class="text-gray-100 text-3xl font-bold">{{ \App\Models\Message::count() }}</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <h3 class="text-gray-100 text-lg font-semibold mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <a href="{{ route('recipients') }}"
                           class="flex items-center justify-between p-4 bg-blue-50 hover:bg-blue-100 rounded-lg group transition-all">
                            <div>
                                <div class="font-semibold text-blue-700">Add New Recipient</div>
                                <div class="text-sm text-blue-600">Create and manage recipients</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500 group-hover:translate-x-2 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ route('chat.index') }}"
                           class="flex items-center justify-between p-4 bg-green-50 hover:bg-green-100 rounded-lg group transition-all">
                            <div>
                                <div class="font-semibold text-green-700">View Conversations</div>
                                <div class="text-sm text-green-600">Manage your chats</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500 group-hover:translate-x-2 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <h3 class="text-gray-100 text-lg font-semibold mb-4">Recent Activity</h3>
                    <div class="space-y-4">
                        @forelse(\App\Models\Message::with('conversation.recipient')->latest()->take(4)->get() as $message)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <div class="font-medium">{{ $message->conversation->recipient->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $message->body }}</div>
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ $message->created_at->diffForHumans() }}
                                </div>
                            </div>
                        @empty
                            <div class="text-gray-500 text-center py-4">No recent activity</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
 </x-app-layout>
