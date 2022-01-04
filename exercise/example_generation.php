<?php /** @noinspection ForgottenDebugOutputInspection */


use eftec\chaosmachineone\ChaosMachineOne;
use eftec\minilang\MiniLang;
include "../vendor/autoload.php";


$machine=new ChaosMachineOne();

$pdo=new \eftec\PdoOne('mysql','127.0.0.1','root','abc.123','sakila');
$pdo->open();





//  when date.w=2  then random(20,300)

echo "<pre>";
$machine->setDb($pdo);
echo $machine->generateCode('*');

echo "</pre>";


