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
use Sylius\Component\Product\Model\ProductAssociation;
use Sylius\Component\Product\Model\ProductAssociationInterface;
use Sylius\Component\Product\Model\ProductAssociationType;
use Sylius\Component\Product\Model\ProductInterface;

final class ProductAssociationTest extends TestCase
{
    /**
     * @var ProductAssociationInterface&MockObject
     */
    private MockObject $productAssociationInterface;

    private ProductAssociation $productAssociation;

    protected function setUp(): void
    {
        $this->productAssociationInterface = $this->createMock(ProductAssociationInterface::class);
        $this->productAssociation = new ProductAssociation();
    }

    public function testImplementsProductAssociationInterface(): void
    {
        self::assertInstanceOf(ProductAssociationInterface::class, $this->productAssociationInterface);
    }

    public function testHasOwner(): void
    {
        /** @var ProductInterface&MockObject $productMock */
        $productMock = $this->createMock(ProductInterface::class);
        $this->productAssociation->setOwner($productMock);
        $this->assertSame($productMock, $this->productAssociation->getOwner());
    }

    public function testHasType(): void
    {
        /** @var ProductAssociationType&MockObject $associationTypeMock */
        $associationTypeMock = $this->createMock(ProductAssociationType::class);
        $this->productAssociation->setType($associationTypeMock);
        $this->assertSame($associationTypeMock, $this->productAssociation->getType());
    }

    public function testAddsAssociationProduct(): void
    {
        /** @var ProductInterface&MockObject $productMock */
        $productMock = $this->createMock(ProductInterface::class);
        $this->productAssociation->addAssociatedProduct($productMock);
        $this->assertCount(1, $this->productAssociation->getAssociatedProducts());
    }

    public function testChecksIfProductIsAssociated(): void
    {
        /** @var ProductInterface&MockObject $productMock */
        $productMock = $this->createMock(ProductInterface::class);
        $this->assertFalse($this->productAssociation->hasAssociatedProduct($productMock));
        $this->productAssociation->addAssociatedProduct($productMock);
        $this->assertTrue($this->productAssociation->hasAssociatedProduct($productMock));
        $this->productAssociation->removeAssociatedProduct($productMock);
        $this->assertFalse($this->productAssociation->hasAssociatedProduct($productMock));
    }
}
