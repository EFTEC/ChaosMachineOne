<?php

use eftec\chaosmachineone\ChaosMachineOne;
use eftec\PdoOne;

include 'common.php';



echo "<h1>Creating sakila - film_actor</h1>";

$db->truncate('film_actor');

$chaos=new ChaosMachineOne();
//$chaos->debugMode=true;
$chaos->table('film_actor',10000)
    ->setDb($db)
    ->field('actor_id','int','database',1)
    ->field('film_id','int','database',1)
    ->setArrayFromDBQuery('actor_arr','select actor_id from actor')
    ->setArrayFromDBQuery('film_arr','select film_id from film')
    ->setArrayFromDBQuery('customer_arr','select customer_id from customer')
    ->gen('when always set actor_id.value=randomarray("actor_arr")') /** @see \eftec\chaosmachineone\ChaosMachineOne::randomarray */
    ->gen('when always set film_id.value=randomarray("film_arr")')
    ->setInsert(true)
    ->run(true)
    ->showTable(['actor_id','film_id'],true)
    ->stat()
    ->show();

    
