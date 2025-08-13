<?php

namespace App\Livewire\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Show extends Component
{
    public User $user;

    public function mount($userId = null)
    {
        if ($userId) {
            $this->user = User::findOrFail($userId);
        } else {
            $this->user = auth()->user();
        }
    }

    /**
     * Get the profile picture URL.
     */
    public function getProfilePictureUrl(): string
    {
        if ($this->user->profile_picture) {
            return Storage::disk('public')->url($this->user->profile_picture);
        }
        
        return asset('images/default-profile.svg');
    }

    /**
     * Get the background picture URL.
     */
    public function getBackgroundPictureUrl(): string
    {
        if ($this->user->background_picture) {
            return Storage::disk('public')->url($this->user->background_picture);
        }
        
        return asset('images/default-background.svg');
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.profile.show');
    }
}
