<?php

declare(strict_types=1);

namespace Prefabcortex\UpsTimeInTransit\Tests\Fixture;

use Override;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * A PSR-18 client that never sends anything and remembers what it was asked to send.
 *
 * The whole reason a generated package can test its own request building without credentials and
 * without a network: PSR-18 is a single method, so standing in for it costs one class. Everything
 * up to the wire — path parameters substituted, query string encoded, body serialised,
 * authentication headers applied — runs exactly as it would against the real service. What comes
 * back is whatever the canned response says.
 *
 * Requests are kept as a list rather than as "the last one", because an operation issuing a second
 * request nobody asked for is a defect worth seeing rather than a detail worth overwriting.
 */
final class RecordingHttpClient implements ClientInterface
{
    /** @var list<RequestInterface> */
    private array $requests = [];

    public function __construct(private readonly ResponseInterface $response)
    {
    }

    #[Override]
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;

        return $this->response;
    }

    /** @return list<RequestInterface> every request handed over, in order */
    public function getRequests(): array
    {
        return $this->requests;
    }
}
