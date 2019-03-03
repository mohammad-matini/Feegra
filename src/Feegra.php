#!/usr/bin/env php
<?php namespace Feegra;

use GetOpt\GetOpt;
use GetOpt\Option;
use GetOpt\Command;
use GetOpt\ArgumentException;
use GetOpt\ArgumentException\Missing;

define('NAME', 'Feegra');
define('VERSION', '0.1');

require_once __DIR__ . '/../vendor/autoload.php';
$configs = include(__DIR__ . '/../config.php');


echo
'
 mmmmmm                 mmm
 #       mmm    mmm   m"   "  m mm   mmm
 #mmmmm #"  #  #"  #  #   mm  #"  " "   #
 #      #""""  #""""  #    #  #     m"""#
 #      "#mm"  "#mm"   "mmm"  #     "mm"#

';


$db = new DB($configs);
if(!$db) exit($db->lastErrorMsg());

$fapi = new FAPI($configs);
if(!$fapi) exit('Could not initialize Facebook SDK');

$getOpt = new GetOpt();

$getOpt->addOptions([
    Option::create(null, 'version', GetOpt::NO_ARGUMENT)
        ->setDescription('Show version information and quit'),
    Option::create('h', 'help', GetOpt::NO_ARGUMENT)
        ->setDescription('Show this help and quit'),
]);


$getOpt->addCommand(new AddCommand($configs, $fapi, $db));
$getOpt->addCommand(new ProcessCommand($configs, $fapi, $db));


try {
    try {
        $getOpt->process();
    } catch (Missing $exception) {
        if (!$getOpt->getOption('help')) {
            throw $exception;
        }
    }
} catch (ArgumentException $exception) {
    file_put_contents('php://stderr', $exception->getMessage() . PHP_EOL);
    echo PHP_EOL . $getOpt->getHelpText();
    exit;
}

if ($getOpt->getOption('version')) {
    echo sprintf('%s: %s' . PHP_EOL, NAME, VERSION);
    exit;
}

$command = $getOpt->getCommand();
if (!$command || $getOpt->getOption('help')) {
    echo $getOpt->getHelpText();
    exit;
}

call_user_func($command->getHandler(), $getOpt);
