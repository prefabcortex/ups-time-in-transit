<?php

declare(strict_types=1);

namespace Prefabcortex\UpsTimeInTransit\Tests;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Prefabcortex\UpsTimeInTransit\Model\SelfNormalizingModel;
use Prefabcortex\UpsTimeInTransit\Tests\Fixture\ModelFixtureProviders;
use RuntimeException;

/**
 * Every model in this package, built and pushed through `toArray()` and back.
 *
 * Building the instance is half the test: it is the only place anything calls `new` on a generated
 * model, so a constructor that leaves a declared property unassigned fails here and nowhere else.
 * The assertion is the other half, and it holds the two serialisation directions to each other —
 * they are emitted separately and agree only by construction.
 *
 * What this cannot show: that the service agrees with its own description. The fixtures come from
 * the same document the client came from, so both believe it equally.
 */
final class ModelRoundTripTest extends TestCase
{
    /**
     * @param callable(array<int|string, mixed>): SelfNormalizingModel $fromArray
     *
     * @throws RuntimeException
     */
    #[DataProviderExternal(ModelFixtureProviders::class, 'roundTrips')]
    public function testModelSurvivesAnArrayRoundTrip(SelfNormalizingModel $model, callable $fromArray): void
    {
        $restored = $fromArray($model->toArray());
        self::assertSame(
            $model->toArray(),
            $restored->toArray(),
            'the rebuilt instance does not serialise back to the same document',
        );
    }
}
