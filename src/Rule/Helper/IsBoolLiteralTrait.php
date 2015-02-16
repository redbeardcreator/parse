<?php

namespace Psecio\Parse\Rule\Helper;

use \PhpParser\Node;

/**
 * Helper to evaluate if node is a boolean literal
 */
trait IsBoolLiteralTrait
{
    /**
     * Determine if $node is a boolean literal, optionally testing for a specific value
     *
     * If $value is true or false, check if $node is specifically $value.
     *
     * @param  Node $node
     * @param  bool|null $value Value to check for. Don't check if null.
     * @return boolean
     */
    protected function isBoolLiteral(Node $node, $value = null)
    {
        if (!$node->name instanceof \PhpParser\Node\Name) {
            return false;
        }

        $name = strtolower($node->name);
        if ($name !== 'true' && $name !== 'false') {
            return false;
        }

        if (!is_bool($value)) {
            return true;
        }

        if ($value) {
            return $name === 'true';
        }

        return $name === 'false';
    }
}
