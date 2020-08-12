<?php

use eftec\chaosmachineone\ChaosMachineOne;
use eftec\PdoOne;

@set_time_limit(200);

include 'common.php';



echo "<h1>Creating sakila - rental</h1>";



$chaos=new ChaosMachineOne();
$chaos->setDb($db);

echo "<pre>";
echo $chaos->generateCode('rental');
echo "</pre>";
//die(1);


$db->truncate('rental');

$chaos->table('rental', 1000)
    ->setDb($db)
    ->field('customer_id', 'int','database')
    ->isnullable(true)
    // inventory_id type mediumint not defined
    ->field('last_update', 'datetime','database',new DateTime('now'))
    ->isnullable(true)
    ->field('rental_date', 'datetime','database',new DateTime('now'))
    ->isnullable(true)
    ->field('rental_id', 'int','identity', 0)
    ->field('return_date', 'datetime','database',new DateTime('now'))
    ->field('staff_id', 'int','database')
    ->isnullable(true)
    ->setArrayFromDBTable('array_customer_id','customer','customer_id')
    ->setArrayFromDBTable('array_inventory_id','inventory','inventory_id')
    ->setArrayFromDBTable('array_staff_id','staff','staff_id')
    ->gen('when always set customer_id.value=randomarray("array_customer_id")')
    ->gen('when always set inventory_id.value=randomarray("array_inventory_id")')
    ->gen('when always set staff_id.value=randomarray("array_staff_id")')
    ->gen('when always set last_update.speed=random(3600,86400)')
    ->gen('when always set rental_date.speed=random(3600,86400)')
    ->gen('when always set return_date.speed=random(3600,86400)')
    ->setInsert(true)
    ->run(true);

