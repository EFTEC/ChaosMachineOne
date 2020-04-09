<?php

use eftec\chaosmachineone\ChaosMachineOne;


use eftec\minilang\MiniLang;
include "../vendor/autoload.php";

$chaos=new ChaosMachineOne();
$chaos->setDictionary('_index',100);
                                   


// skip next day
// skip next workingday
// skip next weekend
// skip next monday
// skip add 8 hours
// skip next month (first month)


$chaos->table('table',1000)
	->field('time','datetime','database',$chaos->now(),0,200)
    ->field('day','string','local','',0,20)
	->gen('when _index=0 then time.speed=3600') // speed is an hour
	->gen('when time.weekday=5 and time.hour>17 then time.skip="monday" and time.add="8h"') // we skip to the next monday
	->gen('when time.weekday>=1 and time.weekday<=5 then time.speed=random(1000,3600)') 
    ->gen('when always() then day.value=time.weekday')
	->showTable(['time','day'],true)
	->stat()
    ->run();
	
	
	
	
	