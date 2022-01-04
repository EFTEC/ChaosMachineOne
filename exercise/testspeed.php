<?php
include '../vendor/autoload.php';

$chaosMachineOne=new \eftec\chaosmachineone\ChaosMachineOne();

$chaosMachineOne
    ->table('table',10)
    ->field('v1','datetime','local',DateTime::createFromFormat('Y-m-d h:i:s', '2010-01-01 00:00:00'))
    ->field('v2','datetime','local',DateTime::createFromFormat('Y-m-d h:i:s', '2010-01-01 00:00:00'))
    ->field('rnd','int','local',0)
    ->gen('when always then rnd.value=random(1,5)')
    ->gen('when always then v1.speed=2')
    ->gen('when always then v2.value=v1.value')
    ->gen('when always then v2.add= rnd+"d"')
    ->gen('when _index>3 and _index<8 then omit()')
    ->showTable(['v1','v2','_index'])
    ->run();
$idTest=$chaosMachineOne->getDictionary('v1');
$idtest2=$chaosMachineOne->getDictionary('v2');

