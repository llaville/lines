<?php

declare(strict_types=1);

use PhpParser\ParserFactory;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\Lines\Command\FeaturesCommand;
use TomasVotruba\Lines\Command\MeasureCommand;
use TomasVotruba\Lines\FeatureCounter\Analyzer\FeatureCounterAnalyzer;
use TomasVotruba\Lines\Finder\ProjectFilesFinder;

/*
use TomasVotruba\Lines\DependencyInjection\ContainerFactory;

if (file_exists(__DIR__ . '/../vendor/scoper-autoload.php')) {
    // A. build downgraded package
    require_once __DIR__ . '/../vendor/scoper-autoload.php';
} elseif (file_exists(__DIR__ . '/../../../../vendor/autoload.php')) {
    // B. dev repository
    require_once __DIR__ . '/../../../../vendor/autoload.php';
} else {
    // C. local repository
    require_once __DIR__ . '/../vendor/autoload.php';
}
*/
require_once __DIR__ . '/../vendor/autoload.php';

//$containerFactory = new ContainerFactory();
//$container = $containerFactory->create();

//$application = $container->make(Application::class);
$application = new Application();

$output = new ConsoleOutput();

$input = new \Symfony\Component\Console\Input\ArgvInput();

if (true === $input->hasParameterOption(['--no-ansi'], true)) {
    $colors = false;
} else {
    $colors = true;
}

$output->setDecorated($colors);

$symfonyStyle = new SymfonyStyle($input, $output);

$featuresCommand = new FeaturesCommand(
    $symfonyStyle,
    new ProjectFilesFinder(),
    new FeatureCounterAnalyzer(new \TomasVotruba\Lines\FeatureCounter\ValueObject\FeatureCollector()),
    new \TomasVotruba\Lines\FeatureCounter\ResultPrinter($symfonyStyle),
    new \TomasVotruba\Lines\Console\OutputFormatter\JsonOutputFormatter()
);

$phpParserFactory = new ParserFactory();

$measureCommand = new MeasureCommand(
    new \TomasVotruba\Lines\Finder\PhpFilesFinder(),
    new \TomasVotruba\Lines\Analyser(
        $phpParserFactory->createForHostVersion(),
        new \SebastianBergmann\LinesOfCode\Counter()
    ),
    new \TomasVotruba\Lines\Console\OutputFormatter\JsonOutputFormatter(),
    new \TomasVotruba\Lines\Console\OutputFormatter\TextOutputFormatter(
        new \TomasVotruba\Lines\Console\ViewRenderer(),
        $symfonyStyle
    ),
    $symfonyStyle
);

$application->addCommands([$featuresCommand, $measureCommand]);
exit($application->run());
