<?php
use eftec\chaosmachineone\ChaosMachineOne;
include_once 'common.php';

var_dump($db->from('Cities')->count());

if($db->from('Cities')->count()>0) {
    echo "table cities skipped<br>";
} else {
    foreach($cities as $city) {
        $db->insert('cities',$city);
    }
    echo "inserting cities<br>";
}
