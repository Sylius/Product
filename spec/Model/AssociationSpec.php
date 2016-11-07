<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Sylius\Component\Product\Model;

use PhpSpec\ObjectBehavior;
use Sylius\Component\Product\Model\AssociableInterface;
use Sylius\Component\Product\Model\Association;
use Sylius\Component\Product\Model\AssociationInterface;
use Sylius\Component\Product\Model\AssociationType;

/**
 * @author Leszek Prabucki <leszek.prabucki@gmail.com>
 * @author Łukasz Chruściel <lukasz.chrusciel@lakion.com>
 */
final class AssociationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Association::class);
    }

    function it_implements_association_interface()
    {
        $this->shouldHaveType(AssociationInterface::class);
    }

    function it_has_owner(AssociableInterface $product)
    {
        $this->setOwner($product);
        $this->getOwner()->shouldReturn($product);
    }

    function it_has_type(AssociationType $associationType)
    {
        $this->setType($associationType);
        $this->getType()->shouldReturn($associationType);
    }

    function it_adds_association_objects(AssociableInterface $product)
    {
        $this->addAssociatedObject($product);
        $this->getAssociatedObjects()->shouldHaveCount(1);
    }

    function it_checks_if_product_is_associated(AssociableInterface $product)
    {
        $this->hasAssociatedObject($product)->shouldReturn(false);

        $this->addAssociatedObject($product);
        $this->hasAssociatedObject($product)->shouldReturn(true);

        $this->removeAssociatedObject($product);
        $this->hasAssociatedObject($product)->shouldReturn(false);
    }
}
