<?php /** @noinspection ForgottenDebugOutputInspection */


use eftec\chaosmachineone\ChaosMachineOne;
use eftec\minilang\MiniLang;
include "../vendor/autoload.php";


$machine=new ChaosMachineOne();





//  when date.w=2  then random(20,300)

$machine->table('table',1000)
    ->field('field1','int','local',0)
    ->field('field2','int','local',0)
    ->gen('when always then field1=random(1,12,1,1,2,3) and field2.speed=1') /** @see \eftec\chaosmachineone\ChaosMachineOne::random */
    ->showTable(['field2','field1'],true)
    ->run();

	
	
	
	
	