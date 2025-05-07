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

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Product\Model\Product;
use Sylius\Component\Product\Model\ProductAssociationInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Resource\Model\ToggleableInterface;

final class ProductTest extends TestCase
{
    /**
     * @var ProductAttributeValueInterface&MockObject
     */
    private MockObject $productAttribute;


    private Product $product;

    /**
     * @var AttributeInterface&MockObject
     */
    private MockObject $attribute;

    protected function setUp(): void
    {
        $this->productAttribute = $this->createMock(ProductAttributeValueInterface::class);
        $this->product = new Product();
        $this->product->setCurrentLocale('en_US');
        $this->product->setFallbackLocale('en_US');
        $this->attribute = $this->createMock(AttributeInterface::class);
    }

    public function testImplementsProductInterface(): void
    {
        self::assertInstanceOf(ProductInterface::class, $this->product);
    }

    public function testImplementsToggleableInterface(): void
    {
        self::assertInstanceOf(ToggleableInterface::class, $this->product);
    }

    public function testHasNoIdByDefault(): void
    {
        $this->assertNull($this->product->getId());
    }

    public function testHasNoNameByDefault(): void
    {
        $this->assertNull($this->product->getName());
    }

    public function testItsNameIsMutable(): void
    {
        $this->product->setName('Super product');
        $this->assertSame('Super product', $this->product->getName());
    }

    public function testHasADescriptor(): void
    {
        $this->product->setName('Name');
        $this->product->setCode('code');

        $this->assertSame('Name (code)', $this->product->getDescriptor());
    }

    public function testHasNoSlugByDefault(): void
    {
        $this->assertNull($this->product->getSlug());
    }

    public function testItsSlugIsMutable(): void
    {
        $this->product->setSlug('super-product');
        $this->assertSame('super-product', $this->product->getSlug());
    }

    public function testHasNoDescriptionByDefault(): void
    {
        $this->assertNull($this->product->getDescription());
    }

    public function testItsDescriptionIsMutable(): void
    {
        $this->product->setDescription('This product is super cool because...');
        $this->assertSame('This product is super cool because...', $this->product->getDescription());
    }

    public function testAddsAttribute(): void
    {
        $this->productAttribute->expects($this->once())
                        ->method('setProduct')
                        ->with($this->product);

        $this->product->addAttribute($this->productAttribute);
        $this->assertTrue($this->product->hasAttribute($this->productAttribute));
    }

    public function testRemovesAttribute(): void
    {
        $this->productAttribute->expects($this->exactly(2))
                  ->method('setProduct')
                  ->willReturnCallback(function ($arg) use (&$calls) {
                      static $callIndex = 0;
                      $expectedArgs = [$this->product, null];

                      TestCase::assertSame($expectedArgs[$callIndex], $arg);
                      $callIndex++;
                  });

        $this->product->addAttribute($this->productAttribute);
        $this->assertTrue($this->product->hasAttribute($this->productAttribute));

        $this->product->removeAttribute($this->productAttribute);
        $this->assertFalse($this->product->hasAttribute($this->productAttribute));
    }

    public function testRefusesToAddNonProductAttribute(): void
    {
        $attributeValue = $this->createMock(AttributeValueInterface::class);

        $this->expectException(InvalidArgumentException::class);

        $this->product->addAttribute(null);

        $this->assertFalse($this->product->hasAttribute($attributeValue));
    }

    public function testRefusesToRemoveNonProductAttribute(): void
    {
        $attributeValue = $this->createMock(AttributeValueInterface::class);

        $this->expectException(InvalidArgumentException::class);

        $this->product->removeAttribute($attributeValue);
    }

    public function testReturnsAttributesByALocaleWithoutABaseLocale(): void
    {
        $attributeValueEN = $this->createMock(ProductAttributeValueInterface::class);

        $attributeValueEN->expects($this->once())->method('setProduct')->with($this->product);
        $attributeValueEN->expects($this->atLeastOnce())->method('getLocaleCode')->willReturn('en_US');
        $attributeValueEN->expects($this->atLeastOnce())->method('getAttribute')->willReturn($this->attribute);
        $attributeValueEN->expects($this->once())->method('getCode')->willReturn('colour');

        $attributeValuePL = $this->createMock(ProductAttributeValueInterface::class);

        $attributeValuePL->expects($this->once())->method('setProduct')->with($this->product);
        $attributeValuePL->expects($this->atLeastOnce())->method('getLocaleCode')->willReturn('pl_PL');
        $attributeValuePL->expects($this->atLeastOnce())->method('getAttribute')->willReturn($this->attribute);
        $attributeValuePL->expects($this->once())->method('getCode')->willReturn('colour');

        $this->product->addAttribute($attributeValueEN);
        $this->product->addAttribute($attributeValuePL);

        $resultEN = $this->product->getAttributesByLocale('en_US', 'pl_PL');
        $resultPL = $this->product->getAttributesByLocale('pl_PL', 'en_US');

        $this->assertEquals([$attributeValueEN], iterator_to_array($resultEN));
        $this->assertEquals([$attributeValuePL], iterator_to_array($resultPL));
    }

