<?php
use eftec\chaosmachineone\ChaosMachineOne;

include_once 'common.php';

$brands=['Value Pack','Aunty Annie','Red Label'];

if($db->from('Brands')->count()>0) {
    echo "table Brands skipped<br>";
} else {
    echo "inserting Brands<br>";
    foreach($brands as $brand) {
        $db->insert('Brands',['name'=>$brand]);
    }
    echo "inserting Brands OK<br>";
}


