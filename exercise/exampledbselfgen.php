<?php

use eftec\chaosmachineone\ChaosMachineOne;
use eftec\PdoOne;

@set_time_limit(200);

include "../vendor/autoload.php";
include "../lib/en_US/Person.php";
include "../lib/en_US/Products.php";


$db=new PdoOne("mysql","localhost","root","abc.123","chaosdb");
//$db=new PdoOne("sqlsrv","localhost","sa","abc.123","BASE_LARI");
$db->open();
$db->logLevel=3;
$chaos = new ChaosMachineOne();
$chaos->setDb($db);
//echo $chaos->generateCode('auditorias');

//die(1);
//echo date("U",strtotime('2012-01-18 11:45:00'));
//var_dump($chaos->now());
//die(1);
//echo "<textarea>";
//$chaos->table('payment', 50000)
$chaos->table('payment', 'products')
	->setDb($db)
	->field('amount', 'decimal','database')
	->field('customer_id', 'int','database')
	->field('last_update', 'datetime','database',$chaos->date('2011-01-01 00:00'))
	->field('payment_date', 'datetime','database',$chaos->date('2011-01-01 00:00'))
	->field('payment_id', 'int','identity', 0)
	->field('rental_id', 'int','database')
	->field('staff_id', 'int','database')
	->setArrayFromDBTable('arr_customer_id','customer','customer_id')
	->setArrayFromDBTable('arr_rental_id','rental','rental_id')
	->setArrayFromDBTable('arr_staff_id','staff','staff_id')
	->gen('when _index=0 then payment_date.speed=3600') // 1 hour (initial speed)
	->gen('when _index=0 then last_update.speed=3600') // 1 hour (initial speed)
	->gen('when always set amount.value=random(1,100)')
	->gen('when always set customer_id.value=randomarray("arr_customer_id")')
	->gen('when always set rental_id.value=randomarray("arr_rental_id")')
	->gen('when always set staff_id.value=randomarray("arr_staff_id")')
	->gen('when always set payment_date.speed=random(1200,28800)')
	->gen('when always set last_update.speed=random(1200,28800)')
	//->insert(true,'%s,')
	->showTable(['amount', 'last_update', 'payment_date','customer_id'])
	//->show()
	->run()
	->stat();
	
//echo "</textarea>";