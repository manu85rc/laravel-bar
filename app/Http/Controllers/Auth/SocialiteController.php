<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Concerns\RedirectsAuthenticatedUsers;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as OAuthUser;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;
use Throwable;

class SocialiteController extends Controller
{
    use RedirectsAuthenticatedUsers;

    private const SCOPES = [
        SocialAccount::PROVIDER_GOOGLE => ['openid', 'profile', 'email'],
        SocialAccount::PROVIDER_FACEBOOK => ['email', 'public_profile'],
    ];

    public function redirect(Request $request, string $provider): SymfonyRedirect|RedirectResponse
    {
        if (! $this->providerIsEnabled($provider)) {
            return back()->withErrors(['email' => $this->unavailableMessage($provider)]);
        }

        if (Auth::check()) {
            $request->session()->put('social_linking', true);
        } elseif ($request->filled('redirect_from')) {
            $request->session()->put('social_redirect_from', $request->input('redirect_from'));
        }

        return $this->driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider)
    {
        if (! $this->providerIsEnabled($provider)) {
            return back()->withErrors(['email' => $this->unavailableMessage($provider)]);
        }

        try {
            $oauthUser = $this->driver($provider)->user();
        } catch (InvalidStateException) {
            return back()->withErrors(['email' => 'La sesión de inicio de sesión expiró. Inténtalo de nuevo.']);
        } catch (Throwable $e) {
            Log::warning('Social login failed', [
                'provider' => $provider,
                'message' => $e->getMessage(),
            ]);

            return back()->withErrors(['email' => 'No pudimos completar el inicio de sesión con '.ucfirst($provider).'.']);
        }

        $linking = $request->session()->pull('social_linking', false);

        if ($linking && Auth::check()) {
            return $this->link($request, $provider, $oauthUser);
        }

        return $this->login($request, $provider, $oauthUser);
    }

    private function link(Request $request, string $provider, OAuthUser $oauthUser)
    {
        $user = $request->user();
        $account = SocialAccount::where('provider', $provider)
            ->where('provider_id', (string) $oauthUser->getId())
            ->first();

        if ($account && $account->user_id !== $user->id) {
            return redirect()->route('profile.edit')
                ->withErrors(['email' => 'Esa cuenta de '.ucfirst($provider).' ya está vinculada a otro usuario.']);
        }

        if ($user->linkedProvider($provider)) {
            return redirect()->route('profile.edit')
                ->with('success', 'Ya tenías '.ucfirst($provider).' vinculado.');
        }

        if ($account) {
            $this->syncTokens($account, $oauthUser);
        } else {
            $this->attach($user, $provider, (string) $oauthUser->getId(), $oauthUser);
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Cuenta de '.ucfirst($provider).' vinculada.');
    }

    private function login(Request $request, string $provider, OAuthUser $oauthUser)
    {
        $user = $this->resolveUser($provider, $oauthUser);

        if ($user === null) {
            return back()->withErrors(['email' => 'Ya existe una cuenta con ese correo. Ingresa con tu contraseña y vincula tu cuenta desde tu perfil.']);
        }

        Auth::login($user, true);

        $request->session()->regenerate();

        return $this->redirectAfterLogin($request, $user);
    }

    public function unlink(Request $request, SocialAccount $socialAccount)
    {
        $user = $request->user();

        abort_if($socialAccount->user_id !== $user->id, 403);

        if (! $user->canUnlinkSocialAccount()) {
            return back()->withErrors(['email' => 'Configura una contraseña antes de desvincular tu última cuenta social.']);
        }

        $socialAccount->delete();

        return redirect()->route('profile.edit')->with('success', 'Cuenta '.$socialAccount->label().' desvinculada.');
    }

    private function resolveUser(string $provider, OAuthUser $oauthUser): ?User
    {
        $providerId = (string) $oauthUser->getId();

        $existing = SocialAccount::where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        if ($existing) {
            $this->syncTokens($existing, $oauthUser);

            return $existing->user()->first();
        }

        $email = $oauthUser->getEmail();

        $user = $email ? User::where('email', $email)->first() : null;

        if ($user) {
            if (! $user->isUser()) {
                return null;
            }

            $this->attach($user, $provider, $providerId, $oauthUser);

            return $user;
        }

        $user = $this->createUser($provider, $email, $oauthUser);

        $this->attach($user, $provider, $providerId, $oauthUser);

        return $user;
    }

    private function createUser(string $provider, ?string $email, OAuthUser $oauthUser): User
    {
        $userRole = Role::where('slug', User::ROLE_USER)->first();

        if (! $userRole) {
            throw new RuntimeException('No existe el rol "user". Ejecuta php artisan db:seed.');
        }

        $email = $email ?: sprintf('%s_%s@social.local', $provider, Str::lower(Str::random(12)));

        $user = User::create([
            'name' => $oauthUser->getName() ?: Str::before($email, '@'),
            'email' => $email,
            'password' => null,
            'role_id' => $userRole->id,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    private function attach(User $user, string $provider, string $providerId, OAuthUser $oauthUser): void
    {
        $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => $providerId,
            'nickname' => $oauthUser->getNickname(),
            'avatar' => $oauthUser->getAvatar(),
            'token' => $oauthUser->token,
            'refresh_token' => $oauthUser->refreshToken,
            'expires_at' => $oauthUser->expiresIn ? now()->addSeconds($oauthUser->expiresIn) : null,
        ]);
    }

    private function syncTokens(SocialAccount $account, OAuthUser $oauthUser): void
    {
        $account->forceFill([
            'nickname' => $oauthUser->getNickname() ?: $account->nickname,
            'avatar' => $oauthUser->getAvatar() ?: $account->avatar,
            'token' => $oauthUser->token ?: $account->token,
            'refresh_token' => $oauthUser->refreshToken ?: $account->refresh_token,
            'expires_at' => $oauthUser->expiresIn ? now()->addSeconds($oauthUser->expiresIn) : $account->expires_at,
        ])->save();
    }

    private function driver(string $provider)
    {
        $driver = Socialite::driver($provider);

        $redirect = config("services.{$provider}.redirect");

        return $driver
            ->redirectUrl($redirect ?: route('social.callback', ['provider' => $provider]))
            ->scopes(self::SCOPES[$provider] ?? []);
    }

    private function providerIsEnabled(string $provider): bool
    {
        return SocialAccount::isSupportedProvider($provider)
            && filled(config("services.{$provider}.client_id"))
            && filled(config("services.{$provider}.client_secret"));
    }

    private function unavailableMessage(string $provider): string
    {
        return 'El acceso con '.ucfirst($provider).' aún no está configurado.';
    }
}