    public function testReturnsAttributesByALocaleWithABaseLocale(): void
    {
        $attributeValueEN = $this->createMock(ProductAttributeValueInterface::class);

        $attributeValueEN->expects($this->once())->method('setProduct')->with($this->product);
        $attributeValueEN->expects($this->atLeastOnce())->method('getLocaleCode')->willReturn('en_US');
        $attributeValueEN->expects($this->atLeastOnce())->method('getAttribute')->willReturn($this->attribute);
        $attributeValueEN->expects($this->once())->method('getCode')->willReturn('colour');

        $attributeValuePL = $this->createMock(ProductAttributeValueInterface::class);

        $attributeValuePL->expects($this->once())->method('setProduct')->with($this->product);
        $attributeValuePL->expects($this->atLeastOnce())->method('getLocaleCode')->willReturn('pl_PL');
        $attributeValuePL->expects($this->atLeastOnce())->method('getAttribute')->willReturn($this->attribute);
        $attributeValuePL->expects($this->atLeastOnce())->method('getCode')->willReturn('colour');

        $attributeValueFR = $this->createMock(ProductAttributeValueInterface::class);

        $attributeValueFR->expects($this->once())->method('setProduct')->with($this->product);
        $attributeValueFR->expects($this->atLeastOnce())->method('getLocaleCode')->willReturn('fr_FR');
        $attributeValueFR->expects($this->atLeastOnce())->method('getAttribute')->willReturn($this->attribute);
        $attributeValueFR->expects($this->once())->method('getCode')->willReturn('colour');

        $this->product->addAttribute($attributeValueEN);
        $this->product->addAttribute($attributeValuePL);
        $this->product->addAttribute($attributeValueFR);

        $resultEN = $this->product->getAttributesByLocale('en_US', 'pl_PL');
        $resultPL = $this->product->getAttributesByLocale('pl_PL', 'fr_FR');
        $resultFR = $this->product->getAttributesByLocale('fr_FR', 'en_US');

        $this->assertEquals([$attributeValueEN], iterator_to_array($resultEN));
        $this->assertEquals([$attributeValuePL], iterator_to_array($resultPL));
        $this->assertEquals([$attributeValueFR], iterator_to_array($resultFR));
    }

    public function testReturnsAttributesByAFallbackLocaleWhenThereIsNoValueForAGivenLocale(): void
    {
        $attributeValueEN = $this->createMock(ProductAttributeValueInterface::class);

        $attributeValueEN->expects($this->once())->method('setProduct')->with($this->product);
        $attributeValueEN->expects($this->atLeastOnce())->method('getLocaleCode')->willReturn('en_US');
        $attributeValueEN->expects($this->once())->method('getAttribute')->willReturn($this->attribute);
        $attributeValueEN->expects($this->once())->method('getCode')->willReturn('colour');

        $this->product->addAttribute($attributeValueEN);

        $resultEN = $this->product->getAttributesByLocale('pl_PL', 'en_US');

        $this->assertEquals([$attributeValueEN], iterator_to_array($resultEN));
    }

    public function testReturnsAttributesByAFallbackLocaleWhenThereIsAnEmptyValueForAGivenLocale(): void
    {
        $attributeValueEN = $this->createMock(ProductAttributeValueInterface::class);

        $attributeValueEN->expects($this->once())->method('setProduct')->with($this->product);
        $attributeValueEN->expects($this->atLeastOnce())->method('getLocaleCode')->willReturn('en_US');
        $attributeValueEN->expects($this->atLeastOnce())->method('getAttribute')->willReturn($this->attribute);
        $attributeValueEN->expects($this->once())->method('getCode')->willReturn('colour');

        $attributeValuePL = $this->createMock(ProductAttributeValueInterface::class);

        $attributeValuePL->expects($this->once())->method('setProduct')->with($this->product);
        $attributeValuePL->expects($this->atLeastOnce())->method('getLocaleCode')->willReturn('pl_PL');
        $attributeValuePL->expects($this->atLeastOnce())->method('getAttribute')->willReturn($this->attribute);
        $attributeValuePL->expects($this->once())->method('getCode')->willReturn('colour');

        $this->product->addAttribute($attributeValueEN);
        $this->product->addAttribute($attributeValuePL);

        $resultEN = $this->product->getAttributesByLocale('en_US', 'pl_PL');
        $resultPL = $this->product->getAttributesByLocale('pl_PL', 'en_US');

        $this->assertEquals([$attributeValueEN], iterator_to_array($resultEN));
        $this->assertEquals([$attributeValuePL], iterator_to_array($resultPL));
    }

