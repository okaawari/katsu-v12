<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your profile information and pictures')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-8">
            <!-- Profile Pictures Section -->
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Profile Pictures') }}</h3>
                
                <!-- Background Picture Upload -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                            {{ __('Background Picture') }}
                        </label>
                        <div class="relative">
                            <div class="w-full h-48 rounded-lg overflow-hidden bg-gray-100 dark:bg-slate-800 border-2 border-dashed border-gray-300 dark:border-slate-600 hover:border-gray-400 dark:hover:border-slate-500 transition-colors">
                                <img src="{{ $this->getBackgroundPictureUrl() }}" 
                                     alt="Background" 
                                     class="w-full h-full object-cover"
                                     wire:loading.class="opacity-50">
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-slate-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-slate-400">
                                            <span class="font-medium">{{ __('Click to upload') }}</span> {{ __('or drag and drop') }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-slate-500">{{ __('PNG, JPG, GIF up to 5MB (16:9 ratio)') }}</p>
                                    </div>
                                </div>
                            </div>
                            <input type="file" 
                                   wire:model="backgroundPicture" 
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                   accept="image/*">
                        </div>
                        @error('backgroundPicture') 
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Profile Picture Upload -->
                    <div class="flex items-center space-x-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                {{ __('Profile Picture') }}
                            </label>
                            <div class="relative">
                                <div class="w-32 h-32 rounded-full overflow-hidden bg-gray-100 dark:bg-slate-800 border-2 border-dashed border-gray-300 dark:border-slate-600 hover:border-gray-400 dark:hover:border-slate-500 transition-colors">
                                    <img src="{{ $this->getProfilePictureUrl() }}" 
                                         alt="Profile" 
                                         class="w-full h-full object-cover"
                                         wire:loading.class="opacity-50">
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="text-center">
                                            <svg class="mx-auto h-8 w-8 text-gray-400 dark:text-slate-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <input type="file" 
                                       wire:model="profilePicture" 
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                       accept="image/*">
                            </div>
                            @error('profilePicture') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ __('Profile Picture Guidelines') }}</h4>
                            <ul class="text-xs text-gray-600 dark:text-slate-400 space-y-1">
                                <li>• {{ __('Square format (1:1 ratio)') }}</li>
                                <li>• {{ __('Maximum 2MB file size') }}</li>
                                <li>• {{ __('PNG, JPG, or GIF format') }}</li>
                                <li>• {{ __('Clear, well-lit photo recommended') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Information Section -->
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Profile Information') }}</h3>
                
                <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

                <div>
                    <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                        <div>
                            <flux:text class="mt-4">
                                {{ __('Your email address is unverified.') }}

                                <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                    {{ __('Click here to re-send the verification email.') }}
                                </flux:link>
                            </flux:text>

                            @if (session('status') === 'verification-link-sent')
                                <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                    {{ __('A new verification link has been sent to your email address.') }}
                                </flux:text>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save Changes') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Profile updated successfully.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
