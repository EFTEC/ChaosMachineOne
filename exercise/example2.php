<?php

use eftec\chaosmachineone\ChaosMachineOne;


use eftec\minilang\MiniLang;
include "../vendor/autoload.php";

$chaos=new ChaosMachineOne();
$chaos->values['_index']=100;
                                   




$chaos->table('table',100)
	->field('idtable','int','database',$chaos->random(0,200),0,200)
	//->table('customertable','indexCol,Name','customer','indexCol','condition=?',[]) // it stores the minimum and maximum value of indexCol
	//->gen('set idcustomer.value=randomarray("customers","idcustomer")')
	//->gen('set idcustomer.value=randomfind("customertable",'idcustomer'))
	//->gen('when _index<200 then idtable.value=ramp(0,100,1000,10)')
	//->gen('when _index<40 then idtable.accel=1')
	//->gen('when _index<60 then idtable.accel=-1')
	//->gen('when _index<100 then idtable.accel=-1')
	//->gen('when _index<200 then idtable.value=parabola(50,2500,-1,1,1)')
	//->gen('when _index<200 then idtable.value=randomprop(1,2,3,30,50,20)')
	//->gen('when _index<200 then idtable.add=sin(0,0,10,30)')
	//->gen('when _index<200 then idtable.value=sin(0,0,10,1)')
	//->gen('when _index<200 then idtable.value=log(0,0,100)')
	//->gen('when _index<200 then idtable.value=exp(0,0,10)')
	//->gen('when _index<200 then idtable.value=ramp(0,100,10,1000)')
	//->gen('when _index<=200 then idtable.value=exp(0,500,10)') 
	//	->gen('when _index<=360 then idtable.value=bell(50,0,1,100)')  
	//->gen('when _index<=360 then idtable.value=atan(50,0,20,10) and idtable.valueabs')
	->gen('when _index<=360 then idtable.value=random(0,200,1,10,80,10)')
	//and idtable.add=random(-2,2) and idtable.add=ramp(0,360,0,30)
	//and idtable.add=randomprop(0,3,80,10)') 
	//->gen('when _index=201 then idtable.speed=0 and idtable.accel=0 and idtable.add=random(-100,100)') 
	//->gen('when _index>200 then idtable.add=random(-100,100)')  
	->show(['idtable']);
	
	
	
	
	