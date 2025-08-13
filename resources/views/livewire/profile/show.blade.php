<div class="min-h-screen bg-gray-50 dark:bg-slate-900">
    <!-- Background Image -->
    <div class="relative h-64 md:h-80 lg:h-96">
        <img src="{{ $this->getBackgroundPictureUrl() }}" 
             alt="Background" 
             class="w-full h-full object-cover">
        {{-- <div class="absolute inset-0 bg-black bg-opacity-50"></div> --}}
        
        <!-- Back Button -->
        <div class="absolute top-4 left-4">
            <a href="{{ url()->previous() }}" 
               class="inline-flex items-center px-3 py-2 bg-white bg-opacity-20 backdrop-blur-sm rounded-lg text-slate-900 hover:bg-opacity-30 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('Back') }}
            </a>
        </div>

        <!-- Edit Profile Button (if viewing own profile) -->
        @if(auth()->check() && auth()->id() === $user->id)
            <div class="absolute top-4 right-4">
                <a href="{{ route('dashboard.settings.profile') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 backdrop-blur-sm rounded-lg text-slate-900 hover:bg-opacity-30 transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    {{ __('Edit Profile') }}
                </a>
            </div>
        @endif
    </div>

    <!-- Profile Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 relative z-10">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-lg overflow-hidden">
            <!-- Profile Header -->
            <div class="relative px-6 py-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-4 sm:space-y-0 sm:space-x-6">
                    <!-- Profile Picture -->
                    <div class="relative">
                        <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-full overflow-hidden border-4 border-white dark:border-slate-700 shadow-lg">
                            <img src="{{ $this->getProfilePictureUrl() }}" 
                                 alt="{{ $user->name }}" 
                                 class="w-full h-full object-cover">
                        </div>
                        
                        <!-- Online Status Indicator -->
                        <div class="absolute bottom-2 right-2 w-6 h-6 bg-green-500 rounded-full border-2 border-white dark:border-slate-700"></div>
                    </div>

                    <!-- User Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-3 mb-2">
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                                {{ $user->name }}
                            </h1>
                            
                            @if(auth()->check() && auth()->id() === $user->id)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ __('You') }}
                                </span>
                            @endif
                        </div>
                        
                        <p class="text-gray-600 dark:text-slate-400 mb-4">
                            {{ $user->email }}
                        </p>

                        <!-- Member Since -->
                        <div class="flex items-center text-sm text-gray-500 dark:text-slate-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ __('Member since') }} {{ $user->created_at->format('F Y') }}
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    @if(auth()->check() && auth()->id() !== $user->id)
                        <div class="flex space-x-3">
                            <button class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                {{ __('Message') }}
                            </button>
                            
                            <button class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                {{ __('Follow') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Profile Stats -->
            <div class="border-t border-gray-200 dark:border-slate-700">
                <div class="grid grid-cols-3 divide-x divide-gray-200 dark:divide-slate-700">
                    <div class="px-6 py-4 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">0</div>
                        <div class="text-sm text-gray-500 dark:text-slate-400">{{ __('Posts') }}</div>
                    </div>
                    <div class="px-6 py-4 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">0</div>
                        <div class="text-sm text-gray-500 dark:text-slate-400">{{ __('Followers') }}</div>
                    </div>
                    <div class="px-6 py-4 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">0</div>
                        <div class="text-sm text-gray-500 dark:text-slate-400">{{ __('Following') }}</div>
                    </div>
                </div>
            </div>

            <!-- Profile Content Tabs -->
            <div class="border-t border-gray-200 dark:border-slate-700">
                <nav class="flex space-x-8 px-6">
                    <button class="py-4 px-1 border-b-2 border-blue-500 text-blue-600 dark:text-blue-400 font-medium text-sm">
                        {{ __('About') }}
                    </button>
                    <button class="py-4 px-1 border-b-2 border-transparent text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-300 font-medium text-sm">
                        {{ __('Posts') }}
                    </button>
                    <button class="py-4 px-1 border-b-2 border-transparent text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-300 font-medium text-sm">
                        {{ __('Photos') }}
                    </button>
                </nav>
                <!-- About Section -->
                <div class="px-6 py-8">
                    <div class="prose prose-gray dark:prose-invert max-w-none">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('About') }}</h3>
                        <p class="text-gray-600 dark:text-slate-400">
                            {{ __('This is a sample profile page. You can customize this section to show more information about the user, their bio, interests, or any other details you want to display.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
