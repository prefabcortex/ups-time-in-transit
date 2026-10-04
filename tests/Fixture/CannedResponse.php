<?php

declare(strict_types=1);

namespace Prefabcortex\UpsTimeInTransit\Tests\Fixture;

use InvalidArgumentException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;

/**
 * The answer {@see RecordingHttpClient} hands back.
 *
 * Deliberately not tailored to any one operation. The smoke test drives the `…Raw()` methods, which
 * return the response untouched, so nothing here has to match a status/content-type row from the
 * description — and pretending it did would be the fabricated half of a mock that proves nothing.
 * What is being watched is the request that went out, not the answer that came back.
 *
 * {@see self::answering()} is the other half: the response test hands an operation the status and
 * content type one of its branches reads, with a body built from the model fixtures.
 */
final class CannedResponse
{
    /**
     * @throws InvalidArgumentException never, for these constant arguments — 200 is a valid status
     *                                  and the header name is well-formed. The PSR-17 factory says it
     *                                  can, though, and this package declares what it does not catch.
     */
    public static function empty(): ResponseInterface
    {
        $factory = new Psr17Factory();

        return $factory->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($factory->createStream('{}'));
    }

    /**
     * @throws InvalidArgumentException when the status is outside what HTTP allows or the header is
     *                                  malformed — never for the values the generated tests pass
     */
    public static function answering(int $status, string $contentType, string $body): ResponseInterface
    {
        $factory = new Psr17Factory();

        return $factory->createResponse($status)
            ->withHeader('Content-Type', $contentType)
            ->withBody($factory->createStream($body));
    }
}
