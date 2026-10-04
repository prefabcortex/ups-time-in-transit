<?php

declare(strict_types=1);

namespace Prefabcortex\UpsTimeInTransit\Examples\Operations;

use Prefabcortex\UpsTimeInTransit\Client;
use Prefabcortex\UpsTimeInTransit\Exception\ApiException;
use Prefabcortex\UpsTimeInTransit\Exception\MalformedResponseException;
use Prefabcortex\UpsTimeInTransit\Exception\ResponseValidationException;
use Prefabcortex\UpsTimeInTransit\Exception\TimeInTransitBadRequestException;
use Prefabcortex\UpsTimeInTransit\Exception\TimeInTransitForbiddenException;
use Prefabcortex\UpsTimeInTransit\Exception\TimeInTransitTooManyRequestsException;
use Prefabcortex\UpsTimeInTransit\Exception\TimeInTransitUnauthorizedException;
use Prefabcortex\UpsTimeInTransit\Exception\TransportException;
use Prefabcortex\UpsTimeInTransit\Exception\UnexpectedContentTypeException;
use Prefabcortex\UpsTimeInTransit\Exception\UnexpectedStatusCodeException;
use Prefabcortex\UpsTimeInTransit\Exception\UnsupportedValueException;
use Prefabcortex\UpsTimeInTransit\Model\TimeInTransitRequest;
use Prefabcortex\UpsTimeInTransit\Model\TimeInTransitResponse;
use Prefabcortex\UpsTimeInTransit\Parameter\TimeInTransitHeaderParameters;

final class TimeInTransitExample
{
    /**
     * Usage: pass an already-authenticated Client (see examples/Auth/).
     *
     *   $client = Client::withOAuth($token, $config); // see examples/Auth/
     *   $headerParameters = new TimeInTransitHeaderParameters($transId, $transactionSrc);
     *   TimeInTransitExample::timeInTransit($client, $version, TimeInTransitExample::build1(), $headerParameters);
     *
     * @throws ApiException
     * @throws UnsupportedValueException
     * @throws TransportException
     * @throws ResponseValidationException
     * @throws MalformedResponseException
     * @throws TimeInTransitBadRequestException
     * @throws TimeInTransitUnauthorizedException
     * @throws TimeInTransitForbiddenException
     * @throws TimeInTransitTooManyRequestsException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedStatusCodeException
     */
    public static function timeInTransit(
        Client $client,
        string $version,
        TimeInTransitRequest $requestBody,
        TimeInTransitHeaderParameters $headerParameters,
    ): TimeInTransitResponse {
        return $client->timeInTransit(
            $version,
            $requestBody,
            $headerParameters,
        );
    }

    /**
     * A sample JSON request (Standard Example).
     */
    public static function build1(): TimeInTransitRequest
    {
        return TimeInTransitRequest::builder('DE')
            ->setOriginStateProvince('REPLACE_ME')
            ->setOriginCityName('REPLACE_ME')
            ->setOriginTownName('REPLACE_ME')
            ->setOriginPostalCode('10703')
            ->setDestinationCountryCode('US')
            ->setDestinationStateProvince('NH')
            ->setDestinationCityName('MANCHESTER')
            ->setDestinationTownName('REPLACE_ME')
            ->setDestinationPostalCode('03104')
            ->setResidentialIndicator('RE')
            ->setShipDate('2019-05-01')
            ->setShipTime('REPLACE_')
            ->setWeight(0.0)
            ->setWeightUnitOfMeasure('LBS')
            ->setShipmentContentsValue(0.0)
            ->setShipmentContentsCurrencyCode('USD')
            ->setBillType('03')
            ->setAvvFlag(true)
            ->setNumberOfPackages(0)
            ->build();
    }
}
