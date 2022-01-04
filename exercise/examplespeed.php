<?php
use eftec\chaosmachineone\ChaosMachineOne;
use eftec\PdoOne;
use eftec\minilang\MiniLang;
include "../vendor/autoload.php";
include "../lib/en_US/Person.php";
$chaos=new ChaosMachineOne();

$chaos
    ->table('table',10)
    ->field('v1','int','local',1)
    ->field('v2','int','local',1)
    ->gen('when always then v1.speed=2')
    ->gen('when always then v2=v1')

    ->showTable(['v1','v2'])
    ->run();
$idTest=$chaos->getDictionary('v1');
$idtest2=$chaos->getDictionary('v2');
