<?php

use eftec\chaosmachineone\ChaosMachineOne;
use eftec\PdoOne;

@set_time_limit(200);

include 'common.php';




echo "<h1>Creating sakila - payment</h1>";

$db->truncate('payment');

$chaos=new ChaosMachineOne();
$chaos->setDb($db);

/*echo "<pre>";
echo $chaos->generateCode('payment');
echo "</pre>";
die(1);
*/

$chaos->table('payment', 10000)
    ->setDb($db)
    ->field('amount', 'decimal','database')
    ->isnullable(true)
    ->field('customer_id', 'int','database')
    ->isnullable(true)
    ->field('last_update', 'datetime','database',new DateTime('2010-01-01'))
    ->isnullable(true)
    ->field('payment_date', 'datetime','database',new DateTime('2010-01-01'))
    ->isnullable(true)
    ->field('payment_id', 'int','identity', 0)
    ->field('rental_id', 'int','database')
    ->field('staff_id', 'int','database')
    ->isnullable(true)
    ->setArrayFromDBTable('array_customer_id','customer','customer_id')
    ->setArrayFromDBTable('array_rental_id','rental','rental_id')
    ->setArrayFromDBTable('array_staff_id','staff','staff_id',[25,10,30,10,25])
    ->gen('when always set customer_id.value=randomarray("array_customer_id")')
    ->gen('when always set rental_id.value=randomarray("array_rental_id")')
    ->gen('when always set staff_id.value=randomarray("array_staff_id")')
    ->gen('when always set amount.value=random(1,100,0.1,10,10)')
    ->gen('when last_update.weekday>=6 set last_update.speed=random(600,1000) else last_update.speed=random(3600,10000)')
    ->gen('when last_update.weekday>=6 set payment_date.speed=random(600,1000) else payment_date.speed=random(3600,10000)')
    ->showTable(['amount','customer_id','last_update','payment_date'],true)
    ->setInsert(true)
    ->run(true);

