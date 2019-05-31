<?php

use eftec\chaosmachineone\ChaosMachineOne;
use eftec\PdoOne;

@set_time_limit(200);

include "../vendor/autoload.php";
include "../lib/en_US/Person.php";
include "../lib/en_US/Products.php";


//$db=new PdoOne("mysql","localhost","root","abc.123","chaosdb");
$db=new PdoOne("sqlsrv","localhost","sa","abc.123","BASE_LARI");
$db->open();
$db->logLevel=3;
$chaos = new ChaosMachineOne();
$chaos->setDb($db);


//die(1);
//echo date("U",strtotime('2012-01-18 11:45:00'));
//var_dump($chaos->now());
//die(1);
$chaos->table('auditorias', 15000)
	->setDb($db)
	->field('FechaAtencion', 'datetime','database',$chaos->date('2010-01-01 00:00'))
	->isnullable(true)
	->field('Idauditoria', 'int','identity', 0)
	->field('IdActividad', 'int','database')
	->isnullable(true)
	->field('IdClave', 'int','database')
	->isnullable(true)
	->field('IdProducto', 'int','database')
	->isnullable(true)
	->field('IdEstado', 'int','database')
	->isnullable(true)
	->field('IdNormalizacion', 'int','database')
	->isnullable(true)
	->field('IdIPTV', 'int','database')
	->isnullable(true)
	->field('IdClasificacion', 'int','database')
	->isnullable(true)
	->field('IdTipoAuditoria', 'int','database')
	->isnullable(true)
	->field('IdInformacion', 'int','database')
	->isnullable(true)
	->field('IdSTB', 'int','database')
	->isnullable(true)
	->field('IdBA', 'int','database')
	->isnullable(true)
	// extras
	->setFormat('f_phones',['0#########','(##)-0#########'])
	->setArray('a_lorem',PersonContainer::$loremIpsum)
	// end extras
	->field('IdDTH', 'int','database')
	->isnullable(true)
	->field('IdFO', 'int','database')
	->isnullable(true)
	
	->field('FechaIngreso', 'datetime','database',$chaos->date('2010-01-01 00:00'))
	->isnullable(true)
	->field('Rutauditor', 'string','database','',0,12)
	->isnullable(true)
	->field('Ruttecnico', 'string','database','',0,12)
	->isnullable(true)
	->field('Peticion', 'string','database','',0,50)
	->isnullable(true)
	->field('Telefono', 'string','database','',0,50)
	->isnullable(true)
	->field('Observaciones', 'string','database','',0,50)
	->isnullable(true)
	->gen('when _index=0 then FechaIngreso.speed=3600 and FechaIngreso.accel=0')
	->gen('when _index=0 then FechaAtencion.speed=3600 and FechaIngreso.accel=0')
	->setArrayFromDBTable('array_IdActividad','Actividades','IdActividad')
	->setArrayFromDBTable('array_IdClave','Claves','Idclave')
	->setArrayFromDBTable('array_IdProducto','Producto','IdProducto')
	->setArrayFromDBTable('array_IdEstado','Estado','IdEstado')
	->setArrayFromDBTable('array_IdNormalizacion','Normalizaciones','IdNormalizacion')
	->setArrayFromDBTable('array_IdTipoAuditoria','TipoAuditoria','IdTipoAuditoria')
	->setArrayFromDBTable('array_IdClasificacion','Clasificacion','IdClasificacion')
	->setArrayFromDBTable('array_IdInformacion','InformacionF','IdInformcion')
	->setArrayFromDBTable('array_IdSTB','STB','IdSTB')
	->setArrayFromDBTable('array_IdBA','BA','IdBA')
	->setArrayFromDBTable('array_IdDTH','DTH','IdDTH')
	->setArrayFromDBTable('array_IdFO','FO','IdFO')
	->setArrayFromDBTable('array_IdIPTV','IPTV','IdIPTV')
	->setArrayFromDBTable('array_Ruttecnico','Tecnicos','Rut')
	->setArrayFromDBTable('array_Rutauditor','Auditores','Rut')
	->gen('when always set IdActividad.value=randomarray("array_IdActividad")')
	->gen('when always set IdClave.value=randomarray("array_IdClave")')
	->gen('when always set IdProducto.value=randomarray("array_IdProducto")')
	->gen('when always set IdEstado.value=randomarray("array_IdEstado")')
	->gen('when always set IdNormalizacion.value=randomarray("array_IdNormalizacion")')
	->gen('when always set IdTipoAuditoria.value=randomarray("array_IdTipoAuditoria")')
	->gen('when always set IdClasificacion.value=randomarray("array_IdClasificacion")')
	->gen('when always set IdInformacion.value=randomarray("array_IdInformacion")')
	->gen('when always set IdSTB.value=randomarray("array_IdSTB")')
	->gen('when always set IdBA.value=randomarray("array_IdBA")')
	->gen('when always set IdDTH.value=randomarray("array_IdDTH")')
	->gen('when always set IdFO.value=randomarray("array_IdFO")')
	->gen('when always set IdIPTV.value=randomarray("array_IdIPTV")')
	->gen('when always set Ruttecnico.value=randomarray("array_Ruttecnico")')
	->gen('when always set Rutauditor.value=randomarray("array_Rutauditor")')
	->gen('when FechaAtencion.month<10 set FechaAtencion.speed=random(3600,86400)')
	->gen('when FechaIngreso.month<10 set FechaIngreso.speed=random(3600,86400)')
	->gen('when always set Peticion.value=random(0,50)') /** @see ChaosMachineOne::random */
	->gen('when always set Telefono.value=randommaskformat("f_phones")') /** @see ChaosMachineOne::randommask */
	->gen('when always set Observaciones.value=randomtext("","a_lorem")') /** @see ChaosMachineOne::randomtext */

	->gen('when FechaAtencion.month>=10 set FechaAtencion.speed=random(900,21600)')
	->gen('when FechaIngreso.month>=10 set FechaIngreso.speed=random(900,21600)')
	->gen('when FechaAtencion.weekday>5 set FechaAtencion.speed=random(1600,36400)') // mas atenciones el fin de semana
	->gen('when FechaIngreso.weekday>5 set FechaIngreso.speed=random(1600,36400)') // mas atenciones el fin de semana
	//->insert(true)
	->show(['FechaAtencion','FechaIngreso','Telefono','Observaciones']);