<?php

use eftec\chaosmachineone\ChaosMachineOne;
use eftec\PdoOne;

@set_time_limit(200);

include 'common.php';




echo "<h1>Creating sakila - staff</h1>";



$chaos=new ChaosMachineOne();
$chaos->setDb($db);
$chaos->debugMode=false;
/*
echo "<pre>";
echo $chaos->generateCode('staff');
echo "</pre>";
die(1);
*/
try {
    $db->truncate('staff');    
} catch(Exception $ex) {
    //var_dump($ex);
    echo "unable truncate table staff<br>";
} 


$chaos->table('staff', 120)
    ->setDb($db)
    ->field('active', 'int','database')
    ->isnullable(true)
    ->field('address_id', 'int','database')
    ->isnullable(true)
    ->field('email', 'string','database','',0,50)
    ->field('first_name', 'string','database','',0,45)
    ->isnullable(true)
    ->field('last_name', 'string','database','',0,45)
    ->isnullable(true)
    ->field('last_update', 'datetime','database',new DateTime('2010-01-01'))
    ->isnullable(true)
    ->field('password', 'string','database','',0,40)
    // picture type blob not defined
    ->field('staff_id', 'int','identity', 0)
    ->field('store_id', 'int','database')
    ->isnullable(true)
    ->field('username', 'string','database','',0,16)
    ->isnullable(true)
    ->setArrayFromDBTable('array_address_id','address','address_id')
    ->setArrayFromDBTable('array_store_id','store','store_id')
    ->setArray('array_first_name',array_merge(PersonContainer::$firstNameMale,PersonContainer::$firstNameFemale))
    ->setArray('array_last_name',PersonContainer::$lastName)
    ->gen('when always set address_id.value=randomarray("array_address_id")')
    ->gen('when always set store_id.value=randomarray("array_store_id")')
    ->gen('when always set active.value=random(0,1)')
    ->gen('when always set first_name.value=randomarray("array_first_name")')
    ->gen('when always set last_name.value=randomarray("array_last_name")')
    ->gen('when always set email.value="{{first_name}}.{{last_name}}@sakila.com"')
    ->gen('when always set last_update.speed=random(3600,86400)')
    ->gen('when always set password.value=randomtext("","",false,0,40)')
    // picture not defined for type blob
    ->gen('when always set username.value="{{first_name}}.{{last_name}}"')
    ->setInsert(true)
    ->showTable(['active','address_id','email','first_name','last_name','last_update','password','staff_id','store_id','username'],true)
    ->run(true);

