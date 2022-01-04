<?php

use eftec\chaosmachineone\ChaosMachineOne;
use eftec\PdoOne;

@set_time_limit(200);

include 'common.php';



echo "<h1>Creating sakila - rental</h1>";



$chaos=new ChaosMachineOne();
$chaos->setDb($db);
$chaos->debugMode=false;

echo "<pre>";
echo $chaos->generateCode('rental');
echo "</pre>";
//die(1);


//$db->truncate('rental');

$chaos->table('rental', 70000)
    ->setDb($db)
    ->field('customer_id', 'int','database')
    ->isnullable(true)
    // inventory_id type mediumint not defined
    ->field('inventory_id', 'int','database')
    ->field('last_update', 'datetime','database','2010-01-01 00:00:00')
    ->isnullable(true)
    ->field('rental_date', 'datetime','database','2010-01-01 00:00:00')
    ->isnullable(true)
    ->field('rental_id', 'int','identity', 0)
    ->field('return_date', 'datetime','database',DateTime::createFromFormat('Y-m-d', '2010-01-01'))
    ->field('staff_id', 'int','database')
    ->isnullable(true)
    ->field('rnd','int','local',0)
    ->field('factor1','decimal','local',1) // the first value to indicate the speed
    ->field('factor2','decimal','local',1) // the last value to indicate the speed
    ->field('factor3','decimal','local',1) // is a tempoeral factor to calculate the initial starting value of factor1
    ->setArrayFromDBTable('array_customer_id','customer','customer_id')
    ->setArrayFromDBTable('array_inventory_id','inventory','inventory_id')
    ->setArrayFromDBTable('array_staff_id','staff','staff_id')
    ->gen('when always then rnd.value=random(1,10)')
    ->gen('when always set customer_id.value=randomarray("array_customer_id")')
    ->gen('when always set inventory_id.value=randomarray("array_inventory_id")')
    ->gen('when always set staff_id.value=randomarray("array_staff_id")')
    ->gen('when always set last_update.speed=random(360,8640)')
    ->gen('when always set factor3.value=rental_date.year-2010*700') // more rentals every new year
    ->gen('when always set factor3.value=factor3.value+sin(0,0,100,1000,rental_date.year)')  // however, values could fluctuate.
    ->gen('when always set factor1.value=11400-factor3.value')
    ->gen('when rental_date.weekday>=6 set factor1.value=factor1.value/3') // more rental during the weekends
    ->gen('when rental_date.month<=2 or rental_date.month=12 set factor1.value=factor1.value/2') // more rental during jan,feb or dec
    ->gen('when rental_date.month=6 set factor1.value=factor1.value*2') // worse month
    ->gen('when rental_date.month=7 set factor1.value=factor1.value*3') // worse month
    ->gen('when rental_date.day>25 set factor1.value=factor1.value/2') // more rentals during end of the month.
    ->gen('when always set factor2.value=factor1.value*5 and rental_date.speed=random(factor1.value,factor2.value)')
    ->gen('when always set return_date.value=rental_date.value')
    ->gen('when always then return_date.add= rnd+"d"')
    ->gen('when rental_date.year=2020 and rental_date.month=12 and rental_date.day>29 then end()')
    ->setInsert(true)
    ->showTable(['customer_id','inventory_id','last_update','rental_date','rental_id','return_date','staff_id'],true)
    ->run(true);
/*
echo "<pre>";
var_dump($chaos);
echo "</pre>";
*/