    public function testReturnsAttributesByABaseLocaleWhenThereIsNoValueForAGivenLocaleOrAFallbackLocale(): void
    {
        $attributeValueFR = $this->createMock(ProductAttributeValueInterface::class);

        $attributeValueFR->expects($this->once())->method('setProduct')->with($this->product);
        $attributeValueFR->expects($this->atLeastOnce())->method('getLocaleCode')->willReturn('fr_FR');
        $attributeValueFR->expects($this->atLeastOnce())->method('getAttribute')->willReturn($this->attribute);
        $attributeValueFR->expects($this->atLeastOnce())->method('getCode')->willReturn('colour');

        $this->product->addAttribute($attributeValueFR);

        $resultFR = $this->product->getAttributesByLocale('pl_PL', 'en_US', 'fr_FR');

        $this->assertEquals([$attributeValueFR], iterator_to_array($resultFR));
    }

    public function testReturnsAttributesByABaseLocaleWhenThereIsAnEmptyValueForAGivenLocaleOrAFallbackLocale(): void
    {
        $attributeValueEN = $this->createMock(ProductAttributeValueInterface::class);

        $attributeValueEN->expects($this->once())->method('setProduct')->with($this->product);
        $attributeValueEN->expects($this->atLeastOnce())->method('getLocaleCode')->willReturn('en_US');
        $attributeValueEN->expects($this->atLeastOnce())->method('getAttribute')->willReturn($this->attribute);
        $attributeValueEN->expects($this->once())->method('getCode')->willReturn('colour');

        $attributeValuePL = $this->createMock(ProductAttributeValueInterface::class);

        $attributeValuePL->expects($this->once())->method('setProduct')->with($this->product);
        $attributeValuePL->expects($this->atLeastOnce())->method('getLocaleCode')->willReturn('pl_PL');
        $attributeValuePL->expects($this->atLeastOnce())->method('getAttribute')->willReturn($this->attribute);
        $attributeValuePL->expects($this->once())->method('getCode')->willReturn('colour');

        $attributeValueFR = $this->createMock(ProductAttributeValueInterface::class);

        $attributeValueFR->expects($this->once())->method('setProduct')->with($this->product);
        $attributeValueFR->expects($this->atLeastOnce())->method('getLocaleCode')->willReturn('fr_FR');
        $attributeValueFR->expects($this->atLeastOnce())->method('getAttribute')->willReturn($this->attribute);
        $attributeValueFR->expects($this->once())->method('getCode')->willReturn('colour');

        $this->product->addAttribute($attributeValueEN);
        $this->product->addAttribute($attributeValuePL);
        $this->product->addAttribute($attributeValueFR);

        $resultEN = $this->product->getAttributesByLocale('en_US', 'pl_PL');
        $resultPL = $this->product->getAttributesByLocale('pl_PL', 'fr_FR');
        $resultFR = $this->product->getAttributesByLocale('fr_FR', 'en_US');

        $this->assertEquals([$attributeValueEN], iterator_to_array($resultEN));
        $this->assertEquals([$attributeValuePL], iterator_to_array($resultPL));
        $this->assertEquals([$attributeValueFR], iterator_to_array($resultFR));
    }

    public function testHasNoVariantsByDefault(): void
    {
        self::assertFalse($this->product->hasVariants());
    }

    public function testItsSaysItHasVariantsOnlyIfMultipleVariantsAreDefined(): void
    {
        $firstVariant = $this->createMock(ProductVariantInterface::class);
        $firstVariant->expects($this->once())->method('setProduct')->with($this->product);

        $secondVariant  = $this->createMock(ProductVariantInterface::class);
        $secondVariant->expects($this->once())->method('setProduct')->with($this->product);

        $this->product->addVariant($firstVariant);
        $this->product->addVariant($secondVariant);
        $this->assertTrue($this->product->hasVariants());
    }

    public function testDoesNotIncludeUnavailableVariantsInAvailableVariants(): void
    {
        $variant  = $this->createMock(ProductVariantInterface::class);

        $variant->expects($this->once())->method('setProduct')->with($this->product);

        $this->product->addVariant($variant);
    }

