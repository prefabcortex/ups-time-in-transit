<?php

declare(strict_types=1);

namespace Prefabcortex\UpsTimeInTransit\Tests\Fixture;

use Prefabcortex\UpsTimeInTransit\Model\CandidateAddress;
use Prefabcortex\UpsTimeInTransit\Model\EmsResponse;
use Prefabcortex\UpsTimeInTransit\Model\ErrorResponse;
use Prefabcortex\UpsTimeInTransit\Model\Errors;
use Prefabcortex\UpsTimeInTransit\Model\SelfNormalizingModel;
use Prefabcortex\UpsTimeInTransit\Model\Services;
use Prefabcortex\UpsTimeInTransit\Model\TimeInTransitRequest;
use Prefabcortex\UpsTimeInTransit\Model\TimeInTransitResponse;
use Prefabcortex\UpsTimeInTransit\Model\ValidationList;
use Prefabcortex\UpsTimeInTransit\Validator\CandidateAddressConstraint;
use Prefabcortex\UpsTimeInTransit\Validator\EmsResponseConstraint;
use Prefabcortex\UpsTimeInTransit\Validator\ErrorResponseConstraint;
use Prefabcortex\UpsTimeInTransit\Validator\ErrorsConstraint;
use Prefabcortex\UpsTimeInTransit\Validator\ServicesConstraint;
use Prefabcortex\UpsTimeInTransit\Validator\TimeInTransitRequestConstraint;
use Prefabcortex\UpsTimeInTransit\Validator\TimeInTransitResponseConstraint;
use Prefabcortex\UpsTimeInTransit\Validator\ValidationListConstraint;
use Symfony\Component\Validator\Constraint;

/**
 * The data providers over ModelFixtures: one schema-conformant instance of every model in this
 * package, and what each is checked against.
 *
 * Values are the ones the API description states — `example` or `default` where it gives one, a
 * typed placeholder where it does not. They are shaped like real data, not equal to it: nothing
 * here has been sent to the service, so a value being accepted by the schema says nothing about it
 * being accepted by the server.
 */
final class ModelFixtureProviders
{
    /**
     * Every model that could be built and reads back what it writes, keyed by class name so a
     * failure names the model.
     *
     * @return iterable<string, array{SelfNormalizingModel, callable(array<int|string, mixed>): SelfNormalizingModel}>
     */
    public static function roundTrips(): iterable
    {
        yield 'TimeInTransitRequest' => [
            ModelFixtures::buildTimeInTransitRequest(),
            TimeInTransitRequest::fromArray(...),
        ];
        yield 'TimeInTransitResponse' => [
            ModelFixtures::buildTimeInTransitResponse(),
            TimeInTransitResponse::fromArray(...),
        ];
        yield 'ErrorResponse' => [ModelFixtures::buildErrorResponse(), ErrorResponse::fromArray(...)];
        yield 'ValidationList' => [ModelFixtures::buildValidationList(), ValidationList::fromArray(...)];
        yield 'EmsResponse' => [ModelFixtures::buildEmsResponse(), EmsResponse::fromArray(...)];
        yield 'CandidateAddress' => [ModelFixtures::buildCandidateAddress(), CandidateAddress::fromArray(...)];
        yield 'Services' => [ModelFixtures::buildServices(), Services::fromArray(...)];
        yield 'Errors' => [ModelFixtures::buildErrors(), Errors::fromArray(...)];
    }

    /**
     * Each model with the wire names its document must carry.
     *
     * @return iterable<string, array{SelfNormalizingModel, callable(array<int|string, mixed>): SelfNormalizingModel, list<string>}>
     */
    public static function documentsMissingARequiredProperty(): iterable
    {
        yield 'TimeInTransitRequest' => [
            ModelFixtures::buildTimeInTransitRequest(),
            TimeInTransitRequest::fromArray(...),
            ['originCountryCode'],
        ];
        yield 'EmsResponse' => [
            ModelFixtures::buildEmsResponse(),
            EmsResponse::fromArray(...),
            [
                'shipDate',
                'shipTime',
                'billType',
                'residentialIndicator',
                'destinationCountryName',
                'destinationCountryCode',
                'originCountryName',
                'originCountryCode',
                'guaranteeSuspended',
                'numberOfServices',
            ],
        ];
        yield 'CandidateAddress' => [
            ModelFixtures::buildCandidateAddress(),
            CandidateAddress::fromArray(...),
            ['countryName'],
        ];
        yield 'Services' => [
            ModelFixtures::buildServices(),
            Services::fromArray(...),
            [
                'serviceLevel',
                'serviceLevelDescription',
                'shipDate',
                'deliveryDate',
                'commitTime',
                'deliveryTime',
                'deliveryDayOfWeek',
                'nextDayPickupIndicator',
                'saturdayPickupIndicator',
                'guaranteeIndicator',
                'totalTransitDays',
                'businessTransitDays',
                'restDaysCount',
                'holidayCount',
                'delayCount',
                'pickupDate',
                'pickupTime',
                'cstccutoffTime',
            ],
        ];
    }

