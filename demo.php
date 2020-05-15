<?php

require './vendor/autoload.php';

$p = new \Squeue\Squeue('127.0.0.1','6379');

$p->start();