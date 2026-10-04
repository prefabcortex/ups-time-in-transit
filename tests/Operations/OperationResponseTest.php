<?php

declare(strict_types=1);

namespace Prefabcortex\UpsTimeInTransit\Tests\Operations;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Prefabcortex\UpsTimeInTransit\Client;
use Prefabcortex\UpsTimeInTransit\ClientConfig;
use Prefabcortex\UpsTimeInTransit\Exception\ApiException;
use Prefabcortex\UpsTimeInTransit\Exception\MalformedDataException;
use Prefabcortex\UpsTimeInTransit\Exception\TimeInTransitBadRequestException;
use Prefabcortex\UpsTimeInTransit\Exception\TimeInTransitForbiddenException;
use Prefabcortex\UpsTimeInTransit\Exception\TimeInTransitTooManyRequestsException;
use Prefabcortex\UpsTimeInTransit\Exception\TimeInTransitUnauthorizedException;
use Prefabcortex\UpsTimeInTransit\Exception\UnexpectedContentTypeException;
use Prefabcortex\UpsTimeInTransit\Exception\UnexpectedStatusCodeException;
use Prefabcortex\UpsTimeInTransit\Http\JsonBody;
use Prefabcortex\UpsTimeInTransit\Http\ValidationMode;
use Prefabcortex\UpsTimeInTransit\Parameter\TimeInTransitHeaderParameters;
use Prefabcortex\UpsTimeInTransit\Tests\Fixture\CannedResponse;
use Prefabcortex\UpsTimeInTransit\Tests\Fixture\ModelFixtures;
use Prefabcortex\UpsTimeInTransit\Tests\Fixture\RecordingHttpClient;
use RuntimeException;

use function sprintf;

/**
 * Every response an operation reads, answered once through the method that reads it.
 *
 * A recorded client answers with the status and content type of one branch and a body built from
 * the model fixtures; the test checks that the model comes back, or the declared exception with the
 * response still readable. A status no response declares and a declared status under the wrong
 * content type are answered too.
 *
 * What this cannot show: that the service sends these documents. They come from the same
 * description the client came from, so this proves the package reads what it promises.
 */
final class OperationResponseTest extends TestCase
{
    private const string BASE_URL = 'https://response-test.invalid';

    /**
     * @throws InvalidArgumentException
     * @throws MalformedDataException
     * @throws RuntimeException
     */
    public function testTimeInTransitReads200(): void
    {
        $body = JsonBody::encode(ModelFixtures::buildTimeInTransitResponse());
        $httpClient = new RecordingHttpClient(CannedResponse::answering(200, 'application/json', $body));
        $client = Client::create(
            ClientConfig::forBaseUrl(self::BASE_URL)->withHttpClient($httpClient)->withValidation(
                ValidationMode::Strict,
            ),
        );
        try {
            $result = $client->timeInTransit(
                'smoke-test',
                ModelFixtures::buildTimeInTransitRequest(),
                new TimeInTransitHeaderParameters('smoke-test', 'smoke-test'),
            );
        } catch (ApiException $error) {
            self::fail(
                sprintf('%s answered %d with %s: %s', 'timeInTransit', 200, $error::class, $error->getMessage()),
            );
        }
        self::assertEquals(ModelFixtures::buildTimeInTransitResponse(), $result);
    }

    /**
     * @throws InvalidArgumentException
     * @throws MalformedDataException
     * @throws RuntimeException
     */
    public function testTimeInTransitReads206(): void
    {
        $body = JsonBody::encode(ModelFixtures::buildTimeInTransitResponse());
        $httpClient = new RecordingHttpClient(CannedResponse::answering(206, 'application/json', $body));
        $client = Client::create(
            ClientConfig::forBaseUrl(self::BASE_URL)->withHttpClient($httpClient)->withValidation(
                ValidationMode::Strict,
            ),
        );
        try {
            $result = $client->timeInTransit(
                'smoke-test',
                ModelFixtures::buildTimeInTransitRequest(),
                new TimeInTransitHeaderParameters('smoke-test', 'smoke-test'),
            );
        } catch (ApiException $error) {
            self::fail(
                sprintf('%s answered %d with %s: %s', 'timeInTransit', 206, $error::class, $error->getMessage()),
            );
        }
        self::assertEquals(ModelFixtures::buildTimeInTransitResponse(), $result);
    }

