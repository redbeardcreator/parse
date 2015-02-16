<?php

namespace Psecio\Parse\Rule;

class ImportRequestVariablesTest extends RuleTestCase
{
    public function parseSampleProvider()
    {
        return [
            ['import_request_variables();', false],
            ['something_else();', true],
        ];
    }

    protected function buildTest()
    {
        return new ImportRequestVariables();
    }
}
