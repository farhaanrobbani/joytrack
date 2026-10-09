<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Translation\PotentiallyTranslatedString;

class Turnstile implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! config('turnstile.secret_key')) {
            return;
        }

        $message = __('Verifikasi keamanan gagal. Silakan coba lagi.');

        if (! is_string($value) || $value === '') {
            $fail($message);

            return;
        }

        try {
            $response = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => config('turnstile.secret_key'),
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);
        } catch (\Throwable) {
            $fail($message);

            return;
        }

        if ($response->failed() || ! $response->json('success')) {
            $fail($message);
        }
    }
}
