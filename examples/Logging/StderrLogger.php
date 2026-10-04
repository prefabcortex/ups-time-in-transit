<?php

declare(strict_types=1);

namespace Prefabcortex\UpsTimeInTransit\Examples\Logging;

use JsonException;
use Override;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Stringable;

use function fwrite;
use function is_scalar;
use function json_encode;
use function sprintf;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const STDERR;

/**
 * Minimal PSR-3 logger that writes to STDERR. Swap this for Monolog (or any
 * other psr/log implementation) in real applications — DebugLoggingHttpClient
 * only depends on Psr\Log\LoggerInterface.
 */
final class StderrLogger implements LoggerInterface
{
    use LoggerTrait;

    /** @throws JsonException */
    #[Override]
    public function log(mixed $level, string|Stringable $message, array $context = []): void
    {
        $levelLabel = is_scalar($level) ? (string) $level : 'unknown';

        fwrite(STDERR, sprintf(
            "[%s] %s %s\n",
            $levelLabel,
            $message,
            json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
        ));
    }
}
