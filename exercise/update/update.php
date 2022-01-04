<?php

use eftec\chaosmachineone\ChaosMachineOne;
use eftec\PdoOne;

include '../../vendor/autoload.php';

$chaos=new ChaosMachineOne();
$chaos->setDb(new PdoOne('sqlsrv','PCJC\SQLDEV','sa','ats475','testdb'));
$chaos->getDb()->logLevel=3;
$chaos->getDb()->connect();
echo "<pre>";
echo $chaos->generateCode('table1');
echo "</pre>";
// insert 1000 values
/*
$chaos->table('table1', 1000)
    ->field('id', 'int','identity', 0)
    ->field('number', 'int','database')
    ->isnullable(true)
    ->field('text', 'string','database','',0,50)
    ->isnullable(true)
    ->gen('when always set number.value=random(1,100,1,10,10)')
    ->gen('when always set text.value=random(0,50)')
    ->setInsert(true)
    ->showTable(['id','number','text'],true)
    ->run(true);
*/
// update 1000 values
$chaos->table('table1', 'table1')
    ->field('id', 'int','identity', 0)
    ->field('number', 'int','database')
    ->isnullable(true)
    ->field('text', 'string','database','',0,50)
    ->isnullable(true)
    ->gen('when always set number.value=random(1,100,1,10,10)')
    ->gen('when always set text.value="hello world"')
    ->gen('when always set update("table1","id",origin_id,"text",text.value)')
    //->setInsert(true)
    ->showTable(['id','number','text'],true)
    ->run(true);