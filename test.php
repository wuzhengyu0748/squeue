<?php

require './Component/Driver.php';
require './Component/JobPool.php';
require './Component/DelayBucket.php';
require './Queue.php';


$q = new \SQueue\Queue('127.0.0.1', '6379');
//$q->startManager();
//$q->add();
$q->add('testtopic', ['order'=>10021,'price'=>22.1]);