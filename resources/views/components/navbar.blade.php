<nav class="bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800" x-data="{ open: false }">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('welcome') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-white" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link href="{{ route('welcome') }}" :active="request()->routeIs('welcome')" @click="$dispatch('navigation-start')">
                        {{ __('Home') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('browse') }}" :active="request()->routeIs('browse')" @click="$dispatch('navigation-start')">
                        {{ __('Browse') }}
                    </x-nav-link>
                    <x-nav-link href="#" :active="false" @click="$dispatch('navigation-start')">
                        {{ __('Explore') }}
                    </x-nav-link>
                    <x-nav-link href="#" :active="false" @click="$dispatch('navigation-start')">
                        {{ __('Marketplace') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <!-- Theme Toggle -->
                <div class="ml-3 relative">
                    <button @click="toggleTheme()" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                        <!-- Sun icon for dark mode -->
                        <svg x-show="!isDark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <!-- Moon icon for light mode -->
                        <svg x-show="isDark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>
                </div>

                <!-- Settings Dropdown -->
                @auth
                    <div class="ml-3 relative">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-900 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-800 active:bg-gray-50 dark:active:bg-gray-800 transition ease-in-out duration-150">
                                        <!-- User Avatar -->
                                        <div class="flex items-center">
                                            <img class="h-8 w-8 rounded-full object-cover mr-2" src="{{ auth()->user()->profile_picture_url ?? '/images/default-profile.svg' }}" alt="{{ auth()->user()->name }}">
                                            <span class="hidden md:block">{{ auth()->user()->name }}</span>
                                        </div>

                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            </x-slot>

                            <x-slot name="content">
                                <!-- User Info -->
                                <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                                </div>

                                <!-- Account Management -->
                                <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">
                                    {{ __('Manage Account') }}
                                </div>

                                <x-dropdown-link href="{{ route('dashboard.profile.show') }}">
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                <x-dropdown-link href="{{ route('dashboard.settings.profile') }}">
                                    {{ __('Settings') }}
                                </x-dropdown-link>

                                <x-dropdown-link href="{{ route('dashboard.index') }}">
                                    {{ __('Dashboard') }}
                                </x-dropdown-link>

                                <div class="border-t border-gray-200 dark:border-gray-700"></div>

                                <!-- Authentication -->
                                <form method="POST" action="{{ route('logout') }}" x-data>
                                    @csrf

                                    <x-dropdown-link href="{{ route('logout') }}"
                                             @click.prevent="$root.submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @else
                    <!-- Guest Menu -->
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 px-3 py-2 text-sm font-medium transition-colors duration-200">
                            {{ __('Sign in') }}
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="bg-gray-800 dark:bg-gray-700 hover:bg-gray-700 dark:hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                {{ __('Sign up') }}
                            </a>
                        @endif
                    </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="{{ route('welcome') }}" :active="request()->routeIs('welcome')" @click="$dispatch('navigation-start')">
                {{ __('Home') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('browse') }}" :active="request()->routeIs('browse')" @click="$dispatch('navigation-start')">
                {{ __('Browse') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="#" :active="false" @click="$dispatch('navigation-start')">
                {{ __('Explore') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="#" :active="false" @click="$dispatch('navigation-start')">
                {{ __('Marketplace') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        @auth
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ auth()->user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link href="{{ route('dashboard.profile.show') }}" :active="request()->routeIs('dashboard.profile.show')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link href="{{ route('dashboard.settings.profile') }}" :active="request()->routeIs('dashboard.settings.profile')">
                        {{ __('Settings') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link href="{{ route('dashboard.index') }}" :active="request()->routeIs('dashboard.index')">
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}" x-data>
                        @csrf

                        <x-responsive-nav-link href="{{ route('logout') }}"
                                   @click.prevent="$root.submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700">
                <div class="px-4 space-y-1">
                    <a href="{{ route('login') }}" class="block px-4 py-2 text-base font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition duration-150 ease-in-out">
                        {{ __('Sign in') }}
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="block px-4 py-2 text-base font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition duration-150 ease-in-out">
                            {{ __('Sign up') }}
                        </a>
                    @endif
                </div>
            </div>
        @endauth
    </div>
</nav>
