<?php

use eftec\chaosmachineone\ChaosMachineOne;
use eftec\PdoOne;

@set_time_limit(200);

include "../vendor/autoload.php";
include "../lib/en_US/Person.php";
include "../lib/en_US/Products.php";

$sqlCreateCustomers="CREATE TABLE `customers` (
  `idcustomer` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL,
  `datecreation` TIMESTAMP NULL,
  PRIMARY KEY (`idcustomer`))";

$sqlCreateProducts="CREATE TABLE `products` (
  `idproduct` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL,
  `price` DECIMAL(9,2) NULL,
  PRIMARY KEY (`idproduct`))";

$sqlCreateSales="CREATE TABLE `sales` (
  `idsales` INT NOT NULL AUTO_INCREMENT,
  `idproduct` INT NULL,
  `idcustomer` INT NULL,
  `date` TIMESTAMP NULL,
  `amount` INT NULL,
  PRIMARY KEY (`idsales`))";

$db=new PdoOne("mysql","localhost","root","abc.123","chaosdb");
$db->open();
$db->logLevel=3;
$customers=false;
$products=false;
$sales=true;
try {
	$db->runRawQuery($sqlCreateCustomers);
	$customers=true;
} catch(Exception $ex) {
	echo "Warning: unable to create table customers or table already exists<br>";
}
try {
	$db->runRawQuery($sqlCreateProducts);
	$products=true;
} catch(Exception $ex) {
	echo "Warning: unable to create table products or table already exists<br>";
}
try {
	$db->runRawQuery($sqlCreateSales);
	$sales=true;
} catch(Exception $ex) {
	echo "Warning: unable to create table sales or table already exists<br>";
}
if($customers) {
	$chaos = new ChaosMachineOne();
	$chaos->debugMode=true;
	$chaos->table('customers', 1000)
		->setDb($db)
		->field('fixedid','int','local',5)
		->field('idcustomer', 'int','identity', 0, 0, 1000)
		->field('name', 'string', 'database', '', 0, 45)
			->retry()
		->field('datecreation', 'datetime', 'database', $chaos->now())
		//->setArray('namemale', PersonContainer::$firstNameMale)
		//->setArray('lastname', PersonContainer::$lastName)
		//->setArrayFromDBTable('namemale','sakila.actor','first_name')
		->setArrayFromDBQuery('namemale','select first_name from sakila.actor')
		->setArrayFromDBQuery('lastname','select last_name from sakila.actor where actor_id={{fixedid}}',[1])
		//->setArrayFromDBQuery('lastname','select last_name from sakila.actor where actor_id>?',[1],['i',1])
		->setArray('namefemale', PersonContainer::$firstNameFemale)
		->setFormat('fullnameformat', ['{{namemale}} {{lastname}}', '{{namefemale}} {{lastname}}'])
		->gen('when always set datecreation.speed=random(5000,86400)')
		->gen('when always set name.value=randomformat("fullnameformat")')
		->run(true)
		//->insert(true)
		->stat()
		->show();
	die(1);
}
if($products) {
	$chaos = new ChaosMachineOne();
	$chaos->table('products', count(Products::$softDrink))
		->setDb($db)
		->field('idproduct', 'int', 'identity', 0, 0, 1000)
		->field('name', 'string', 'database', '', 0, 45)
		->field('price', 'decimal', 'database', 2, 0, 100)
		->setArray('productname', Products::$softDrink)
		->gen('when always set price.value=random(0.5,20,0.1)')
		->gen('when always set name.value=arrayindex("productname")')
		->insert(true)
		->stat();
		//->show(['name', 'price']);
	//->insert();
}
if($sales || 1==1 ) {
	$countProducts=count(Products::$softDrink);
	$chaos = new ChaosMachineOne();
	$chaos->table('sales', 'products')
		->setDb($db)
		->field('idsales', 'int', 'identity', 0)
		->field('idproduct', 'int', 'database', 1)
		->field('idcustomer', 'int', 'database', 1)
		->field('amount', 'int', 'database', 1, 1, 100)->isNullable(true)
		->field('date', 'datetime', 'database', $chaos->now())
		->gen('when date.weekday>=1 and date.weekday<=5 then date.speed=random(5000,50000)') // workingdays are slow
		->gen('when date.weekday>=6  then date.speed=random(3000,10000)') // the weekend sells more
		->gen('when date.hour>18 then date.skip="day" and date.add="8h"') // if night then we jump to the next day (8am), i.e  
		->gen('when always then idproduct.value=random(1,$countProducts) 
		and idcustomer.value=random(1,1000) and amount.value=random(1,10)')
		->gen('when always set amount.value=randomprop(null,amount,50,50)') // @see \eftec\chaosmachineone\ChaosMachineOne::randomprop
		->showTable(['idproduct','idcustomer','amount','date','origin_name'])
		->run()
		//->insert(true)
		->stat();
}