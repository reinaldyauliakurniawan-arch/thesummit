<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Onboarding extends Component
{
    public bool $show = false;

    public function mount(): void
    {
        $user = Auth::user();

        // Only show onboarding for authenticated users who haven't seen it
        if ($user && !$user->has_seen_onboarding) {
            $this->show = true;
        }
    }

    /**
     * Mark onboarding as seen and hide the modal.
     */
    public function dismiss(): void
    {
        $user = Auth::user();
        if ($user) {
            $user->has_seen_onboarding = true;
            $user->save();
        }
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.onboarding');
    }
}