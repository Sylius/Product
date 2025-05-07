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

namespace Tests\Sylius\Component\Product\Generator;

use PHPUnit\Framework\MockObject\MockObject;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Product\Checker\ProductVariantsParityCheckerInterface;
use Sylius\Component\Product\Exception\ProductWithoutOptionsException;
use Sylius\Component\Product\Exception\ProductWithoutOptionsValuesException;
use Sylius\Component\Product\Factory\ProductVariantFactoryInterface;
use Sylius\Component\Product\Generator\ProductVariantGenerator;
use Sylius\Component\Product\Generator\ProductVariantGeneratorInterface;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Product\Model\ProductVariant;
use Sylius\Component\Product\Model\ProductVariantInterface;

final class ProductVariantGeneratorTest extends TestCase
{
    /**
     * @var ProductVariantFactoryInterface<ProductVariant>&MockObject
     */
    private MockObject $productVariantFactoryMock;

    /**
     * @var ProductVariantsParityCheckerInterface&MockObject
     */
    private MockObject $variantsParityCheckerMock;

    private ProductVariantGenerator $productVariantGenerator;

    protected function setUp(): void
    {
        $this->productVariantFactoryMock = $this->createMock(ProductVariantFactoryInterface::class);
        $this->variantsParityCheckerMock = $this->createMock(ProductVariantsParityCheckerInterface::class);
        $this->productVariantGenerator = new ProductVariantGenerator(
            $this->productVariantFactoryMock,
            $this->variantsParityCheckerMock
        );
    }

    public function testImplementsProductVariantGeneratorInterface(): void
    {
        $this->assertInstanceOf(ProductVariantGeneratorInterface::class, $this->productVariantGenerator);
    }

    public function testThrowsAnExceptionIfProductHasNoOptions(): void
    {
        /** @var ProductInterface&MockObject $productMock */
        $productMock = $this->createMock(ProductInterface::class);
        $productMock->expects($this->once())->method('hasOptions')->willReturn(false);
        $this->expectException(ProductWithoutOptionsException::class);
        $this->productVariantGenerator->generate($productMock);
    }

    public function testThrowsAnExceptionIfProductHasNoOptionsValues(): void
    {
        /** @var ProductInterface&MockObject $productMock */
        $productMock = $this->createMock(ProductInterface::class);
        /** @var ProductOptionInterface&MockObject $colorOptionMock */
        $colorOptionMock = $this->createMock(ProductOptionInterface::class);
        $productMock->expects($this->once())->method('hasOptions')->willReturn(true);
        $productMock->expects($this->once())->method('getOptions')->willReturn(new ArrayCollection([$colorOptionMock]));
        $colorOptionMock->expects($this->once())->method('getValues')->willReturn(new ArrayCollection([]));
        $this->expectException(ProductWithoutOptionsValuesException::class);
        $this->productVariantGenerator->generate($productMock);
    }

    public function testGeneratesVariantsForEveryValueOfAnObjectsSingleOption(): void
    {
        /** @var ProductInterface&MockObject $productVariableMock */
        $productVariableMock = $this->createMock(ProductInterface::class);
        /** @var ProductOptionInterface&MockObject $colorOptionMock */
        $colorOptionMock = $this->createMock(ProductOptionInterface::class);
        /** @var ProductOptionValueInterface&MockObject $blackColorMock */
        $blackColorMock = $this->createMock(ProductOptionValueInterface::class);
        /** @var ProductOptionValueInterface&MockObject $redColorMock */
        $redColorMock = $this->createMock(ProductOptionValueInterface::class);
        /** @var ProductOptionValueInterface&MockObject $whiteColorMock */
        $whiteColorMock = $this->createMock(ProductOptionValueInterface::class);
        /** @var ProductVariantInterface&MockObject $permutationVariantMock */
        $permutationVariantMock = $this->createMock(ProductVariantInterface::class);

        $productVariableMock->expects($this->once())->method('hasOptions')->willReturn(true);
        $productVariableMock->expects($this->once())
                            ->method('getOptions')
                            ->willReturn(new ArrayCollection([$colorOptionMock]));
        $colorOptionMock->expects($this->once())
                        ->method('getValues')
                        ->willReturn(new ArrayCollection([$blackColorMock, $whiteColorMock, $redColorMock]));
        $blackColorMock->expects($this->atLeastOnce())->method('getCode')->willReturn('black1');
        $whiteColorMock->expects($this->atLeastOnce())->method('getCode')->willReturn('white2');
        $redColorMock->expects($this->atLeastOnce())->method('getCode')->willReturn('red3');
        $this->variantsParityCheckerMock->expects($this->atLeastOnce())
                                        ->method('checkParity')
                                        ->with($permutationVariantMock, $productVariableMock)
                                        ->willReturn(false);
        $this->productVariantFactoryMock->expects($this->atLeastOnce())
                                        ->method('createForProduct')
                                        ->with($productVariableMock)
                                        ->willReturn($permutationVariantMock);
        $permutationVariantMock->expects($this->atLeastOnce())
                               ->method('addOptionValue')
                               ->with($this->isInstanceOf(ProductOptionValueInterface::class));
        $productVariableMock->expects($this->atLeastOnce())->method('addVariant')->with($permutationVariantMock);
        $this->productVariantGenerator->generate($productVariableMock);
    }

