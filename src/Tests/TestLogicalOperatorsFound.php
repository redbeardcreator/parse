<?php

namespace Psecio\Parse\Tests;

use Psecio\Parse\TestInterface;
use PhpParser\Node;
use Psecio\Parse\File;
use PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use PhpParser\Node\Expr\BinaryOp\LogicalOr;
use PhpParser\Node\Expr\BinaryOp\LogicalXor;

/**
 * The logical operators AND, OR and XOR should be avoided as they have lower precedence the assignment operator
 */
class TestLogicalOperatorsFound implements TestInterface
{
    use Helper\NameTrait;

    public function getDescription()
    {
        return 'Avoid using AND, OR and XOR (in favor of || and &&) as they may cause subtle precedence bugs';
    }

    public function evaluate(Node $node, File $file)
    {
        if ($node instanceof LogicalAnd || $node instanceof LogicalOr || $node instanceof LogicalXor) {
            return false;
        }
        return true;
    }
}
