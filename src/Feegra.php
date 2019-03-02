#!/usr/bin/env php
<?php
namespace Feegra;

require_once __DIR__ . '/../vendor/autoload.php';

echo
    '
 mmmmmm                 mmm
 #       mmm    mmm   m"   "  m mm   mmm
 #mmmmm #"  #  #"  #  #   mm  #"  " "   #
 #      #""""  #""""  #    #  #     m"""#
 #      "#mm"  "#mm"   "mmm"  #     "mm"#

';
$db = new DB();
if(!$db) exit($db->lastErrorMsg());
$db->close();
