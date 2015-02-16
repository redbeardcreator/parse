<?php

namespace Psecio\Parse\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Output\OutputInterface;
use org\bovigo\vfs\vfsStream;

/**
 * @covers \Psecio\Parse\Command\ScanCommand
 */
class ScanCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string Name of empty php file used in scanning
     */
    private $filename;
    private $root;

    const DOTS_REGEX = "/Parse.*\n\n\.+\n\nOK.*\n$/";

    public function setUp()
    {
        $this->root = vfsStream::setup('exampleDir');
        $this->filename = vfsStream::url('exampleDir') . '/test.php';
        touch($this->filename);
    }

    public function testDottedOutput()
    {
        $this->assertRegExp(
            self::DOTS_REGEX,
            $this->executeCommand(['--format' => 'dots']),
            'Using --format=dots should generate output'
        );
    }

    public function testProgressOutput()
    {
        $this->assertRegExp(
            '/\[\=+\]/',
            $this->executeCommand(['--format' => 'progress'], ['decorated' => true]),
            'Using --format=progress should use the progressbar'
        );
    }

    public function testDotsOutputIfNotDecorated()
    {
        $this->assertRegExp(
            self::DOTS_REGEX,
            $this->executeCommand(['--format' => 'progress'], ['decorated' => false]),
            'Using --format=progress should use the dots if not decorated'
        );
    }

    public function testVerboseOutput()
    {
        $this->assertRegExp(
            '/\[PARSE\]/',
            $this->executeCommand([], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]),
            'Using -v should generate verbose output'
        );
    }

    public function testVeryVerboseOutput()
    {
        $this->assertRegExp(
            '/\[DEBUG\]/',
            $this->executeCommand([], ['verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE]),
            'Using -vv should generate debug output'
        );
    }

    public function testXmlOutput()
    {
        $this->assertRegExp(
            '/^<\?xml version="1.0" encoding="UTF-8"\?>/',
            $this->executeCommand(['--format' => 'xml']),
            'Using --format=xml should generate a valid xml doctype'
        );
    }

    public function testExceptionOnUnknownFormat()
    {
        $this->setExpectedException('RuntimeException');
        $this->executeCommand(['--format' => 'this-format-does-not-exist']);
    }

    public function testParseCsv()
    {
        $this->assertSame(
            ['php', 'phps'],
            (new ScanCommand)->parseCsv('php,phps'),
            'parsing comma separated values should work'
        );
        $this->assertSame(
            ['php', 'phps'],
            array_values((new ScanCommand)->parseCsv('php,,phps')),
            'multiple commas should be skipped while parsing csv'
        );
        $this->assertSame(
            [],
            (new ScanCommand)->parseCsv(''),
            'parsing an empty string should return an empty array'
        );
    }

    private function executeCommand(array $input, array $options = array())
    {
        $application = new Application;
        $application->add(new ScanCommand);
        $tester = new CommandTester($application->find('scan'));
        $input['command'] = 'scan';
        $input['path'] = [$this->filename];
        $tester->execute($input, $options);

        return $tester->getDisplay();
    }
}
