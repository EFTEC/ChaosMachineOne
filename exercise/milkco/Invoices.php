<?php
use eftec\chaosmachineone\ChaosMachineOne;

 include 'common.php';

@set_time_limit(600); 
 
/*
  * SET @i=0;
UPDATE Invoices SET idInvoice=(@i:=@i+1);
  */

$db->from('invoices')->where('1=1')->delete();


$customerArray=$db->select('cu.idcustomer,round(population/1000) pop') // we divided by 1000 because we don't need big numbers
    ->from('invoices inv')
    ->innerjoin('customers cu on inv.idcustomer=cu.idcustomer')
    ->innerjoin('cities ci on cu.idcity=ci.idcity')
    ->toListKeyValue(); // popularity of the customers by population of the cities
/*echo "<pre>";
var_dump($customerArray);
echo "</pre>";
die(1);
*/

$customers=$db->runRawQuery(
    'select b.idcustomer,b.idcity,c.idcountry,b.isbusiness from customers b 
        inner join cities c on b.idcity=c.idcity',[],true);

foreach($customers as $kounter=>$cus) {

    $idcustomer=$cus['idcustomer'];
    $idcity=$cus['idcity'];
    $idcountry=$cus['idcountry'];
    $isbusiness=$cus['isbusiness'];

    $chaos = new ChaosMachineOne();
    if($isbusiness==="1") {
        $empnu = $chaos->random(1,200,1,50,1); // from 1 to 200 invoices per company with a trend to 1    
    } else {
        $empnu = $chaos->random(1,40,1,50,1); // from 1 to 40 invoices per customer with a trend to 1
    }
    
    $chaos->debugMode = false;
    $chaos->setDb($db);
    
    $sellers=$db->select('idemployee')->from('employees e')
                ->innerjoin('cities ci on e.idcity=ci.idcity')
                ->where('e.idrole=? and ci.idcountry=?',['i',4,'i',$idcountry])
                ->toListSimple(); // sellers from the same country

    $empyear = $chaos->random(-13,-1,1); // customers are 1 to 13 year old
    $init=(new DateTime('now'))->modify($empyear.' year');

    $chaos->table('Invoices', $empnu)->setDb($db)
        ->field('idInvoice', 'int', 'identity', 0)
        ->field('creationDate', 'datetime', 'database', $init)
        ->field('idCustomer', 'int', 'database')
        ->field('idSeller', 'int', 'database')
        ->field('lastDate', 'datetime', 'database', new DateTime('now'))
        ->setArray('array_idCustomer', $customerArray) // customers
        //->setArrayFromDBTable('array_idCustomer','Customers','idCustomer')
        ->setArray('array_idSeller',$sellers)
        //->setArrayFromDBTable('array_idSeller', 'Employees', 'idEmployee', [1], 'idrole=4 and idcity='.$idcity) // sales
        ->gen('when always set idCustomer.value='.$idcustomer)
        ->gen('when always set idSeller.value=randomarray("array_idSeller")')
        ->gen('when creationDate.month<4 and creationDate.weekday<6 set creationDate.speed=random(86400,728000)')
        ->gen('when creationDate.month>=4 and creationDate.month<=8 and creationDate.weekday<6 set creationDate.speed=random(86400,1728000)')
        ->gen('when creationDate.month>8 and creationDate.weekday<6 set creationDate.speed=random(86400,728000)')
        ->gen('when creationDate.month<4 and creationDate.weekday>5 set creationDate.speed=random(86400,1228000)')
        ->gen('when creationDate.month>=4 and creationDate.weekday>5 set creationDate.speed=random(86400,1628000)')
        ->gen('when always set lastDate.speed=0')
        ->setInsert(false)
        //->showTable(['idInvoice', 'creationDate', 'idCustomer', 'idSeller', 'lastDate'], true)
        ->run(true);
    echo "<h1>$idcustomer $kounter of ".count($customers)." : $empnu</h1>";
}