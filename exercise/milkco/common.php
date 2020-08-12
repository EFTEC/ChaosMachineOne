<?php

use eftec\chaosmachineone\ChaosMachineOne;
use eftec\PdoOne;

@set_time_limit(200);

include "../../vendor/autoload.php";
include "../../lib/en_US/Person.php";
include __DIR__.'/SakilaLib.php';
include "../../lib/en_US/World.php";
include "../../lib/en_US/Address.php";
include "../../lib/en_US/Company.php";


$db=new PdoOne("mysql","localhost","root","abc.123","milkco");
$db->open();
$db->logLevel=3;


$chaos=new ChaosMachineOne();
$chaos->debugMode=true;
$chaos->setDb($db);



/*
$tables=$db->tableSorted();
foreach($tables as $table) {
    echo "truncate table `".$table."`;<br>";
}


$tables=$db->objectList('table','true');

foreach($tables as $table) {
    $file=$table.'.php';
    if(!file_exists($file)) {
        $chaos=new ChaosMachineOne();
        $chaos->setDb($db);
        $code="<?php\n";
        $code.="use eftec\chaosmachineone\ChaosMachineOne;\n";
        $code.="\n include 'common.php';\n";
        $code.=$chaos->generateCode($table);
        file_put_contents($file,$code);
    }
}
*/
/*

 */
