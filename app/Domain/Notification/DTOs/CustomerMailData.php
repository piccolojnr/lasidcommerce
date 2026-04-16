<?php

namespace App\Domain\Notification\DTOs;

class CustomerMailData
{
    /**
     * @param  array<string, string>  $facts
     * @param  list<string>  $lines
     * @param  list<string>  $footerLines
     */
    public function __construct(
        public readonly string $subject,
        public readonly string $preheader,
        public readonly string $eyebrow,
        public readonly string $title,
        public readonly string $intro,
        public readonly array $lines = [],
        public readonly ?string $actionText = null,
        public readonly ?string $actionUrl = null,
        public readonly array $facts = [],
        public readonly array $footerLines = [],
    ) {}
}
