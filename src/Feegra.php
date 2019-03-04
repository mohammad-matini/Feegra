#!/usr/bin/env php
<?php namespace Feegra;


// Copyright (C) 2019 Mohammad Matini

// Author: Mohammad Matini <mohammad.matini@outlook.com>
// Maintainer: Mohammad Matini <mohammad.matini@outlook.com>

// This file is part of Feegra.

// Feegra is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// Feegra is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with Feegra.  If not, see <https://www.gnu.org/licenses/>.


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

 Welcome to Feegra, the Facebook Feed Grabber.
 ---------------------------------------------

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


$getOpt->addCommand(new InitCommand($configs, $fapi, $db));
$getOpt->addCommand(new AddCommand($configs, $fapi, $db));
$getOpt->addCommand(new ProcessCommand($configs, $fapi, $db));
$getOpt->addCommand(new ListCommand($configs, $fapi, $db));


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
