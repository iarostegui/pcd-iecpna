<?php

declare(strict_types=1);

namespace Pcd\Tests\Unit;

use Pcd\Support\TextNormalizer;
use PHPUnit\Framework\TestCase;

final class TextNormalizerTest extends TestCase
{
    public function testAccentsCaseAndSpacesCollapse(): void
    {
        $this->assertSame('JESUS MARIA', TextNormalizer::normalize('  Jesús   María  '));
        $this->assertSame(TextNormalizer::normalize('Jesús María'), TextNormalizer::normalize('JESUS MARIA'));
    }

    public function testPreservesVisibleLabelOutsideNormalization(): void
    {
        $this->assertSame('IECPNA JESUS MARIA', TextNormalizer::normalize('  IECPNA   Jesús María '));
    }

    public function testExactEqualityIsNotSimilarity(): void
    {
        $this->assertFalse(TextNormalizer::similar('Sede Central', 'sede central'));
    }

    public function testContainedNameIsSimilar(): void
    {
        $this->assertTrue(TextNormalizer::similar('Sede Central', 'Sede Central Anexo'));
    }

    public function testDistantNamesAreNotSimilar(): void
    {
        $this->assertFalse(TextNormalizer::similar('Sede Norte', 'Comunidad Sur'));
    }
}