    public function testDoesNotGenerateVariantIfGivenVariantExists(): void
    {
        /** @var ProductInterface&MockObject $productVariableMock */
        $productVariableMock = $this->createMock(ProductInterface::class);
        /** @var ProductOptionInterface&MockObject $colorOptionMock */
        $colorOptionMock = $this->createMock(ProductOptionInterface::class);
        /** @var ProductOptionValueInterface&MockObject $blackColorMock */
        $blackColorMock = $this->createMock(ProductOptionValueInterface::class);
        /** @var ProductOptionValueInterface&MockObject $redColorMock */
        $redColorMock = $this->createMock(ProductOptionValueInterface::class);
        /** @var ProductOptionValueInterface&MockObject $whiteColorMock */
        $whiteColorMock = $this->createMock(ProductOptionValueInterface::class);
        /** @var ProductVariantInterface&MockObject $permutationVariantMock */
        $permutationVariantMock = $this->createMock(ProductVariantInterface::class);
        $productVariableMock->expects($this->once())->method('hasOptions')->willReturn(true);
        $productVariableMock->expects($this->once())
                            ->method('getOptions')
                            ->willReturn(new ArrayCollection([$colorOptionMock]));
        $colorOptionMock->expects($this->once())->method('getValues')->willReturn(
            new ArrayCollection([$blackColorMock, $whiteColorMock, $redColorMock]),
        );
        $blackColorMock->expects($this->atLeastOnce())->method('getCode')->willReturn('black1');
        $whiteColorMock->expects($this->atLeastOnce())->method('getCode')->willReturn('white2');
        $redColorMock->expects($this->atLeastOnce())->method('getCode')->willReturn('red3');
        $this->variantsParityCheckerMock->expects($this->atLeastOnce())
                                        ->method('checkParity')
                                        ->with($permutationVariantMock, $productVariableMock)
                                        ->willReturn(true);
        $this->productVariantFactoryMock->expects($this->atLeastOnce())
                                        ->method('createForProduct')
                                        ->with($productVariableMock)
                                        ->willReturn($permutationVariantMock);
        $permutationVariantMock->expects($this->atLeastOnce())
                               ->method('addOptionValue')
                               ->with($this->isInstanceOf(ProductOptionValueInterface::class));
        $productVariableMock->expects($this->never())->method('addVariant')->with($permutationVariantMock);
        $this->productVariantGenerator->generate($productVariableMock);
    }

    public function testGeneratesVariantsForEveryPossiblePermutationOfAnObjectsOptionsAndOptionValues(): void
    {
        /** @var ProductInterface&MockObject $productVariableMock */
        $productVariableMock = $this->createMock(ProductInterface::class);
        /** @var ProductOptionInterface&MockObject $colorOptionMock */
        $colorOptionMock = $this->createMock(ProductOptionInterface::class);
        /** @var ProductOptionInterface&MockObject $sizeOptionMock */
        $sizeOptionMock = $this->createMock(ProductOptionInterface::class);
        /** @var ProductOptionValueInterface&MockObject $blackColorMock */
        $blackColorMock = $this->createMock(ProductOptionValueInterface::class);
        /** @var ProductOptionValueInterface&MockObject $largeSizeMock */
        $largeSizeMock = $this->createMock(ProductOptionValueInterface::class);
        /** @var ProductOptionValueInterface&MockObject $mediumSizeMock */
        $mediumSizeMock = $this->createMock(ProductOptionValueInterface::class);
        /** @var ProductOptionValueInterface&MockObject $redColorMock */
        $redColorMock = $this->createMock(ProductOptionValueInterface::class);
        /** @var ProductOptionValueInterface&MockObject $smallSizeMock */
        $smallSizeMock = $this->createMock(ProductOptionValueInterface::class);
        /** @var ProductOptionValueInterface&MockObject $whiteColorMock */
        $whiteColorMock = $this->createMock(ProductOptionValueInterface::class);
        /** @var ProductVariantInterface&MockObject $permutationVariantMock */
        $permutationVariantMock = $this->createMock(ProductVariantInterface::class);
        $productVariableMock->expects($this->once())->method('hasOptions')->willReturn(true);
        $productVariableMock->expects($this->once())->method('getOptions')->willReturn(
            new ArrayCollection([$colorOptionMock, $sizeOptionMock]),
        );
        $colorOptionMock->expects($this->once())->method('getValues')->willReturn(
            new ArrayCollection([$blackColorMock, $whiteColorMock, $redColorMock]),
        );
        $sizeOptionMock->expects($this->once())->method('getValues')->willReturn(
            new ArrayCollection([$smallSizeMock, $mediumSizeMock, $largeSizeMock]),
        );
        $blackColorMock->expects($this->atLeastOnce())->method('getCode')->willReturn('black1');
        $whiteColorMock->expects($this->atLeastOnce())->method('getCode')->willReturn('white2');
        $redColorMock->expects($this->atLeastOnce())->method('getCode')->willReturn('red3');
        $smallSizeMock->expects($this->atLeastOnce())->method('getCode')->willReturn('small4');
        $mediumSizeMock->expects($this->atLeastOnce())->method('getCode')->willReturn('medium5');
        $largeSizeMock->expects($this->atLeastOnce())->method('getCode')->willReturn('large6');
        $this->variantsParityCheckerMock->expects($this->atLeastOnce())
                                        ->method('checkParity')
                                        ->with($permutationVariantMock, $productVariableMock)
                                        ->willReturn(false);
        $this->productVariantFactoryMock->expects($this->atLeastOnce())
                                        ->method('createForProduct')
                                        ->with($productVariableMock)
                                        ->willReturn($permutationVariantMock);
        $permutationVariantMock->expects($this->atLeastOnce())
                               ->method('addOptionValue')
                               ->with($this->isInstanceOf(ProductOptionValueInterface::class));
        $productVariableMock->expects($this->atLeastOnce())->method('addVariant')->with($permutationVariantMock);
        $this->productVariantGenerator->generate($productVariableMock);
    }
}
