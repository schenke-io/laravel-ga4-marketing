<?php

namespace SchenkeIo\LaravelGa4Marketing\Services;

/**
 * Service for validating and normalizing GA4 event names.
 *
 * This class ensures that event names comply with Google Analytics 4
 * naming conventions, including snake_case conversion, reserved
 * prefix removal, and character restrictions.
 */
class EventValidator
{
    /**
     * Validate and normalize event name according to GA4 standards.
     */
    public function validateName(string $name): string
    {
        // 1. convert to snake_case if it's CamelCase
        $name = (string) preg_replace('/(?<!^)[A-Z](?=[a-z])|(?<=[a-z0-9])[A-Z]/', '_$0', $name);
        $name = strtolower($name);

        // 2. Remove reserved prefixes case-insensitively: ga_, google_, firebase_
        $name = (string) preg_replace('/^(ga_|google_|firebase_)/i', '', $name);

        // 3. alphanumeric and underscores only
        $name = (string) preg_replace('/[^a-z0-9_]/', '', $name);

        // 4. remove double underscores
        $name = (string) preg_replace('/__+/', '_', $name);

        // 5. trim underscores from start/end
        $name = trim($name, '_');

        // 6. Must start with an alphabetic character
        if ($name !== '' && ! ctype_alpha($name[0])) {
            $name = 'e_'.$name;
        }

        return substr($name, 0, 40);
    }
}
