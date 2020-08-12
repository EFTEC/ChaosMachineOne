<?php
use eftec\chaosmachineone\ChaosMachineOne;

 include 'common.php';
$chaos->table('Country', 1000)
		->setDb($db)
		->field('idCountry', 'int','database')
			->isnullable(true)
		->field('name', 'string','database','',0,50)
			->isnullable(true)
		->field('lastUpdate', 'datetime','database',new DateTime('now'))
			->isnullable(true)
		->gen('when always set idCountry.value=random(1,100,1,10,10)')
		->gen('when always set name.value=random(0,50)')
		->gen('when always set lastUpdate.speed=random(3600,86400)')
		->setInsert(true)
		->showTable(['idCountry','name','lastUpdate'],true)
		->run(true);
