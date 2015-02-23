<?php

namespace Psecio\Parse\Rule\Helper;

use PhpParser\Node;
use PhpParser\Node\Name;
use Mockery as M;

/**
 * Base test for implementing parse based unit tests for traits
 */
class IsFunctionCallTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Parser The parser to use to parse samples
     */
    protected $ifcTest;

    /**
     * Set up the parser the same way the Scanner does
     */
    public function setUp()
    {
        $this->ifcTest = new IsFunctionCallTester;
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Node
     */
    public function testGetCalledFunctionName_throwsOnBadNode()
    {
        $node = M::mock('\\PhpParser\\Node');
        $this->ifcTest->callGetCalledFunctionName($node);
    }

    public function testGetCalledFunctionName_returnsName()
    {
        $name = 'aFunctionLivesHere';
        $node = M::mock('\\PhpParser\\Node\\Expr\\FuncCall');
        $nodeName = M::mock('\\PhpParser\\Node\\Name');
        $nodeName->shouldReceive('__toString')
                 ->andReturn($name);
        $node->name = $nodeName;

        $actual = $this->ifcTest->callGetCalledFunctionName($node);
        $this->assertSame($name, $actual);
    }

    public function testGetCalledFunctionName_emptyStringOnMissingName()
    {
        $node = M::mock('\\PhpParser\\Node\\Expr\\FuncCall');
        $actual = $this->ifcTest->callGetCalledFunctionName($node);
        $this->assertSame('', $actual);
    }

    public function testGetCalledFunctionName_emptyStringOnNonNameObject()
    {
        $node = M::mock('\\PhpParser\\Node\\Expr\\FuncCall');
        $node->name = new \stdClass;
        $actual = $this->ifcTest->callGetCalledFunctionName($node);
        $this->assertSame('', $actual);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Node
     */
    public function testGetCalledFunctionArgument_throwsOnBadNode()
    {
        $node = M::mock('\\PhpParser\\Node');
        $this->ifcTest->callGetCalledFunctionArgument($node, 0);
    }

    public function testGetCalledFunctionArgument_emptyStringOnNoArgs()
    {
        $node = M::mock('\\PhpParser\\Node\\Expr\\FuncCall');
        $emptyString = new \PhpParser\Node\Scalar\String('');
        $expected = new \PhpParser\Node\Arg($emptyString);

        $actual = $this->ifcTest->callGetCalledFunctionArgument($node, 0);

        $this->assertEquals($expected, $actual);
    }

    public function testGetCalledFunctionArgument_emptyStringOnMissingArg()
    {
        $node = M::mock('\\PhpParser\\Node\\Expr\\FuncCall');
        $node->args = [M::mock('\\PhpParser\\Node\\Arg')];
        $emptyString = new \PhpParser\Node\Scalar\String('');
        $expected = new \PhpParser\Node\Arg($emptyString);

        $actual = $this->ifcTest->callGetCalledFunctionArgument($node, 1);

        $this->assertEquals($expected, $actual);
    }

    public function testGetCalledFunctionArgument_emptyStringOnNegativeIndex()
    {
        $node = M::mock('\\PhpParser\\Node\\Expr\\FuncCall');
        $node->args = [M::mock('\\PhpParser\\Node\\Arg')];
        $emptyString = new \PhpParser\Node\Scalar\String('');
        $expected = new \PhpParser\Node\Arg($emptyString);

        $actual = $this->ifcTest->callGetCalledFunctionArgument($node, -1);

        $this->assertEquals($expected, $actual);
    }

    public function testGetCalledFunctionArgument_correctArg()
    {
        $node = M::mock('\\PhpParser\\Node\\Expr\\FuncCall');
        $expected = M::mock('\\PhpParser\\Node\\Arg');
        $other = M::mock('\\PhpParser\\Node\\Arg');
        $node->args = [$other, $expected, $other];

        $actual = $this->ifcTest->callGetCalledFunctionArgument($node, 1);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Node
     */
    public function testCountCalledFunctionArguments_throwsOnBadNode()
    {
        $node = M::mock('\\PhpParser\\Node');
        $this->ifcTest->callCountCalledFunctionArguments($node);
    }

    public function testGetCalledFunctionArgument_zeroOnNoArgs()
    {
        $node = M::mock('\\PhpParser\\Node\\Expr\\FuncCall');
        $actual = $this->ifcTest->callCountCalledFunctionArguments($node);
        $this->assertEquals(0, $actual);
    }

    public function testGetCalledFunctionArgument_zeroOnEmptyArgs()
    {
        $node = M::mock('\\PhpParser\\Node\\Expr\\FuncCall');
        $node->args = [];
        $actual = $this->ifcTest->callCountCalledFunctionArguments($node);
        $this->assertEquals(0, $actual);
    }

    public function testGetCalledFunctionArgument_numberOfArgs()
    {
        $node = M::mock('\\PhpParser\\Node\\Expr\\FuncCall');
        $node->args = [1, 2, 3];
        $actual = $this->ifcTest->callCountCalledFunctionArguments($node);
        $this->assertEquals(3, $actual);
    }
}

class IsFunctionCallTester
{
    use IsFunctionCallTrait;

    public function callIsFunctionCall($node, $names = '')
    {
        return $this->isFunctionCall($node, $name);
    }

    public function callGetCalledFunctionName(Node $node)
    {
        return $this->getCalledFunctionName($node);
    }

    public function callGetCalledFunctionArgument(Node $node, $index)
    {
        return $this->getCalledFunctionArgument($node, $index);
    }

    public function callcountCalledFunctionArguments(Node $node)
    {
        return $this->countCalledFunctionArguments($node);
    }
}
