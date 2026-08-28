<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AntiBotService
{
    /**
     * Common disposable and temporary email domains.
     */
    protected array $disposableDomains = [
        'mailinator.com',
        'guerrillamail.com',
        'guerrillamail.net',
        'guerrillamail.org',
        'guerrillamail.biz',
        'guerrillamail.de',
        'guerrillamailblock.com',
        'sharklasers.com',
        'grr.la',
        'pokemail.net',
        'spam4.me',
        'temp-mail.org',
        'tempmail.com',
        'tempmail.net',
        '10minutemail.com',
        '10minutemail.net',
        'yopmail.com',
        'yopmail.fr',
        'yopmail.net',
        'dispostable.com',
        'getairmail.com',
        'throwawaymail.com',
        'trashmail.com',
        'trashmail.net',
        'fakemailgenerator.com',
        'mohmal.com',
        'burnermail.io',
        'crazymailing.com',
        'emailondeck.com',
        'inboxkitten.com',
        'generator.email',
        'tempail.com',
        'mytemp.email',
        'nada.ltd',
        'getnada.com',
        'maildrop.cc',
        'dropmail.me',
        'minuteinbox.com',
    ];

    /**
     * Generate an encrypted time-gate token containing the current timestamp.
     */
    public function generateTimeToken(): string
    {
        return Crypt::encryptString((string) time());
    }

    /**
     * Validate that the time taken between form display and submission is reasonable.
     * Rejects automated bots that submit within milliseconds (< 3s) or stale tokens (> 2 hours).
     */
    public function validateTimeToken(?string $token, int $minSeconds = 3, int $maxSeconds = 7200): bool
    {
        if (empty($token)) {
            return false;
        }

        try {
            $renderedAt = (int) Crypt::decryptString($token);
            $elapsed = time() - $renderedAt;

            if ($elapsed < $minSeconds || $elapsed > $maxSeconds) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Validate that the honeypot field is empty.
     * Bots typically fill every input field they find in the HTML.
     */
    public function validateHoneypot(?string $value): bool
    {
        return trim((string) $value) === '';
    }

    /**
     * Validate that the email address is not from a known disposable provider and has valid MX/DNS.
     */
    public function validateEmailDomain(string $email): bool
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return false;
        }

        $domain = strtolower(trim($parts[1]));

        if (empty($domain)) {
            return false;
        }

        // Check against known disposable domain list
        if (in_array($domain, $this->disposableDomains, true)) {
            return false;
        }

        // Optional DNS MX verification if function is enabled in PHP
        if (function_exists('checkdnsrr')) {
            $hasMx = @checkdnsrr($domain, 'MX');
            $hasA = @checkdnsrr($domain, 'A');
            if (!$hasMx && !$hasA) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate Google reCAPTCHA v3 response token.
     *
     * @return array{valid: bool, score: float, reason: string}
     */
    public function validateRecaptcha(?string $token, ?string $ip = null, string $expectedAction = 'register'): array
    {
        $secretKey = config('services.recaptcha.secret_key');
        $minScore = (float) config('services.recaptcha.min_score', 0.5);

        // If reCAPTCHA keys are not configured, gracefully pass validation
        if (empty($secretKey)) {
            return [
                'valid' => true,
                'score' => 1.0,
                'reason' => 'recaptcha_disabled',
            ];
        }

        if (empty($token)) {
            return [
                'valid' => false,
                'score' => 0.0,
                'reason' => 'missing_recaptcha_token',
            ];
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secretKey,
                    'response' => $token,
                    'remoteip' => $ip,
                ]);

            if (!$response->successful()) {
                Log::warning('Google reCAPTCHA v3 siteverify HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'valid' => false,
                    'score' => 0.0,
                    'reason' => 'recaptcha_http_failure',
                ];
            }

            $data = $response->json();
            $success = $data['success'] ?? false;
            $score = (float) ($data['score'] ?? 0.0);
            $action = $data['action'] ?? null;

            if (!$success) {
                Log::warning('Google reCAPTCHA v3 verification returned success=false', [
                    'error_codes' => $data['error-codes'] ?? [],
                    'ip' => $ip,
                ]);

                return [
                    'valid' => false,
                    'score' => 0.0,
                    'reason' => 'recaptcha_failed',
                ];
            }

            // Verify action if provided
            if ($action && $expectedAction && $action !== $expectedAction) {
                Log::warning('Google reCAPTCHA v3 action mismatch', [
                    'expected' => $expectedAction,
                    'received' => $action,
                    'ip' => $ip,
                ]);

                return [
                    'valid' => false,
                    'score' => $score,
                    'reason' => 'action_mismatch',
                ];
            }

            // Check if score meets the threshold
            if ($score < $minScore) {
                Log::warning('Google reCAPTCHA v3 low bot score', [
                    'score' => $score,
                    'min_score' => $minScore,
                    'ip' => $ip,
                ]);

                return [
                    'valid' => false,
                    'score' => $score,
                    'reason' => 'low_score',
                ];
            }

            return [
                'valid' => true,
                'score' => $score,
                'reason' => 'passed',
            ];
        } catch (Exception $e) {
            Log::error('Google reCAPTCHA v3 exception during verification: ' . $e->getMessage());

            return [
                'valid' => false,
                'score' => 0.0,
                'reason' => 'recaptcha_exception',
            ];
        }
    }
}
