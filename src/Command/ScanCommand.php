<?php

namespace Psecio\Parse\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Psecio\Parse\RuleFactory;
use Psecio\Parse\FileIterator;
use Psecio\Parse\CallbackVisitor;
use Psecio\Parse\Scanner;
use Psecio\Parse\File;
use Psecio\Parse\Conf\ConfFactory;
use Psecio\Parse\Subscriber\ExitCodeCatcher;
use Psecio\Parse\Subscriber\Console\Dots;
use Psecio\Parse\Subscriber\Console\Progress;
use Psecio\Parse\Subscriber\Console\Lines;
use Psecio\Parse\Subscriber\Console\Debug;
use Psecio\Parse\Subscriber\Console\Report;
use Psecio\Parse\Subscriber\Xml;
use Psecio\Parse\Event\Events;
use Psecio\Parse\Event\MessageEvent;
use RuntimeException;
use SplFileInfo;

/**
 * The main command, scan paths for possible security issues
 */
class ScanCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('scan')
            ->setDescription('Scans paths for possible security issues')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL|InputArgument::IS_ARRAY,
                'Path to scan'
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Output format (progress, dots, lines, debug or xml)'
            )
            ->addOption(
                'ignore-paths',
                'i',
                InputOption::VALUE_REQUIRED,
                'Comma-separated list of paths to ignore'
            )
            ->addOption(
                'extensions',
                'x',
                InputOption::VALUE_REQUIRED,
                'Comma-separated list of file extensions to parse (default: php,phps,phtml,php5)'
            )
            ->addOption(
                'whitelist-rules',
                'w',
                InputOption::VALUE_REQUIRED,
                'Comma-separated list of rules to whitelist'
            )
            ->addOption(
                'blacklist-rules',
                'b',
                InputOption::VALUE_REQUIRED,
                'Comma-separated list of rules to blacklist'
            )
            ->addOption(
                'disable-annotations',
                'd',
                InputOption::VALUE_NONE,
                'Skip all annotation-based rule toggles'
            )
            ->addOption(
                'configuration',
                'c',
                InputOption::VALUE_REQUIRED,
                'Read configuration from file'
            )
            ->addOption(
                'no-configuration',
                null,
                InputOption::VALUE_NONE,
                'Ignore default configuration file'
            )
            ->setHelp(
                "Scan paths for possible security issues:\n\n  <info>psecio-parse %command.name% /path/to/src</info>\n"
            );
    }

    /**
     * Execute the "scan" command
     *
     * @param  InputInterface   $input Input object
     * @param  OutputInterface  $output Output object
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conf = (new ConfFactory)->createConf($input, $confFileName);

        $rules = (new RuleFactory($conf->getRuleWhitelist(), $conf->getRuleBlacklist()))->createRuleCollection();

        $files = new FileIterator($conf->getPaths(), $conf->getIgnorePaths(), $conf->getExtensions());

        $dispatcher = new EventDispatcher;

        $exitCode = new ExitCodeCatcher;
        $dispatcher->addSubscriber($exitCode);

        switch ($conf->getFormat()) {
            case 'dots':
            case 'progress':
                $output->writeln("<info>Parse: A PHP Security Scanner</info>\n");
                if ($output->isVeryVerbose()) {
                    $dispatcher->addSubscriber(
                        new Debug($output)
                    );
                } elseif ($output->isVerbose()) {
                    $dispatcher->addSubscriber(
                        new Lines($output)
                    );
                } elseif ('progress' == $conf->getFormat() && $output->isDecorated()) {
                    $dispatcher->addSubscriber(
                        new Progress(new ProgressBar($output, count($files)))
                    );
                } else {
                    $dispatcher->addSubscriber(
                        new Dots($output)
                    );
                }
                $dispatcher->addSubscriber(new Report($output));
                break;
            case 'xml':
                $dispatcher->addSubscriber(new Xml($output));
                break;
            default:
                throw new RuntimeException("Unknown output format '{$conf->getFormat()}'");
        }

        if ($confFileName) {
            $dispatcher->dispatch(Events::DEBUG, new MessageEvent("Reading configurations from $confFileName"));
        }

        $dispatcher->dispatch(Events::DEBUG, new MessageEvent("Using ruleset: $rules"));

        $scanner = new Scanner($dispatcher, new CallbackVisitor($rules));
        $scanner->scan($files);

        return $exitCode->getExitCode();
    }
}
