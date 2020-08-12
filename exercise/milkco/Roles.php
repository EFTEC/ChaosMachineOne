<?php
use eftec\chaosmachineone\ChaosMachineOne;

 include 'common.php';
$chaos->table('Roles', 1000)
		->setDb($db)
		->field('idRole', 'int','database')
			->isnullable(true)
		->field('name', 'string','database','',0,45)
		->field('monthlySalary', 'decimal','database')
		->field('lastUpdate', 'datetime','database',new DateTime('now'))
		->gen('when always set idRole.value=random(1,100,1,10,10)')
		->gen('when always set name.value=random(0,45)')
		->gen('when always set monthlySalary.value=random(1,100,0.1,10,10)')
		->gen('when always set lastUpdate.speed=random(3600,86400)')
		->setInsert(true)
		->showTable(['idRole','name','monthlySalary','lastUpdate'],true)
		->run(true);
