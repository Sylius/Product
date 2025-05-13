<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Sylius\Component\Product\Model;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Product\Model\ProductAssociationTypeTranslation;
use Sylius\Component\Product\Model\ProductAssociationTypeTranslationInterface;

final class ProductAssociationTypeTranslationTest extends TestCase
{
    private ProductAssociationTypeTranslationInterface&MockObject $productAssociationTypeTranslationInterface;

    private ProductAssociationTypeTranslation $productAssociationTypeTranslation;

    protected function setUp(): void
    {
        $this->productAssociationTypeTranslationInterface = $this->createMock(ProductAssociationTypeTranslationInterface::class);
        $this->productAssociationTypeTranslation = new ProductAssociationTypeTranslation();
    }

    public function testImplementsAProductAssociationTypeTranslationInterface(): void
    {
        self::assertInstanceOf(
            ProductAssociationTypeTranslationInterface::class,
            $this->productAssociationTypeTranslationInterface
        );
    }

    public function testHasNoNameByDefault(): void
    {
        $this->assertNull($this->productAssociationTypeTranslation->getName());
    }

    public function testItsNameIsMutable(): void
    {
        $this->productAssociationTypeTranslation->setName('Association type name');
        $this->assertSame('Association type name', $this->productAssociationTypeTranslation->getName());
    }
}
