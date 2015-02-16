<?php

namespace Psecio\Parse\Rule\Helper;

use PhpParser\Node;
use PhpParser\Node\Name;
use Mockery as M;

/**
 * Base test for implementing parse based unit tests for traits
 */
class IsBoolLiteralTraitTest extends \PHPUnit_Framework_TestCase
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
        $this->checker = new IsBoolLiteral;
    }

    public function test_notNamedNode_false()
    {
        $node = M::mock('\\PhpParser\\Node');
        $node->name = new \stdClass();

        $result = $this->check($node);

        $this->assertFalse($result);
    }

    public function test_namedNodeTrue_true()
    {
        $node = M::mock('\\PhpParser\\Node');
        $node->name = new Name('true');

        $result = $this->check($node);

        $this->assertTrue($result);
    }

    public function test_namedNodeFalse_true()
    {
        $node = M::mock('\\PhpParser\\Node');
        $node->name = new Name('false');

        $result = $this->check($node);

        $this->assertTrue($result);
    }

    public function test_namedNodeNotTF_false()
    {
        $node = M::mock('\\PhpParser\\Node');
        $node->name = new Name('foo');

        $result = $this->check($node);

        $this->assertFalse($result);
    }

    public function test_nameNodeTF_ignoresCase()
    {
        $values = ['true', 'false', 'TRUE', 'FALSE', 'True', 'False', 'truE', 'falsE'];

        $node = M::mock('\\PhpParser\\Node');

        foreach ($values as $v) {
            $node->name = new Name($v);
            $this->assertTrue($this->check($node));
        }
    }

    public function test_trueNode_nonBoolReturnsTrue()
    {
        $node = M::mock('\\PhpParser\\Node');
        $node->name = new Name('true');

        $this->assertTrue($this->check($node, 1));
        $this->assertTrue($this->check($node, 52));
        $this->assertTrue($this->check($node, 0));
        $this->assertTrue($this->check($node, 'false'));
    }

    public function test_falseNode_intMatchReturnsTrue()
    {
        $node = M::mock('\\PhpParser\\Node');
        $node->name = new Name('false');

        $this->assertTrue($this->check($node, 1));
        $this->assertTrue($this->check($node, 52));
        $this->assertTrue($this->check($node, 0));
        $this->assertTrue($this->check($node, 'true'));
    }

    public function test_trueNode_match()
    {
        $node = M::mock('\\PhpParser\\Node');
        $node->name = new Name('true');

        $this->assertTrue($this->check($node, true));
        $this->assertFalse($this->check($node, false));
    }

    public function test_falseNode_match()
    {
        $node = M::mock('\\PhpParser\\Node');
        $node->name = new Name('false');

        $this->assertTrue($this->check($node, false));
        $this->assertFalse($this->check($node, true));
    }

    /**
     * Call the {@see $checker} method. This is needed because, for some reason,
     * calling an property object as a function fails.
     */
    private function check(Node $node, $value = null)
    {
        $c = $this->checker;
        return $c($node, $value);
    }
}

class IsBoolLiteral
{
    use IsBoolLiteralTrait;

    public function __invoke(Node $node, $value = null)
    {
        return $this->isBoolLiteral($node, $value);
    }
}
