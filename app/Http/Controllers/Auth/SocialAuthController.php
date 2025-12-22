<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class SocialAuthController extends Controller
{
    /**
     * @return RedirectResponse|SymfonyRedirectResponse
     */
    public function redirect(string $provider)
    {
        $provider = $this->normalizeProvider($provider);

        if (! $provider) {
            abort(404);
        }

        if (! class_exists(\Laravel\Socialite\Facades\Socialite::class)) {
            return $this->missingProvider();
        }

        if (! $this->hasProviderConfig($provider)) {
            return $this->missingProvider();
        }

        return \Laravel\Socialite\Facades\Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $provider = $this->normalizeProvider($provider);

        if (! $provider) {
            abort(404);
        }

        if (! class_exists(\Laravel\Socialite\Facades\Socialite::class)) {
            return $this->missingProvider();
        }

        if (! $this->hasProviderConfig($provider)) {
            return $this->missingProvider();
        }

        $socialUser = \Laravel\Socialite\Facades\Socialite::driver($provider)->user();
        $email = $socialUser->getEmail();

        if (! $email) {
            return $this->missingProvider('Your social account did not provide an email.');
        }

        $name = trim((string) $socialUser->getName());
        $parts = preg_split('/\s+/', $name) ?: [];
        $first = array_shift($parts) ?: $email;
        $last = $parts ? implode(' ', $parts) : null;

        $customer = Customer::firstOrCreate(
            ['email' => $email],
            [
                'first_name' => $first,
                'last_name' => $last,
                'password' => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
            ]
        );

        if (! $customer->email_verified_at) {
            $customer->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::guard('customer')->login($customer, true);

        return redirect()->intended(route('account.index', absolute: false));
    }

    private function normalizeProvider(string $provider): ?string
    {
        $provider = strtolower($provider);
        $supported = ['google', 'facebook', 'apple'];

        return in_array($provider, $supported, true) ? $provider : null;
    }

    private function hasProviderConfig(string $provider): bool
    {
        $config = config("services.{$provider}");

        return is_array($config)
            && ! empty($config['client_id'])
            && ! empty($config['client_secret'])
            && ! empty($config['redirect']);
    }

    private function missingProvider(string $message = 'Social sign-in is not configured yet.'): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['social' => $message]);
    }
}
