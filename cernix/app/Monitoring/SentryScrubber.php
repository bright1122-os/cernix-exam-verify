<?php

namespace App\Monitoring;

use Sentry\Event;
use Sentry\EventHint;

/**
 * Strips fields that must never leave the server before an event is sent to Sentry.
 *
 * Registered as the `before_send` callable in config/sentry.php.
 * This runs even when send_default_pii is false — belt-and-suspenders for
 * domain-specific secrets that Sentry's built-in scrubbing does not know about.
 */
class SentryScrubber
{
    /**
     * Keys whose values are always replaced with '[Filtered]'.
     * Applied recursively so nested structures (e.g. qr_data.*) are covered.
     */
    private const SENSITIVE_KEYS = [
        'encrypted_payload',
        'hmac_signature',
        'hmac_secret',
        'aes_key',
        'rrr_number',
        'token_id',
        'qr_data',
        'qr_svg',
        'password',
        'token',
        'api_key',
        'secret',
    ];

    public static function beforeSend(Event $event, ?EventHint $hint): ?Event
    {
        // Scrub POST / JSON body captured by Sentry's request integration
        $request = $event->getRequest();
        if (isset($request['data']) && is_array($request['data'])) {
            $request['data'] = self::scrub($request['data']);
            $event->setRequest($request);
        }

        // Scrub any developer-attached extra context
        $extra = $event->getExtra();
        if (!empty($extra)) {
            $event->setExtra(self::scrub($extra));
        }

        return $event;
    }

    private static function scrub(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true)) {
                $data[$key] = '[Filtered]';
            } elseif (is_array($value)) {
                $data[$key] = self::scrub($value);
            }
        }

        return $data;
    }
}
