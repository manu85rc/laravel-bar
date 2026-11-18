<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RedirectsAuthenticatedUsers
{
    protected function redirectAfterLogin(Request $request, User $user): RedirectResponse
    {
        $redirectFrom = $request->input('redirect_from') ?: $request->session()->pull('social_redirect_from');

        if ($user->isUser()) {
            if ($redirectFrom === 'reservar') {
                return redirect()->route('home')->with('open_reservar', true);
            }

            return redirect()->route('home');
        }

        return redirect()->intended(route($user->panelHomeRoute() ?? 'home'));
    }
}
