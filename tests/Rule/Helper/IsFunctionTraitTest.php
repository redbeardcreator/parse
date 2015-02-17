<?php

namespace Psecio\Parse\Rule\Helper;

use PhpParser\Node;
use PhpParser\Node\Name;
use Mockery as M;

/**
 * Base test for implementing parse based unit tests for traits
 */
class IsFunctionTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Parser The parser to use to parse samples
     */
    protected $checker;

    /**
     * Set up the parser the same way the Scanner does
     */
    public function setUp()
    {
        $this->checker = new IsFunction;
    }

    public function test_notFuncCall_false()
    {
        $node = M::mock('\\PhpParser\\Node');

        $result = $this->check($node);

        $this->assertFalse($result);
    }

    public function test_noName_true()
    {
        $node = M::mock('\\PhpParser\\Node\\Expr\\FuncCall');

        $result = $this->check($node);

        $this->assertTrue($result);
    }

    public function test_stringNameMatches_true()
    {
        $name = 'someFuncName';
        $node = M::mock('\\PhpParser\\Node\\Expr\\FuncCall');
        $node->name = $name;

        $result = $this->check($node, $name);

        $this->assertTrue($result);
    }

    public function test_stringNameNoMatch_false()
    {
        $name = 'someFuncName';
        $node = M::mock('\\PhpParser\\Node\\Expr\\FuncCall');
        $node->name = $name;

        $result = $this->check($node, 'notSomeFuncName');

        $this->assertFalse($result);
    }

    public function test_varNameMatches_true()
    {
        $name = 'someFuncName';
        $node = M::mock('\\PhpParser\\Node\\Expr\\FuncCall');
        $nameObj = M::mock('\\PhpParser\\Node\\Expr\\Variable');
        $nameObj->name = $name;
        $node->name = $nameObj;

        $result = $this->check($node, $name);

        $this->assertTrue($result);
    }

    public function test_varNameNoMatch_false()
    {
        $name = 'someFuncName';
        $node = M::mock('\\PhpParser\\Node\\Expr\\FuncCall');
        $nameObj = M::mock('\\PhpParser\\Node\\Expr\\Variable');
        $nameObj->name = $name;
        $node->name = $nameObj;

        $result = $this->check($node, 'notTheMomma');

        $this->assertFalse($result);
    }

    public function test_varLookupNamePassedName_true()
    {
        $node = M::mock('\\PhpParser\\Node\\Expr\\FuncCall');
        $nameObj = M::mock('\\PhpParser\\Node\\Expr\\ArrayDimFetch');
        $node->name = $nameObj;

        $result = $this->check($node, 'someFuncName');

        $this->assertTrue($result);
    }

    public function test_varLookupNamePassedNameNull_true()
    {
        $node = M::mock('\\PhpParser\\Node\\Expr\\FuncCall');
        $nameObj = M::mock('\\PhpParser\\Node\\Expr\\ArrayDimFetch');
        $node->name = $nameObj;

        $result = $this->check($node);

        $this->assertTrue($result);
    }

    /**
     * Call the {@see $checker} method. This is needed because, for some reason,
     * calling an property object as a function fails.
     */
    private function check(Node $node, $name = null)
    {
        $c = $this->checker;
        return $c($node, $name);
    }
}

class IsFunction
{
    use IsFunctionTrait;

    public function __invoke(Node $node, $name = null)
    {
        return $this->isFunction($node, $name);
    }
}
