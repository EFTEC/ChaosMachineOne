<?php

use eftec\chaosmachineone\ChaosMachineOne;
use eftec\PdoOne;

@set_time_limit(200);

include "../../vendor/autoload.php";
include "../../lib/en_US/Person.php";
include __DIR__.'/SakilaLib.php';
include "../../lib/en_US/World.php";
include "../../lib/en_US/Address.php";


$db=new PdoOne("mysql","localhost","root","abc.123","milkco");
$db->open();
$db->logLevel=3;


$chaos=new ChaosMachineOne();
$chaos->setDb($db);



//include 'Roles.php';
//include 'Country.php';
//include 'Cities.php';
//include 'ProductTypes.php';
include 'Branches.php'; // ok 

include 'Employees.php'; // ok
include 'Brands.php'; // ok
include 'Containers.php'; // ok
include 'Customers.php'; // ok
include 'Invoices.php';
include 'InvoiceDetails.php';
include 'ProductSubTypes.php'; // ok
include 'Services.php'; // ok
include 'Skus.php'; // ok
