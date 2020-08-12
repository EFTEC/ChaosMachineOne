<?php
use eftec\chaosmachineone\ChaosMachineOne;



include 'common.php';

$db->from('branches')->where('1=1')->delete();

$chaos->table('Branches', 50)
		->setDb($db)
        ->setArray('nameCompany',CompanyContainer::$nameCompany)
		->field('idBranch', 'int','identity', 0)
		->field('name', 'string','database','',0,45)
		->field('monthlyCost', 'decimal','database')
		->field('address', 'string','database','',0,200)
		->field('idCity', 'int','database')
		->field('idManager', 'int','database')->allowNull(true)
		->field('lastUpdate', 'datetime','database',new DateTime('now'))
        ->field('idkey', 'int','local')
        ->field('cityname', 'string','local')
        ->setFormat('nameformat',['{{cityname}} Farm','{{cityname}} Milk Co','{{cityname}} Co'
                                  ,'{{cityname}} Dairy'
                                  ,'{{nameCompany}} Dairy'
                                  ,'{{nameCompany}} Farm'])
		->setArrayFromDBTable2('array_idCity','array_cityName','Cities'
            ,['idCity'=>'population'],'name',[1],'population>1000000') // branches in populous cities with population>1 million 
		->setArrayFromDBTable('array_idManager','Employees','idEmployee')
        ->gen('when always set idkey.value=randomarraykey("array_cityName")')
        ->gen('when always set cityname.value=getArray("array_cityName",idkey.value)') /** @see \eftec\chaosmachineone\ChaosMachineOne::getArray **/
		->gen('when always set idCity.value=getArray("array_idCity",idkey.value)')
		->gen('when always set idManager.value=null()')
		->gen('when always set name.value=randomformat("nameformat")')
		->gen('when always set monthlyCost.value=random(1000000,10000000,1,10,50)')
		->gen('when always set address.value=random(0,200)')
		->gen('when always set lastUpdate.speed=random(3600,86400)')
		->setInsert(false)
		->showTable(['idBranch','name','monthlyCost','address','idCity','idManager','lastUpdate'],true)
		->run(true);
