<?php

declare(strict_types=1);

namespace Prefabcortex\UpsTimeInTransit\Examples\Auth;

use InvalidArgumentException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Prefabcortex\UpsTimeInTransit\Client;
use Prefabcortex\UpsTimeInTransit\ClientConfig;
use Prefabcortex\UpsTimeInTransit\Server;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use RuntimeException;

use function array_key_exists;
use function base64_encode;
use function getenv;
use function http_build_query;
use function is_array;
use function is_string;
use function json_decode;
use function sprintf;
use function urlencode;

/**
 * Example: obtain an OAuth2 access token for the "OAuth2" scheme
 * (client_credentials grant, see RFC 6749) and use it to authenticate the
 * generated client.
 *
 * Pass the PSR-18 client your application uses. It fetches the token *and* becomes the client
 * the returned Client sends through, so both halves of the exchange go over the same
 * connection settings — timeouts, proxies and TLS options included.
 *
 * The client authenticates with an HTTP Basic header (client_secret_basic,
 * RFC 6749 §2.3.1), as the API description asks; the body carries the grant alone.
 *
 * The token expires after the number of seconds the token response names in expires_in, and
 * the returned Client keeps using it regardless. This example neither caches nor renews it:
 * call it again for a fresh Client once that time has passed.
 *
 * Usage: set OAUTH2_CLIENT_ID and OAUTH2_CLIENT_SECRET in the environment, then
 *
 *   $client = getOAuth2Client($httpClient);
 *
 * @throws RuntimeException
 * @throws InvalidArgumentException
 * @throws ClientExceptionInterface
 */
function getOAuth2Client(ClientInterface $httpClient): Client
{
    $clientId = getenv('OAUTH2_CLIENT_ID');
    $clientSecret = getenv('OAUTH2_CLIENT_SECRET');

    if (false === $clientId || false === $clientSecret) {
        throw new RuntimeException('Set OAUTH2_CLIENT_ID and OAUTH2_CLIENT_SECRET before running this example.');
    }

    $config = ClientConfig::forServer(Server::CustomerIntegrationEnvironment)->withHttpClient($httpClient);

    // The API description states the token endpoint in full, so the server of the
    // client has no say in it.
    $tokenUrl = 'https://wwwcie.ups.com/security/v1/oauth/token';

    $formFields = [
        'grant_type' => 'client_credentials',
    ];

    $factory = new Psr17Factory();

    $request = $factory
        ->createRequest('POST', $tokenUrl)
        ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
        ->withHeader('Authorization', 'Basic ' . base64_encode(urlencode($clientId) . ':' . urlencode($clientSecret)))
        ->withBody($factory->createStream(http_build_query($formFields)));

    $response = $httpClient->sendRequest($request);
    $status = $response->getStatusCode();
    $payload = json_decode((string) $response->getBody(), true);

    // A refused token request names its reason in `error` and may explain it in
    // `error_description` (RFC 6749 §5.2).
    if ($status < 200 || $status >= 300) {
        $reason = 'no error code';
        if (is_array($payload) && array_key_exists('error', $payload) && is_string($payload['error'])) {
            $reason = $payload['error'];
        }
        if (is_array($payload) && array_key_exists('error_description', $payload) && is_string($payload['error_description'])) {
            $reason .= ' — ' . $payload['error_description'];
        }

        throw new RuntimeException(sprintf('Token endpoint refused the request (HTTP %d): %s', $status, $reason));
    }

    if (!is_array($payload) || !array_key_exists('access_token', $payload) || !is_string($payload['access_token'])) {
        throw new RuntimeException(sprintf('Token endpoint did not return an access_token (HTTP %d).', $status));
    }

    return Client::withOAuth($payload['access_token'], $config);
}
