<?php
use eftec\chaosmachineone\ChaosMachineOne;

die(1);

 include 'common.php';
$chaos->table('Cities', 1000)
		->setDb($db)
		->field('idCity', 'int','database')
			->isnullable(true)
		->field('name', 'string','database','',0,50)
			->isnullable(true)
		->field('idCountry', 'int','database')
			->isnullable(true)
		->field('population', 'int','database')
		->field('lastUpdate', 'datetime','database',new DateTime('now'))
			->isnullable(true)
		->setArrayFromDBTable('array_idCountry','Country','idCountry')
		->gen('when always set idCountry.value=randomarray("array_idCountry")')
		->gen('when always set idCity.value=random(1,100,1,10,10)')
		->gen('when always set name.value=random(0,50)')
		->gen('when always set population.value=random(1,100,1,10,10)')
		->gen('when always set lastUpdate.speed=random(3600,86400)')
		->setInsert(true)
		->showTable(['idCity','name','idCountry','population','lastUpdate'],true)
		->run(true);
