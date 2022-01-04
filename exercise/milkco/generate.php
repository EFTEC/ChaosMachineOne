<?php

use eftec\PdoOne;

include '../../vendor/autoload.php';

$t1=microtime(true);
echo "<h1>making</h1>";


// connecting to database sakila at 127.0.0.1 with user root and password abc.123
$dao = new PdoOne('mysql', '127.0.0.1', 'root', 'abc.123', 'milkco');
//$dao->setUseInternalCache(true);
$dao->logLevel=3;
$dao->open();

//new \dBug\dBug($dao->getDefTable('fummediciones'));
//die(1);

/*
var_dump($dao->objectList('table',true));
var_dump($dao->objectList('table',true));
var_dump($dao->lastQuery);

die(1);
*/
/*$tables=$dao->objectList('table',true);

foreach($tables as $table) {
    $tablen=ucfirst($table);
    echo "'$table'          => ['{$tablen}Repo', '{$tablen}Model'],<br>";
}*/
//tproject\\ChaosMachineOne\\exercise\\milkco\/repo\/AbstractCountriesRepo.ph

//die(1);
$relations = [
    'Countries'=>['CountriesRepo'],
    'Cities'=>['CitiesRepo'],
    'ProductTypes'=>['ProductTypesRepo'],
    'Branches'=>['BranchesRepo'],
    'Brands'=>['BrandsRepo'],
    'Containers'=>['ContainersRepo'],
    'Customers'=>['CustomersRepo'],
    'Invoices'=>['InvoicesRepo'],
    'InvoiceDetails'=>['InvoiceDetailsRepo'],
    'ProductSubTypes'=>['ProductSubTypesRepo'],
    'Roles'=>['RolesRepo'],
    'Employees'=>['EmployeesRepo'],
    'Services'=>['ServicesRepo'],
    'Products'=>['ProductsRepo']
    // 'chamberhistories'       => ['ChamberHistoryRepo', 'ChamberHistory'],

];
$columnRelation = [
    //'chambers'     => ['_processes'=>'PARENT'],
];

foreach ($relations as $k => $v) {
    $relations[$k] = $v[0]; // remove from array
}





/*
$txt="ALTER TABLE `termo2`.`chamberhistories2`  RENAME TO  `termo2`.`ChamberHistories` ;";

    foreach($relations as $i=>$k) {
        $k0=$k[0];
        $k1=str_replace('Repo','Model',$k0);
        echo "'$i' => ['$k0', '$k1'],\n";
    }
*/


$tables = $dao->tableSorted();
foreach ($tables as $table) {
    echo "'{$table}'=>['{$table}Repo'],<br>";
    // echo "include 'repo/{$table}RepoExt.php';<br>";
    // echo "include 'model/{$table}Repo.php';<br>";
}

$dao->generateCodeClassConversions(['datetime' => 'datetime3', 'decimal' => 'decimal','int'=>'int']);
$logs = $dao->generateAllClasses($relations, 'XBaseDb', 'termo2\repo',
    __DIR__ . '/repomysql', false, $columnRelation);


echo "Cache:".$dao->internalCacheCounter."<br>";
echo "Cache Count:".count($dao->internalCache)."<br>";
echo "errors:<br>";
echo "time:".(microtime(true)-$t1)."<br>";
echo "<pre>";
var_dump($logs);
echo "</pre>";


