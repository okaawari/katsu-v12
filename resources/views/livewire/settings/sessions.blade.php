<section class="w-full" wire:poll.30s>
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Sessions')" :subheading="__('Manage your active sessions across all devices')">
        @if (session('message'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-700 dark:bg-green-900/20">
                <div class="flex items-center gap-3">
                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                        <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <p class="text-sm text-green-800 dark:text-green-200">{{ session('message') }}</p>
                </div>
            </div>
        @endif
        
        <div class="space-y-6">
            <!-- Current Session Info -->
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                        <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Current Session') }}</h3>
                        <p class="text-xs text-gray-500 dark:text-slate-400">{{ __('This is your current active session') }}</p>
                    </div>
                </div>
            </div>

            <!-- Sessions List -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Active Sessions') }}</h3>
                    <div class="flex items-center gap-2">
                        <flux:button 
                            variant="outline" 
                            size="sm" 
                            wire:click="cleanupDuplicateSessions"
                            wire:confirm="{{ __('This will remove duplicate sessions from the same device. Continue?') }}"
                        >
                            {{ __('Cleanup Duplicates') }}
                        </flux:button>
                        <flux:button 
                            variant="outline" 
                            size="sm" 
                            wire:click="refreshSessions"
                        >
                            {{ __('Refresh') }}
                        </flux:button>
                        @if(count($sessions) > 1)
                            <flux:button 
                                variant="outline" 
                                size="sm" 
                                wire:click="terminateAllOtherSessions"
                                wire:confirm="{{ __('Are you sure you want to terminate all other sessions?') }}"
                            >
                                {{ __('Terminate All Others') }}
                            </flux:button>
                        @endif
                    </div>
                </div>

                @forelse($sessions as $session)
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-slate-700 {{ $session['is_current'] ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-700' : 'bg-white dark:bg-slate-800' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-3">
                                <!-- Device Icon -->
                                <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $session['is_current'] ? 'bg-blue-100 dark:bg-blue-900' : 'bg-gray-100 dark:bg-slate-700' }}">
                                    @if($session['device_info']['device'] === 'Mobile')
                                        <svg class="h-5 w-5 {{ $session['is_current'] ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                    @elseif($session['device_info']['device'] === 'Tablet')
                                        <svg class="h-5 w-5 {{ $session['is_current'] ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5 {{ $session['is_current'] ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    @endif
                                </div>

                                <!-- Session Details -->
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $session['device_info']['browser'] }} on {{ $session['device_info']['os'] }}
                                        </h4>
                                        @if($session['is_current'])
                                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ __('Current') }}
                                            </span>
                                        @endif
                                        @if($session['session_count'] > 1)
                                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                {{ $session['session_count'] }} {{ __('sessions') }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="mt-1 space-y-1">
                                        <p class="text-xs text-gray-500 dark:text-slate-400">
                                            <span class="font-medium">{{ __('Device:') }}</span> {{ $session['device_info']['device'] }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400">
                                            <span class="font-medium">{{ __('IP Address:') }}</span> {{ $session['ip_address'] }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400">
                                            <span class="font-medium">{{ __('Last Activity:') }}</span> 
                                            {{ \Carbon\Carbon::createFromTimestamp($session['last_activity'])->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Button -->
                            @if(!$session['is_current'])
                                <flux:button 
                                    variant="outline" 
                                    size="sm" 
                                    wire:click="terminateSession('{{ $session['id'] }}')"
                                    wire:confirm="{{ __('Are you sure you want to terminate this session?') }}"
                                    class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                >
                                    {{ __('Terminate') }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-gray-200 bg-white p-8 text-center dark:border-slate-700 dark:bg-slate-800">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('No active sessions') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">{{ __('You are not currently signed in on any devices.') }}</p>
                    </div>
                @endforelse
            </div>

            <!-- Security Notice -->
            <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-700 dark:bg-yellow-900/20">
                <div class="flex items-start gap-3">
                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900">
                        <svg class="h-4 w-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">{{ __('Security Notice') }}</h4>
                        <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                            {{ __('If you notice any suspicious sessions, terminate them immediately. You can also change your password to force all sessions to re-authenticate.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </x-settings.layout>
</section>
