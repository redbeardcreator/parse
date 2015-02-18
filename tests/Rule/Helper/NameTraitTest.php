<?php

namespace Psecio\Parse\Rule\Helper;

use PhpParser\Node;
use PhpParser\Node\Name;
use Mockery as M;

/**
 * Base test for implementing parse based unit tests for traits
 */
class NameTraitTest extends \PHPUnit_Framework_TestCase
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
        $this->checker = new GetName;
    }

    public function test_getName()
    {
        $expected = 'GetName';
        $result = $this->check();

        $this->assertEquals($expected, $result);
    }

    /**
     * Call the {@see $checker} method. This is needed because, for some reason,
     * calling an property object as a function fails.
     */
    private function check()
    {
        $c = $this->checker;
        return $c();
    }
}

class GetName
{
    use NameTrait;

    public function __invoke()
    {
        return $this->getName();
    }
}
