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


$chaos->table('table',1000)
	->field('name','string','database','',0,200)
	->field('fullname','string','database','',0,200)
	->field('text','string','database','',0,40)
	->field('prefix','string','database','',0,40)
	->field('prefixprop','string','database','',0,40)
	->field('nationalid','string','database','',0,40)
	->field('email','string','database','',0,40)
	->field('sex','int','local',0,0,1)
	->setArray('suffix',PersonContainer::$suffix,'increase')
	->setArray('firstNameMale',PersonContainer::$firstNameMale,"increase")  // first name are not popular, last names are most popular
	->setArray('lastName',PersonContainer::$lastName)
	->setArray('titleMale',PersonContainer::$titleMale)
	->setArray('firstNameFemale',PersonContainer::$firstNameFemale,"decrease") // first name are  popular, last names are least popular
	->setArray('titleFemale',PersonContainer::$titleFemale)
	->setArray('loremIpsum',PersonContainer::$loremIpsum)
	->setArray('domains',PersonContainer::$domains)
	->setArray('prefixarray',[''=>70,'Dr.'=>10,'Phd.'=>20]) //70% change of no prefix, 10% of Dr. and 20% of PhD
	->setFormat('maleNameFormats',PersonContainer::$maleNameFormats) 
	->setFormat('femaleNameFormats',PersonContainer::$femaleNameFormats) 
	->setFormat('formatProp',['{{firstNameMale}}'=>80,'{{suffix}} {{firstNameMale}}'=>20]) // 80% only name, 20% suffix and name
	->gen('when always then sex=random(0,1)')
	->gen('when sex=0 set name.value=randomarray("firstNameMale")')
	->gen('when sex=0 set fullname.value=randomformat("maleNameFormats")') /** @see \eftec\chaosmachineone\ChaosMachineOne::randomformat */
	->gen('when sex=1 set name.value=randomarray("firstNameFemale")')
	->gen('when sex=1 set fullname.value=randomformat("femaleNameFormats")')
	->gen('when always set prefixprop.value=randomformat("formatProp")')
	->gen('when always set prefix.value=randomarray("prefixarray")')
	->gen('when always set nationalid.value=randommask("##-00 uu ll \0 - xx (?)","lastName")') // 0 optional number #=forced number, u=upper text, l=lower text o=optional text,a=array
	->gen('when always then text.value=randomtext("Lorem ipsum dolor","loremIpsum",1,4,30)')
	->gen('when always then email.value=name.getvalue and email.concat=randommask("@?","domains")')
	->show(['name','fullname','text','prefix','prefixprop','nationalid','email']);
	
