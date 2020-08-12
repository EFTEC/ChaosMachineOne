<?php
use eftec\chaosmachineone\ChaosMachineOne;

 include 'common.php';
$chaos->table('Skus', 200)
		->setDb($db)
        ->setFormat('nameformat',['{{nameproductsubtype}} {{namecontainer}} {{namebrand}}'])
        ->setFormat('serviceformat',['{{nameservice}} for {{namebrand}}','{{nameservice}} #{{randomnumber}} for {{namebrand}}'])
		->field('sku', 'int','identity', 0)
		->field('name', 'string','database','',0,200)
		->field('stock', 'int','database')
		->field('minStock', 'int','database')
		->field('isService', 'int','database')
		->field('idProductSubType', 'int','database')
		->field('idContainer', 'int','database')
		->field('idBrand', 'int','database')
		->field('idService', 'int','database')
		->field('unitPrice', 'decimal','database')
		->field('margin', 'decimal','database')
		->field('lastUpdate', 'datetime','database',new DateTime('now'))
        ->field('keybrand','int','local')
        ->field('keycontainer','int','local')
        ->field('keyproductsubtype','int','local')
        ->field('keyservice','int','local')
        

        ->field('namebrand','string','local')
        ->field('namecontainer','string','local')
        ->field('nameproductsubtype','string','local')
        ->field('nameservice','string','local')
        ->field('randomnumber','int','local')
        
    
		->setArrayFromDBTable2('array_idBrand','array_nameBrand','Brands','idBrand','name','leftbias')
		->setArrayFromDBTable2('array_idContainer','array_nameContainer','Containers','idContainer','name')
		->setArrayFromDBTable2('array_idProductSubType','array_nameProductSubType','ProductSubTypes'
                    ,'idProductSubType','name','leftbias')
		->setArrayFromDBTable2('array_idService','array_nameService','Services','idService','name')
    
        ->gen('when always set randomnumber.value=random(1,100)')
    
        ->gen('when always set keybrand.value=randomarraykey("array_nameBrand")')
    ->gen('when always set keycontainer.value=randomarraykey("array_nameContainer")')
    ->gen('when always set keyproductsubtype.value=randomarraykey("array_nameProductSubType")')
    ->gen('when always set keyservice.value=randomarraykey("array_nameService")')
    
    
        ->gen('when always set idBrand.value=getArray("array_idBrand",keybrand.value)')
        ->gen('when always set idContainer.value=getArray("array_idContainer",keycontainer.value)')
        ->gen('when always set idProductSubType.value=getArray("array_idProductSubType",keyproductsubtype.value)')


    ->gen('when always set namebrand.value=getArray("array_nameBrand",keybrand.value)')
    ->gen('when always set namecontainer.value=getArray("array_nameContainer",keycontainer.value)')
    ->gen('when always set nameproductsubtype.value=getArray("array_nameProductSubType",keyproductsubtype.value)')
    ->gen('when always set nameservice.value=getArray("array_nameService",keyservice.value)')       
    
		//->gen('when always set idContainer.value=randomarray("array_idContainer")')
		//->gen('when always set idProductSubType.value=randomarray("array_idProductSubType")')
		->gen('when always set idService.value=randomarray("array_idService")')
		
		->gen('when always set stock.value=random(1,100,1,10,10)')
		->gen('when always set minStock.value=random(1,100,1,10,10)')
		->gen('when always set isService.value=random(0,1,1,50,1,1)')
        ->gen('when isService=0 set name.value=randomformat("nameformat")')
        ->gen('when isService=1 set name.value=randomformat("serviceformat")')
        ->gen('when isService=1 set unitPrice.value=random(10,100,0.1,10,10)')
		->gen('when idBrand=1 and isService=0 set unitPrice.value=random(1,20,0.1,10,10)')
        ->gen('when idBrand=2 and isService=0 set unitPrice.value=random(20,50,0.1,10,10)')
        ->gen('when idBrand=3 and isService=0 set unitPrice.value=random(50,100,0.1,10,10)')
        ->gen('when isService=1 set margin.value=3')
		->gen('when idBrand=1 and isService=0 set margin.value=random(1,10,0.1,10,10)')
        ->gen('when idBrand=2 and isService=0 set margin.value=random(8,15,0.1,10,10)')
        ->gen('when idBrand=3 and isService=0 set margin.value=random(10,20,0.1,10,10)')
		->gen('when always set lastUpdate.speed=random(3600,86400)')
		->setInsert(false)
		->showTable(['sku','name','stock','minStock','isService','idProductSubType','idContainer','idBrand','idService','unitPrice','margin','lastUpdate'],true)
		->run(true);
