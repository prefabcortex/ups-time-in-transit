<?php

declare(strict_types=1);

namespace Prefabcortex\UpsTimeInTransit\Tests;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Prefabcortex\UpsTimeInTransit\Exception\MalformedDataException;
use Prefabcortex\UpsTimeInTransit\Model\SelfNormalizingModel;
use Prefabcortex\UpsTimeInTransit\Tests\Fixture\ModelFixtureProviders;
use RuntimeException;

/**
 * Documents that do not fit leave through a documented exception, never a TypeError.
 *
 * Two corruptions of a valid document, both derived from the API description: a required property
 * removed, and a property given a value of a type it cannot hold. Each has to raise
 * `MalformedDataException` — the class the operations already name in their `@throws`, so a caller
 * following the documented contract catches it.
 *
 * The alternative is what makes this worth asserting: an unchecked value reaching a typed
 * constructor raises `TypeError`, which is an `Error` rather than an `Exception` and which no
 * `@throws` here mentions. Both look identical until something runs.
 *
 * What this cannot show is that the service sends what its description promises. These documents
 * were built from that description too.
 */
final class MalformedDataTest extends TestCase
{
    /**
     * @param callable(array<int|string, mixed>): SelfNormalizingModel $fromArray
     * @param list<string>                                             $requiredProperties
     *
     * @throws RuntimeException
     */
    #[DataProviderExternal(ModelFixtureProviders::class, 'documentsMissingARequiredProperty')]
    public function testAMissingRequiredPropertyIsRejected(
        SelfNormalizingModel $model,
        callable $fromArray,
        array $requiredProperties,
    ): void {
        $accepted = [];
        foreach ($requiredProperties as $property) {
            $document = $model->toArray();
            unset($document[$property]);
            try {
                $fromArray($document);
            } catch (MalformedDataException) {
                continue;
            }
            $accepted[] = $property;
        }
        self::assertSame([], $accepted, 'documents without these required properties were accepted');
    }

    /**
     * @param callable(array<int|string, mixed>): SelfNormalizingModel $fromArray
     * @param array<array-key, int|string>                             $wrongValues
     *
     * @throws RuntimeException
     */
    #[DataProviderExternal(ModelFixtureProviders::class, 'documentsWithAMistypedProperty')]
    public function testAMistypedPropertyIsRejected(
        SelfNormalizingModel $model,
        callable $fromArray,
        array $wrongValues,
    ): void {
        $accepted = [];
        foreach ($wrongValues as $property => $wrongValue) {
            $document = $model->toArray();
            $document[$property] = $wrongValue;
            try {
                $fromArray($document);
            } catch (MalformedDataException) {
                continue;
            }
            $accepted[] = $property;
        }
        self::assertSame([], $accepted, 'values of the wrong type were accepted for these properties');
    }
}
