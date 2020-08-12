<?php
use eftec\chaosmachineone\ChaosMachineOne;

 include 'common.php';
$chaos->table('ProductTypes', 1000)
		->setDb($db)
		->field('idProductType', 'int','identity', 0)
		->field('name', 'string','database','',0,45)
		->field('lastDate', 'datetime','database',new DateTime('now'))
		->gen('when always set name.value=random(0,45)')
		->gen('when always set lastDate.speed=random(3600,86400)')
		->setInsert(true)
		->showTable(['idProductType','name','lastDate'],true)
		->run(true);
