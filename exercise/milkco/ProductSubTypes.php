<?php
use eftec\chaosmachineone\ChaosMachineOne;

 include 'common.php';
$chaos->table('ProductSubTypes', 1000)
		->setDb($db)
		->field('idProductSubType', 'int','identity', 0)
		->field('idProductType', 'int','database')
		->field('name', 'string','database','',0,45)
		->field('lastUpdate', 'datetime','database',new DateTime('now'))
		->field('unitName', 'string','database','',0,45)
		->field('ProductTypes_idProductType', 'int','database')
			->isnullable(true)
		->setArrayFromDBTable('array_ProductTypes_idProductType','ProductTypes','idProductType')
		->gen('when always set ProductTypes_idProductType.value=randomarray("array_ProductTypes_idProductType")')
		->gen('when always set idProductType.value=random(1,100,1,10,10)')
		->gen('when always set name.value=random(0,45)')
		->gen('when always set lastUpdate.speed=random(3600,86400)')
		->gen('when always set unitName.value=random(0,45)')
		->setInsert(true)
		->showTable(['idProductSubType','idProductType','name','lastUpdate','unitName','ProductTypes_idProductType'],true)
		->run(true);
