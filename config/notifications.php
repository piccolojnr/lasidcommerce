<?php

$internalRecipients = array_values(array_filter(array_map(
    static fn (string $email): string => trim($email),
    explode(',', (string) env('INTERNAL_NOTIFICATION_EMAILS', ''))
)));

return [
    'internal' => [
        'recipients' => $internalRecipients,
    ],
];
