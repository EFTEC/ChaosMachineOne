<?php

use eftec\chaosmachineone\ChaosMachineOne;
use eftec\DaoOne;

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

$db=new DaoOne("localhost","root","abc.123","chaosdb");
$db->open();
$customers=false;
$products=false;
$sales=false;
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
		->field('idcustomer', 'int', 'identity', 0, 0, 1000)
		->field('name', 'string', 'database', '', 0, 45)
		->field('datecreation', 'datetime', 'database', $chaos->now())
		->setArray('namemale', PersonContainer::$firstNameMale)
		->setArray('lastname', PersonContainer::$lastName)
		->setArray('namefemale', PersonContainer::$firstNameFemale)
		->setFormat('fullnameformat', ['{{namemale}} {{lastname}}', '{{namefemale}} {{lastname}}'])
		->gen('when always set datecreation.speed=random(5000,86400)')
		->gen('when always set name.value=randomformat("fullnameformat")')
		->insert(true)
		->stat()
		->show(['name', 'datecreation']);
}
if($products) {
	$chaos = new ChaosMachineOne();
	$chaos->table('products', count(Products::$products))
		->setDb($db)
		->field('idproduct', 'int', 'identity', 0, 0, 1000)
		->field('name', 'string', 'database', '', 0, 45)
		->field('price', 'decimal', 'database', 2, 0, 100)
		->setArray('productname', Products::$products)
		->gen('when always set price.value=random(0.5,20,0.1)')
		->gen('when always set name.value=arrayindex("productname")')
		->insert(true)
		->stat();
		//->show(['name', 'price']);
	//->insert();
}
if($sales || 1==1 ) {
	$countProducts=count(Products::$products);
	$chaos = new ChaosMachineOne();
	$chaos->table('sales', 5000)
		->setDb($db)
		->field('idsales', 'int', 'identity', 0)
		->field('idproduct', 'int', 'database', 1)
		->field('idcustomer', 'int', 'database', 1)
		->field('amount', 'int', 'database', 1, 1, 100)
		->field('date', 'datetime', 'database', $chaos->now())
		->gen('when date.weekday>=1 and date.weekday<=5 then date.speed=random(5000,50000)') // workingdays are slow
		->gen('when date.weekday>=6  then date.speed=random(3000,10000)') // the weekend sells more
		->gen('when date.hour>18 then date.skip="day" and date.add="8h"') // if night then we jump to the next day (8am)
		->gen('when always then idproduct.value=random(1,$countProducts) 
		and idcustomer.value=random(1,1000) and amount.value=random(1,10)')
		->show(['idproduct','idcustomer','amount','date'])
		->insert(true)
		->stat()
		->show(['idproduct','idcustomer','amount','date']);
}