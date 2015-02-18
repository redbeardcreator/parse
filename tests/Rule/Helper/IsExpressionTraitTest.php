<?php

namespace Psecio\Parse\Rule\Helper;

use PhpParser\Node;
use Mockery as M;

class IsExpressionTraitTest extends \PHPUnit_Framework_TestCase
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
        $this->checker = new IsExpression;
    }

    public function test_normalNodeName()
    {
        $name = 'PostInc';
        $node = M::mock('\\PhpParser\\Node\\Expr\\' . $name);
        $this->assertTrue($this->check($node, $name));
    }

    public function test_underScoreNodeName()
    {
        $name = 'Isset';
        $node = M::mock('\\PhpParser\\Node\\Expr\\' . $name . '_');
        $this->assertTrue($this->check($node, $name));
    }

    public function test_noMatch()
    {
        $name = 'PreInc';
        $node = M::mock('\\PhpParser\\Node\\Expr\\PostInc');
        $this->assertFalse($this->check($node, $name));
    }

    /**
     * Call the {@see $checker} method. This is needed because, for some reason,
     * calling an property object as a function fails.
     */
    private function check(Node $node, $name)
    {
        $c = $this->checker;
        return $c($node, $name);
    }
}

class IsExpression
{
    use IsExpressionTrait;

    public function __invoke(Node $node, $name)
    {
        return $this->isExpression($node, $name);
    }
}
