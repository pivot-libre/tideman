<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

// To use composer's autoload
require_once '../vendor/autoload.php';

use PivotLibre\Tideman\Tools\CondorcetChecker;

CondorcetChecker::main();
