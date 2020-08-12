<?php

use eftec\PdoOne;
use mapache_commons\Collection;
use mapache_commons\Text;

include "../../vendor/autoload.php";
include "../Text.php";
include "../Collection.php";
include 'SakilaLib.php';

$db=new PdoOne("mysql","localhost","root","abc.123","sakila");
$db->open();
$db->logLevel=3;



$txt="A Emotional Epistle of a Pioneer And a Crocodile who must Battle a Man in A Manhattan Penthouse";


$p=0;

$films=$db->select('description')->from('film')->toListSimple();
/*
$first=[];
foreach($films as $k=>$txt) {
    $r=explode(',',$txt);
    $first=array_merge($first,$r);
    
}

echo Collection::generateTable([$first]);
die(1);
*/
foreach($films as $k=>$txt) {
    echo "<hr>";
    
    $p=0;
    $r=Text::between($txt, "A ", " of a ", $p, true);
    $r=explode(' ',$r,2);
    $first[$k][0]=$r[0];
    $first[$k][1]=$r[1];
    $first[$k][2]=Text::between($txt, " of a ", " and a ", $p, true);
    $first[$k][3]=Text::between($txt, "and a ", " who ", $p, true);
    $first[$k][4]=Text::between($txt, " who must ", " a ", $p, true);
    $first[$k][5]=Text::between($txt, " a ", " in ", $p, true);
    $first[$k][6]=Text::between($txt, " in ", "", $p, true);
    var_dump($txt);
    var_dump($first[$k]);

}
echo Collection::generateTable($first);