    /**
     * @throws InvalidArgumentException
     * @throws MalformedDataException
     * @throws RuntimeException
     */
    public function testTimeInTransitReads400(): void
    {
        $body = JsonBody::encode(ModelFixtures::buildErrorResponse());
        $httpClient = new RecordingHttpClient(CannedResponse::answering(400, 'application/json', $body));
        $client = Client::create(
            ClientConfig::forBaseUrl(self::BASE_URL)->withHttpClient($httpClient)->withValidation(
                ValidationMode::Strict,
            ),
        );
        try {
            $client->timeInTransit(
                'smoke-test',
                ModelFixtures::buildTimeInTransitRequest(),
                new TimeInTransitHeaderParameters('smoke-test', 'smoke-test'),
            );
            self::fail(
                sprintf('%s did not throw TimeInTransitBadRequestException for its %d response', 'timeInTransit', 400),
            );
        } catch (TimeInTransitBadRequestException $exception) {
            self::assertSame(400, $exception->getResponse()->getStatusCode());
            self::assertEquals(ModelFixtures::buildErrorResponse(), $exception->getErrorResponse());
            self::assertSame($body, $exception->getRawResponse());
            self::assertSame($body, $exception->getResponse()->getBody()->getContents());
        } catch (ApiException $error) {
            self::fail(
                sprintf('%s answered %d with %s: %s', 'timeInTransit', 400, $error::class, $error->getMessage()),
            );
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws MalformedDataException
     * @throws RuntimeException
     */
    public function testTimeInTransitReads401(): void
    {
        $body = JsonBody::encode(ModelFixtures::buildErrorResponse());
        $httpClient = new RecordingHttpClient(CannedResponse::answering(401, 'application/json', $body));
        $client = Client::create(
            ClientConfig::forBaseUrl(self::BASE_URL)->withHttpClient($httpClient)->withValidation(
                ValidationMode::Strict,
            ),
        );
        try {
            $client->timeInTransit(
                'smoke-test',
                ModelFixtures::buildTimeInTransitRequest(),
                new TimeInTransitHeaderParameters('smoke-test', 'smoke-test'),
            );
            self::fail(
                sprintf(
                    '%s did not throw TimeInTransitUnauthorizedException for its %d response',
                    'timeInTransit',
                    401,
                ),
            );
        } catch (TimeInTransitUnauthorizedException $exception) {
            self::assertSame(401, $exception->getResponse()->getStatusCode());
            self::assertEquals(ModelFixtures::buildErrorResponse(), $exception->getErrorResponse());
            self::assertSame($body, $exception->getRawResponse());
            self::assertSame($body, $exception->getResponse()->getBody()->getContents());
        } catch (ApiException $error) {
            self::fail(
                sprintf('%s answered %d with %s: %s', 'timeInTransit', 401, $error::class, $error->getMessage()),
            );
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws MalformedDataException
     * @throws RuntimeException
     */
    public function testTimeInTransitReads403(): void
    {
        $body = JsonBody::encode(ModelFixtures::buildErrorResponse());
        $httpClient = new RecordingHttpClient(CannedResponse::answering(403, 'application/json', $body));
        $client = Client::create(
            ClientConfig::forBaseUrl(self::BASE_URL)->withHttpClient($httpClient)->withValidation(
                ValidationMode::Strict,
            ),
        );
        try {
            $client->timeInTransit(
                'smoke-test',
                ModelFixtures::buildTimeInTransitRequest(),
                new TimeInTransitHeaderParameters('smoke-test', 'smoke-test'),
            );
            self::fail(
                sprintf('%s did not throw TimeInTransitForbiddenException for its %d response', 'timeInTransit', 403),
            );
        } catch (TimeInTransitForbiddenException $exception) {
            self::assertSame(403, $exception->getResponse()->getStatusCode());
            self::assertEquals(ModelFixtures::buildErrorResponse(), $exception->getErrorResponse());
            self::assertSame($body, $exception->getRawResponse());
            self::assertSame($body, $exception->getResponse()->getBody()->getContents());
        } catch (ApiException $error) {
            self::fail(
                sprintf('%s answered %d with %s: %s', 'timeInTransit', 403, $error::class, $error->getMessage()),
            );
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws MalformedDataException
     * @throws RuntimeException
     */
    public function testTimeInTransitReads429(): void
    {
        $body = JsonBody::encode(ModelFixtures::buildErrorResponse());
        $httpClient = new RecordingHttpClient(CannedResponse::answering(429, 'application/json', $body));
        $client = Client::create(
            ClientConfig::forBaseUrl(self::BASE_URL)->withHttpClient($httpClient)->withValidation(
                ValidationMode::Strict,
            ),
        );
        try {
            $client->timeInTransit(
                'smoke-test',
                ModelFixtures::buildTimeInTransitRequest(),
                new TimeInTransitHeaderParameters('smoke-test', 'smoke-test'),
            );
            self::fail(
                sprintf(
                    '%s did not throw TimeInTransitTooManyRequestsException for its %d response',
                    'timeInTransit',
                    429,
                ),
            );
        } catch (TimeInTransitTooManyRequestsException $exception) {
            self::assertSame(429, $exception->getResponse()->getStatusCode());
            self::assertEquals(ModelFixtures::buildErrorResponse(), $exception->getErrorResponse());
            self::assertSame($body, $exception->getRawResponse());
            self::assertSame($body, $exception->getResponse()->getBody()->getContents());
        } catch (ApiException $error) {
            self::fail(
                sprintf('%s answered %d with %s: %s', 'timeInTransit', 429, $error::class, $error->getMessage()),
            );
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws MalformedDataException
     * @throws RuntimeException
     */
    public function testTimeInTransitRejectsAnUndeclaredStatus(): void
    {
        $httpClient = new RecordingHttpClient(
            CannedResponse::answering(
                599,
                'text/html',
                '<html><body>Served by something in front of the API</body></html>',
            ),
        );
        $client = Client::create(
            ClientConfig::forBaseUrl(self::BASE_URL)->withHttpClient($httpClient)->withValidation(
                ValidationMode::Strict,
            ),
        );
        try {
            $client->timeInTransit(
                'smoke-test',
                ModelFixtures::buildTimeInTransitRequest(),
                new TimeInTransitHeaderParameters('smoke-test', 'smoke-test'),
            );
            self::fail(
                sprintf(
                    '%s did not throw UnexpectedStatusCodeException for a %d response it cannot read',
                    'timeInTransit',
                    599,
                ),
            );
        } catch (UnexpectedStatusCodeException $exception) {
            self::assertSame(
                '<html><body>Served by something in front of the API</body></html>',
                $exception->getResponse()->getBody()->getContents(),
            );
        } catch (ApiException $error) {
            self::fail(
                sprintf('%s answered %d with %s: %s', 'timeInTransit', 599, $error::class, $error->getMessage()),
            );
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws MalformedDataException
     * @throws RuntimeException
     */
    public function testTimeInTransitRejectsAnUndeclaredContentType(): void
    {
        $httpClient = new RecordingHttpClient(
            CannedResponse::answering(
                200,
                'text/html',
                '<html><body>Served by something in front of the API</body></html>',
            ),
        );
        $client = Client::create(
            ClientConfig::forBaseUrl(self::BASE_URL)->withHttpClient($httpClient)->withValidation(
                ValidationMode::Strict,
            ),
        );
        try {
            $client->timeInTransit(
                'smoke-test',
                ModelFixtures::buildTimeInTransitRequest(),
                new TimeInTransitHeaderParameters('smoke-test', 'smoke-test'),
            );
            self::fail(
                sprintf(
                    '%s did not throw UnexpectedContentTypeException for a %d response it cannot read',
                    'timeInTransit',
                    200,
                ),
            );
        } catch (UnexpectedContentTypeException $exception) {
            self::assertSame(
                '<html><body>Served by something in front of the API</body></html>',
                $exception->getResponse()->getBody()->getContents(),
            );
        } catch (ApiException $error) {
            self::fail(
                sprintf('%s answered %d with %s: %s', 'timeInTransit', 200, $error::class, $error->getMessage()),
            );
        }
    }
}
