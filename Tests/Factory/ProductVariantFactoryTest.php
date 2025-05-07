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

namespace Tests\Sylius\Component\Product\Factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Product\Factory\ProductVariantFactory;
use Sylius\Component\Product\Factory\ProductVariantFactoryInterface;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Resource\Factory\FactoryInterface;

final class ProductVariantFactoryTest extends TestCase
{
    /**
     * @var FactoryInterface|MockObject
     */
    private MockObject $factoryMock;

    private ProductVariantFactory $productVariantFactory;

    protected function setUp(): void
    {
        $this->factoryMock = $this->createMock(FactoryInterface::class);
        $this->productVariantFactory = new ProductVariantFactory($this->factoryMock);
    }

    public function testAResourceFactory(): void
    {
        $this->assertInstanceOf(FactoryInterface::class, $this->productVariantFactory);
    }

    public function testImplementsVariantFactoryInterface(): void
    {
        $this->assertInstanceOf(ProductVariantFactoryInterface::class, $this->productVariantFactory);
    }

    public function testCreatesNewVariant(): void
    {
        /** @var ProductVariantInterface|MockObject $variantMock */
        $variantMock = $this->createMock(ProductVariantInterface::class);
        $this->factoryMock->expects($this->once())->method('createNew')->willReturn($variantMock);
        $this->assertSame($variantMock, $this->productVariantFactory->createNew());
    }

    public function testCreatesAVariantAndAssignsAProductToIt(): void
    {
        /** @var ProductInterface|MockObject $productMock */
        $productMock = $this->createMock(ProductInterface::class);
        /** @var ProductVariantInterface|MockObject $variantMock */
        $variantMock = $this->createMock(ProductVariantInterface::class);
        $this->factoryMock->expects($this->once())->method('createNew')->willReturn($variantMock);
        $variantMock->expects($this->once())->method('setProduct')->with($productMock);
        $this->assertSame($variantMock, $this->productVariantFactory->createForProduct($productMock));
    }
}
