<?php
use eftec\chaosmachineone\ChaosMachineOne;

 include 'common.php';
$chaos->table('Services', 1000)
		->setDb($db)
		->field('idService', 'int','database')
			->isnullable(true)
		->field('name', 'string','database','',0,45)
		->field('lastUpdate', 'datetime','database',new DateTime('now'))
		->gen('when always set idService.value=random(1,100,1,10,10)')
		->gen('when always set name.value=random(0,45)')
		->gen('when always set lastUpdate.speed=random(3600,86400)')
		->setInsert(true)
		->showTable(['idService','name','lastUpdate'],true)
		->run(true);