    /**
     * Each model with, per wire name, a value of a type that property cannot hold.
     *
     * Only properties whose type is a single closed shape appear. A union may legitimately accept
     * what looks like the wrong type, and a schema stating no type accepts anything.
     *
     * @return iterable<string, array{SelfNormalizingModel, callable(array<int|string, mixed>): SelfNormalizingModel, array<array-key, int|string>}>
     */
    public static function documentsWithAMistypedProperty(): iterable
    {
        yield 'TimeInTransitRequest' => [
            ModelFixtures::buildTimeInTransitRequest(),
            TimeInTransitRequest::fromArray(...),
            ['originCountryCode' => 42],
        ];
        yield 'EmsResponse' => [
            ModelFixtures::buildEmsResponse(),
            EmsResponse::fromArray(...),
            [
                'shipDate' => 42,
                'shipTime' => 42,
                'billType' => 42,
                'residentialIndicator' => 42,
                'destinationCountryName' => 42,
                'destinationCountryCode' => 42,
                'originCountryName' => 42,
                'originCountryCode' => 42,
                'guaranteeSuspended' => 0,
                'numberOfServices' => 'not-a-number',
            ],
        ];
        yield 'CandidateAddress' => [
            ModelFixtures::buildCandidateAddress(),
            CandidateAddress::fromArray(...),
            ['countryName' => 42],
        ];
        yield 'Services' => [
            ModelFixtures::buildServices(),
            Services::fromArray(...),
            [
                'serviceLevel' => 42,
                'serviceLevelDescription' => 42,
                'shipDate' => 42,
                'deliveryDate' => 42,
                'commitTime' => 42,
                'deliveryTime' => 42,
                'deliveryDayOfWeek' => 42,
                'nextDayPickupIndicator' => 42,
                'saturdayPickupIndicator' => 42,
                'guaranteeIndicator' => 42,
                'totalTransitDays' => 'not-a-number',
                'businessTransitDays' => 'not-a-number',
                'restDaysCount' => 'not-a-number',
                'holidayCount' => 'not-a-number',
                'delayCount' => 'not-a-number',
                'pickupDate' => 42,
                'pickupTime' => 42,
                'cstccutoffTime' => 42,
            ],
        ];
    }

    /**
     * Each model whose values all pass their constraints, with those constraints.
     *
     * @return iterable<string, array{SelfNormalizingModel, list<Constraint>}>
     */
    public static function modelsWithTheirConstraints(): iterable
    {
        yield 'TimeInTransitRequest' => [
            ModelFixtures::buildTimeInTransitRequest(),
            TimeInTransitRequestConstraint::constraints(),
        ];
        yield 'TimeInTransitResponse' => [
            ModelFixtures::buildTimeInTransitResponse(),
            TimeInTransitResponseConstraint::constraints(),
        ];
        yield 'ErrorResponse' => [ModelFixtures::buildErrorResponse(), ErrorResponseConstraint::constraints()];
        yield 'ValidationList' => [ModelFixtures::buildValidationList(), ValidationListConstraint::constraints()];
        yield 'EmsResponse' => [ModelFixtures::buildEmsResponse(), EmsResponseConstraint::constraints()];
        yield 'CandidateAddress' => [ModelFixtures::buildCandidateAddress(), CandidateAddressConstraint::constraints()];
        yield 'Services' => [ModelFixtures::buildServices(), ServicesConstraint::constraints()];
        yield 'Errors' => [ModelFixtures::buildErrors(), ErrorsConstraint::constraints()];
    }
}
