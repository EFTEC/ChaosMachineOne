<?php

use eftec\chaosmachineone\ChaosMachineOne;

use eftec\minilang\MiniLang;
include "../vendor/autoload.php";

$chaos=new ChaosMachineOne();
$chaos->setDictionary('_index',100);
                                   
include "../lib/en_US/Person.php";



// skip next day
// skip next workingday
// skip next weekend
// skip next monday
// skip add 8 hours
// skip next month (first month)


$chaos->table('table',PersonContainer::$firstNameMale,"original")
	->field('name','string','local','',0,200)
	->gen('when always then name.value=original.value')
	->showTable(['name','original'])
	->run();
var_dump($chaos->getDictionary());
	
