<?php

use eftec\chaosmachineone\ChaosMachineOne;
use eftec\PdoOne;

@set_time_limit(200);

include "../../vendor/autoload.php";
include "../../lib/en_us/Person.php";


$db=new PdoOne("mysql","localhost","root","abc.123","sakila");
$db->open();
$db->logLevel=3;

/*
$tables=$db->objectList('table','true');
foreach($tables as $table) {
    $file=$table.'.php';
    if(!file_exists($file)) {
        $chaos=new ChaosMachineOne();
        $chaos->setDb($db);
        $code="<?php\n";
        $code.="use eftec\chaosmachineone\ChaosMachineOne;\n";
        $code.=$chaos->generateCode($table);
        file_put_contents($file,$code);
    }
}
*/