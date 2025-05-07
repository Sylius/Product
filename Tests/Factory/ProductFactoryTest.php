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
use Sylius\Component\Product\Factory\ProductFactory;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Resource\Factory\FactoryInterface;

final class ProductFactoryTest extends TestCase
{
    /**
     * @var FactoryInterface|MockObject
     */
    private MockObject $factoryMock;

    /**
     * @var FactoryInterface|MockObject
     */
    private MockObject $variantFactoryMock;


    private ProductFactory $productFactory;

    protected function setUp(): void
    {
        $this->factoryMock = $this->createMock(FactoryInterface::class);
        $this->variantFactoryMock = $this->createMock(FactoryInterface::class);
        $this->productFactory = new ProductFactory($this->factoryMock, $this->variantFactoryMock);
    }

    public function testImplementsProductFactoryInterface(): void
    {
        $this->assertInstanceOf(ProductFactoryInterface::class, $this->productFactory);
    }

    public function testCreatesNewProduct(): void
    {
        /** @var ProductInterface|MockObject $productMock */
        $productMock = $this->createMock(ProductInterface::class);
        $this->factoryMock->expects($this->once())->method('createNew')->willReturn($productMock);
        $this->assertSame($productMock, $this->productFactory->createNew());
    }

    public function testCreatesNewProductWithVariant(): void
    {
        /** @var ProductInterface|MockObject $productMock */
        $productMock = $this->createMock(ProductInterface::class);
        /** @var ProductVariantInterface|MockObject $variantMock */
        $variantMock = $this->createMock(ProductVariantInterface::class);
        $this->variantFactoryMock->expects($this->once())->method('createNew')->willReturn($variantMock);
        $this->factoryMock->expects($this->once())->method('createNew')->willReturn($productMock);
        $productMock->expects($this->once())->method('addVariant')->with($variantMock);
        $this->assertSame($productMock, $this->productFactory->createWithVariant());
    }
}
