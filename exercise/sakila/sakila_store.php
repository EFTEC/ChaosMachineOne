<?php

use eftec\chaosmachineone\ChaosMachineOne;
use eftec\PdoOne;

@set_time_limit(200);

include 'common.php';



echo "<h1>Creating sakila - store</h1>";



$chaos=new ChaosMachineOne();
$chaos->setDb($db);
$chaos->debugMode=false;
/*
echo "<pre>";
echo $chaos->generateCode('store');
echo "</pre>";
die(1);
*/
try {
    $db->truncate('store');    
} catch(Exception $ex) {
    //var_dump($ex);
    echo "unable truncate table store<br>";
}


$chaos->table('store', 30)
    ->setDb($db)
    ->field('address_id', 'int','database')
    ->isnullable(true)
    ->field('last_update', 'datetime','database',new DateTime('now'))
    ->isnullable(true)
    ->field('manager_staff_id', 'int','database')
    ->isnullable(true)
    ->field('store_id', 'int','identity', 0)
    ->setArrayFromDBTable('array_address_id','address','address_id')
    ->setArrayFromDBTable('array_manager_staff_id','staff','staff_id')
    ->gen('when always set address_id.value=randomarray("array_address_id")')
    ->gen('when always set manager_staff_id.value=randomarray("array_manager_staff_id")')
    ->gen('when always set last_update.speed=random(3600,86400)')
    ->setInsert(true)
    ->showTable(['address_id','last_update','manager_staff_id','store_id'],true)
    ->run(true);

