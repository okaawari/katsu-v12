<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $profilePicture;
    public $backgroundPicture;
    public $tempProfilePicture;
    public $tempBackgroundPicture;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->tempProfilePicture = $user->profile_picture;
        $this->tempBackgroundPicture = $user->background_picture;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'profilePicture' => ['nullable', 'image', 'max:2048', 'dimensions:ratio=1/1'],
            'backgroundPicture' => ['nullable', 'image', 'max:5120', 'dimensions:ratio=16/9'],
        ]);

        // Handle profile picture upload
        if ($this->profilePicture) {
            $profilePath = $this->profilePicture->store('profile-pictures', 'public');
            $user->profile_picture = $profilePath;
            $this->tempProfilePicture = $profilePath;
        }

        // Handle background picture upload
        if ($this->backgroundPicture) {
            $backgroundPath = $this->backgroundPicture->store('background-pictures', 'public');
            $user->background_picture = $backgroundPath;
            $this->tempBackgroundPicture = $backgroundPath;
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->profilePicture = null;
        $this->backgroundPicture = null;

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Handle profile picture upload with temporary preview.
     */
    public function updatedProfilePicture(): void
    {
        $this->validate([
            'profilePicture' => ['image', 'max:2048', 'dimensions:ratio=1/1'],
        ]);
    }

    /**
     * Handle background picture upload with temporary preview.
     */
    public function updatedBackgroundPicture(): void
    {
        $this->validate([
            'backgroundPicture' => ['image', 'max:5120', 'dimensions:ratio=16/9'],
        ]);
    }

    /**
     * Get the profile picture URL.
     */
    public function getProfilePictureUrl(): string
    {
        if ($this->profilePicture) {
            return $this->profilePicture->temporaryUrl();
        }
        
        if ($this->tempProfilePicture) {
            return Storage::disk('public')->url($this->tempProfilePicture);
        }
        
        return asset('images/default-profile.svg');
    }

    /**
     * Get the background picture URL.
     */
    public function getBackgroundPictureUrl(): string
    {
        if ($this->backgroundPicture) {
            return $this->backgroundPicture->temporaryUrl();
        }
        
        if ($this->tempBackgroundPicture) {
            return Storage::disk('public')->url($this->tempBackgroundPicture);
        }
        
        return asset('images/default-background.svg');
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard.index', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}
