<?php
use eftec\chaosmachineone\ChaosMachineOne;

 include 'common.php';
$chaos->table('Brands', 1000)
		->setDb($db)
		->field('idBrand', 'int','identity', 0)
		->field('name', 'string','database','',0,45)
		->field('lastUpdate', 'datetime','database',new DateTime('now'))
		->gen('when always set name.value=random(0,45)')
		->gen('when always set lastUpdate.speed=random(3600,86400)')
		->setInsert(true)
		->showTable(['idBrand','name','lastUpdate'],true)
		->run(true);
