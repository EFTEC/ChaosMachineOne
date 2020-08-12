<?php
use eftec\chaosmachineone\ChaosMachineOne;

 include 'common.php';
$chaos->table('Containers', 1000)
		->setDb($db)
		->field('idContainer', 'int','identity', 0)
		->field('idDairySubType', 'int','database')
		->field('name', 'string','database','',0,45)
		->field('sizeOunce', 'decimal','database')
		->field('lastUpdate', 'datetime','database',new DateTime('now'))
		->gen('when always set idDairySubType.value=random(1,100,1,10,10)')
		->gen('when always set name.value=random(0,45)')
		->gen('when always set sizeOunce.value=random(1,100,0.1,10,10)')
		->gen('when always set lastUpdate.speed=random(3600,86400)')
		->setInsert(true)
		->showTable(['idContainer','idDairySubType','name','sizeOunce','lastUpdate'],true)
		->run(true);
