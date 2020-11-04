<?php

use Symfony\Component\Dotenv\Dotenv;

include 'vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');