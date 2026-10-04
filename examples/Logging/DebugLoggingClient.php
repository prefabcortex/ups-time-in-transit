<?php

declare(strict_types=1);

namespace Prefabcortex\UpsTimeInTransit\Examples\Logging;

// No autoloader reaches examples/, so the two classes below are pulled in by hand. Both paths are
// siblings of this file and therefore hold wherever the package is installed.
require_once __DIR__ . '/DebugLoggingHttpClient.php';
require_once __DIR__ . '/StderrLogger.php';

use Psr\Http\Client\ClientInterface;

use function getenv;

/**
 * Example: enable full request/response debug logging for the generated client.
 *
 * Pass the PSR-18 client your application already uses — the same one the package would
 * otherwise pick up on its own. Wrapping it here rather than looking one up keeps the choice
 * where it belongs, with you, and keeps this example free of the package's internals.
 *
 * Combine this with any Client construction:
 * See examples/Auth/ for a credentialed one, or Client::create() to sign requests yourself.
 *
 *   $config = ClientConfig::forServer(Server::CustomerIntegrationEnvironment)->withHttpClient(getDebugLoggingHttpClient($httpClient));
 *   $client = Client::create($config); // or Client::withOAuth($token, $config), see examples/Auth/
 *
 * Swap `new StderrLogger()` below for Monolog or any other psr/log
 * implementation to send these logs somewhere other than STDERR.
 *
 * Run with:
 *   DEBUG=1 php your-script.php
 */
function getDebugLoggingHttpClient(ClientInterface $httpClient): ClientInterface
{
    return false !== getenv('DEBUG') ? new DebugLoggingHttpClient($httpClient, new StderrLogger()) : $httpClient;
}
