<?php

declare(strict_types=1);

namespace Prefabcortex\UpsTimeInTransit\Tests\Fixture;

use Prefabcortex\UpsTimeInTransit\Model\CandidateAddress;
use Prefabcortex\UpsTimeInTransit\Model\EmsResponse;
use Prefabcortex\UpsTimeInTransit\Model\ErrorResponse;
use Prefabcortex\UpsTimeInTransit\Model\Errors;
use Prefabcortex\UpsTimeInTransit\Model\Services;
use Prefabcortex\UpsTimeInTransit\Model\TimeInTransitRequest;
use Prefabcortex\UpsTimeInTransit\Model\TimeInTransitResponse;
use Prefabcortex\UpsTimeInTransit\Model\ValidationList;

final class ModelFixtures
{
    public static function buildTimeInTransitRequest(): TimeInTransitRequest
    {
        return TimeInTransitRequest::builder('RE')->build();
    }

    public static function buildTimeInTransitResponse(): TimeInTransitResponse
    {
        return TimeInTransitResponse::builder()->build();
    }

    public static function buildErrorResponse(): ErrorResponse
    {
        return ErrorResponse::builder()->build();
    }

    public static function buildValidationList(): ValidationList
    {
        return ValidationList::builder()->build();
    }

    public static function buildEmsResponse(): EmsResponse
    {
        return EmsResponse::builder(
            // shipDate
            'REPLACE_ME',
            // shipTime
            'REPLACE_ME',
            // billType
            'RE',
            // residentialIndicator
            'RE',
            // destinationCountryName
            'REPLACE_ME',
            // destinationCountryCode
            'RE',
            // originCountryName
            'REPLACE_ME',
            // originCountryCode
            'RE',
            // guaranteeSuspended
            true,
            // numberOfServices
            0,
        )->build();
    }

    public static function buildCandidateAddress(): CandidateAddress
    {
        return CandidateAddress::builder('REPLACE_ME')->build();
    }

    public static function buildServices(): Services
    {
        return Services::builder(
            // serviceLevel
            'RE',
            // serviceLevelDescription
            'REPLACE_ME',
            // shipDate
            'REPLACE_ME',
            // deliveryDate
            'REPLACE_ME',
            // commitTime
            'REPLACE_',
            // deliveryTime
            'REPLACE_',
            // deliveryDayOfWeek
            'REP',
            // nextDayPickupIndicator
            'R',
            // saturdayPickupIndicator
            'R',
            // guaranteeIndicator
            'R',
            // totalTransitDays
            0,
            // businessTransitDays
            0,
            // restDaysCount
            0,
            // holidayCount
            0,
            // delayCount
            0,
            // pickupDate
            'REPLACE_ME',
            // pickupTime
            'REPLACE_',
            // cstccutoffTime
            'REPLACE_',
        )->build();
    }

    public static function buildErrors(): Errors
    {
        return Errors::builder()->build();
    }
}
