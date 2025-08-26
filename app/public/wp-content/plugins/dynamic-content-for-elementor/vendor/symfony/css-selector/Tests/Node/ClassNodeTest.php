<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace DynamicOOOS\Symfony\Component\CssSelector\Tests\Node;

use DynamicOOOS\Symfony\Component\CssSelector\Node\ClassNode;
use DynamicOOOS\Symfony\Component\CssSelector\Node\ElementNode;
class ClassNodeTest extends AbstractNodeTest
{
    public function getToStringConversionTestData()
    {
        return [[new ClassNode(new ElementNode(), 'class'), 'Class[Element[*].class]']];
    }
    public function getSpecificityValueTestData()
    {
        return [[new ClassNode(new ElementNode(), 'class'), 10], [new ClassNode(new ElementNode(null, 'element'), 'class'), 11]];
    }
}