    public function testReturnsAvailableVariants(): void
    {
        $unavailableVariant = $this->createMock(ProductVariantInterface::class);
        $unavailableVariant->expects($this->once())->method('setProduct')->with($this->product);

        $variant  = $this->createMock(ProductVariantInterface::class);
        $variant->expects($this->once())->method('setProduct')->with($this->product);

        $this->product->addVariant($unavailableVariant);
        $this->product->addVariant($variant);
    }

    public function testHasNoOptionsByDefault(): void
    {
        $this->assertFalse($this->product->hasOptions());
    }

    public function testItsSaysItHasOptionsOnlyIfAnyOptionDefined(): void
    {
        $option = $this->createMock(ProductOptionInterface::class);
        $this->product->addOption($option);
        $this->assertTrue($this->product->hasOptions());
    }

    public function testAddsOptionProperly(): void
    {
        $option = $this->createMock(ProductOptionInterface::class);
        $this->product->addOption($option);
        $this->assertTrue($this->product->hasOption($option));
    }

    public function testRemovesOptionProperly(): void
    {
        $option = $this->createMock(ProductOptionInterface::class);

        $this->product->addOption($option);
        $this->assertTrue($this->product->hasOption($option));

        $this->product->removeOption($option);
        $this->assertFalse($this->product->hasOption($option));
    }

    public function testItsCreationDateIsMutable(): void
    {
        $creationDate = new DateTimeImmutable();
        $this->product->setCreatedAt($creationDate);
        $this->assertSame($creationDate, $this->product->getCreatedAt());
    }

    public function testHasNoLastUpdateDateByDefault(): void
    {
        $this->assertNull($this->product->getUpdatedAt());
    }

    public function testItsLastUpdateDateIsMutable(): void
    {
        $updateDate = new DateTimeImmutable();
        $this->product->setUpdatedAt($updateDate);
        $this->assertSame($updateDate, $this->product->getUpdatedAt());
    }

    public function testEnabledByDefault(): void
    {
        $this->assertTrue($this->product->isEnabled());
    }

    public function testToggleable(): void
    {
        $this->product->disable();
        self::assertFalse($this->product->isEnabled());

        $this->product->enable();
        $this->assertTrue($this->product->isEnabled());
    }

    public function testAddsAssociation(): void
    {
        $association  = $this->createMock(ProductAssociationInterface::class);

        $association->expects($this->once())->method('setOwner')->with($this->product);
        $this->product->addAssociation($association);

        $this->assertTrue($this->product->hasAssociation($association));
    }

    public function testAllowsToRemoveAssociation(): void
    {
        $association = $this->createMock(ProductAssociationInterface::class);

        $association->expects($this->exactly(2))
                    ->method('setOwner')
                    ->willReturnCallback(function ($arg) use (&$calls) {
                        static $callIndex = 0;
                        $expectedArgs = [$this->product, null];

                        TestCase::assertSame($expectedArgs[$callIndex], $arg);
                        $callIndex++;
                    });

        $this->product->addAssociation($association);
        $this->product->removeAssociation($association);

        $this->assertFalse($this->product->hasAssociation($association));
    }

    public function testSimpleIfItHasOneVariantAndNoOptions(): void
    {
        $variant = $this->createMock(ProductVariantInterface::class);

        $variant->expects($this->once())->method('setProduct')->with($this->product);
        $this->product->addVariant($variant);

        $this->assertTrue($this->product->isSimple());
        $this->assertFalse($this->product->isConfigurable());
    }

    public function testConfigurableIfItHasAtLeastTwoVariants(): void
    {
        $firstVariant  = $this->createMock(ProductVariantInterface::class);

        $firstVariant->expects($this->once())->method('setProduct')->with($this->product);
        $this->product->addVariant($firstVariant);

        $secondVariant  = $this->createMock(ProductVariantInterface::class);

        $secondVariant->expects($this->once())->method('setProduct')->with($this->product);
        $this->product->addVariant($secondVariant);

        $this->assertTrue($this->product->isConfigurable());
        $this->assertFalse($this->product->isSimple());
    }

    public function testConfigurableIfItHasOneVariantAndAtLeastOneOption(): void
    {
        $variant  = $this->createMock(ProductVariantInterface::class);
        $option = $this->createMock(ProductOptionInterface::class);

        $variant->expects($this->once())->method('setProduct')->with($this->product);
        $this->product->addVariant($variant);
        $this->product->addOption($option);

        $this->assertTrue($this->product->isConfigurable());
        $this->assertFalse($this->product->isSimple());
    }
}
