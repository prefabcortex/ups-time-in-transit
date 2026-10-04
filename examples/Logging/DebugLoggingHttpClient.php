<?php

declare(strict_types=1);

namespace Prefabcortex\UpsTimeInTransit\Examples\Logging;

use InvalidArgumentException;
use JsonException;
use Override;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

use function array_key_exists;
use function explode;
use function implode;
use function in_array;
use function is_array;
use function json_decode;
use function json_encode;
use function str_starts_with;
use function strtolower;
use function urldecode;

use const JSON_THROW_ON_ERROR;

/**
 * Decorates a PSR-18 HTTP client and logs the full request and response
 * (method, URI, headers, body) through a PSR-3 logger on every call.
 *
 * Credentials are replaced with `***` on the way to the logger, so the output
 * can go into a bug report as it stands. Hidden are the headers and the query
 * parameters named in the constants below — `Authorization`, `Cookie` and
 * friends, plus whichever header or query parameter this API description
 * declares as its API key — and the token fields of a JSON response body. That
 * makes the log deliberately incomplete; leaving the lists intact is the point.
 *
 * Nothing else is filtered. A request body is the payload you are debugging and
 * is logged verbatim, except when it is form-encoded, in which case it passes
 * through the same parameter filter as the query string. If your API carries a
 * secret in a body of another shape, add it to the redaction yourself.
 *
 * Assumes seekable request/response body streams, which holds for the streams
 * this package creates and for most common PSR-18 client implementations. If
 * your client returns a non-seekable response body, wrap it in a buffering
 * stream before passing it to this decorator.
 */
final readonly class DebugLoggingHttpClient implements ClientInterface
{
    private const string REDACTED = '***';

    /** Header names whose value is never logged, compared lowercased. */
    private const array REDACTED_HEADERS = ['authorization', 'cookie', 'proxy-authorization', 'set-cookie'];

    /** Query parameter and form field names whose value is never logged, compared lowercased. */
    private const array REDACTED_PARAMETERS = ['access_token', 'client_secret', 'password', 'refresh_token', 'token'];

    /**
     * Top-level keys of a JSON response body whose value is never logged. A token endpoint
     * answers with `{"access_token": "…"}`, and that answer passes through this decorator
     * like any other.
     */
    private const array REDACTED_RESPONSE_FIELDS = ['access_token', 'client_secret', 'id_token', 'refresh_token'];

    public function __construct(
        private ClientInterface $httpClient,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    #[Override]
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->logger->debug('HTTP request', $this->requestContext($request));

        $response = $this->httpClient->sendRequest($request);

        $this->logger->debug('HTTP response', $this->responseContext($response));

        return $response;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException the redacted query is put back on the URI, and PSR-7 lets
     *                                  `withQuery()` reject what it is given
     * @throws RuntimeException
     */
    private function requestContext(RequestInterface $request): array
    {
        $body = (string) $request->getBody();
        if ($request->getBody()->isSeekable()) {
            $request->getBody()->rewind();
        }

        $uri = $request->getUri();

        return [
            'method' => $request->getMethod(),
            'uri' => (string) $uri->withQuery($this->redactParameters($uri->getQuery())),
            'headers' => $this->flattenHeaders($request),
            'body' => $this->hasContentType($request, 'application/x-www-form-urlencoded')
                ? $this->redactParameters($body)
                : $body,
        ];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws RuntimeException
     */
    private function responseContext(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        if ($response->getBody()->isSeekable()) {
            $response->getBody()->rewind();
        }

        return [
            'status_code' => $response->getStatusCode(),
            'reason_phrase' => $response->getReasonPhrase(),
            'headers' => $this->flattenHeaders($response),
            'body' => $this->redactJsonFields($response, $body),
        ];
    }

    /** @return array<string, string> */
    private function flattenHeaders(MessageInterface $message): array
    {
        $headers = [];

        foreach ($message->getHeaders() as $rawName => $values) {
            $name = (string) $rawName;
            $headers[$name] = in_array(strtolower($name), self::REDACTED_HEADERS, true)
                ? self::REDACTED
                : implode(', ', $values);
        }

        return $headers;
    }

    /**
     * Replaces the value of every credential-bearing field in a `key=value&…` string. A
     * form-encoded body has exactly that shape, so the URI query and the body are served
     * by one implementation rather than two.
     *
     * Pair by pair rather than through `parse_str()`/`http_build_query()`: that round trip
     * re-encodes every value it did not touch, and would write the mask itself as `%2A%2A%2A`
     * — unreadable in the one place whose whole purpose is being read.
     */
    private function redactParameters(string $query): string
    {
        if ('' === $query) {
            return $query;
        }

        $pairs = [];
        foreach (explode('&', $query) as $pair) {
            $name = explode('=', $pair, 2)[0];
            $pairs[] = in_array(strtolower(urldecode($name)), self::REDACTED_PARAMETERS, true)
                ? $name . '=' . self::REDACTED
                : $pair;
        }

        return implode('&', $pairs);
    }

    /**
     * Only the top level of the body is rewritten, and only on the field names above — an
     * API's own payload keeps its shape, and a body that is not JSON, not decodable or
     * carries none of those fields is handed back untouched rather than re-encoded.
     */
    private function redactJsonFields(MessageInterface $message, string $body): string
    {
        if (!$this->hasContentType($message, 'application/json')) {
            return $body;
        }

        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $body;
        }

        if (!is_array($decoded)) {
            return $body;
        }

        $found = false;
        foreach (self::REDACTED_RESPONSE_FIELDS as $field) {
            if (array_key_exists($field, $decoded)) {
                $decoded[$field] = self::REDACTED;
                $found = true;
            }
        }

        if (!$found) {
            return $body;
        }

        try {
            return json_encode($decoded, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $body;
        }
    }

    private function hasContentType(MessageInterface $message, string $mediaType): bool
    {
        return str_starts_with(strtolower($message->getHeaderLine('Content-Type')), $mediaType);
    }
}
