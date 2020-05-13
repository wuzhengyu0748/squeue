<?php

require './Core/Driver.php';
require './Structure/JobPool.php';
require './Queue.php';


$q = new \SQueue\Queue('127.0.0.1', '6379');
$q->add('testtopic', ['order'=>10021,'price'=>22.1]);