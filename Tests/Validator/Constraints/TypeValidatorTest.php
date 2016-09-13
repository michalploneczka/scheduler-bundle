<?php
/*
* This file is part of the scheduler-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Validator\Constraints;

use Abc\Bundle\SchedulerBundle\Schedule\ProcessorRegistryInterface;
use Abc\Bundle\SchedulerBundle\Validator\Constraints\Type;
use Abc\Bundle\SchedulerBundle\Validator\Constraints\TypeValidator;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class TypeValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessorRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var ExecutionContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var TypeValidator
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->registry = $this->getMock(ProcessorRegistryInterface::class);
        $this->subject  = new TypeValidator($this->registry);
        $this->context  = $this->getMockBuilder(ExecutionContext::class)->disableOriginalConstructor()->getMock();
        $this->subject->initialize($this->context);
    }

    public function testValidateWithNull()
    {
        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->subject->validate(null, new Type());
    }

    public function testWithTypeNotRegistered()
    {
        $value = 'foobar';
        $builder = $this->getMock(ConstraintViolationBuilderInterface::class);

        $this->registry->expects($this->once())
            ->method('has')
            ->with($value)
            ->willReturn(false);

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->willReturn($builder);

        $builder->expects($this->once())
            ->method('setParameter')
            ->with('{{string}}', $value)
            ->willReturn($builder);

        $builder->expects($this->once())
            ->method('addViolation');

        $this->subject->validate($value, new Type());
    }

    public function testValidateWithTypeRegistered()
    {
        $value = 'foobar';

        $this->registry->expects($this->once())
            ->method('has')
            ->with($value)
            ->willReturn(true);

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->subject->validate($value, new Type());
    }
}