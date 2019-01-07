<?php

use eftec\chaosmachineone\ChaosMachineOne;
use eftec\DaoOne;
use eftec\minilang\MiniLang;
include "../vendor/autoload.php";
include "../lib/en_US/Person.php";
$chaos=new ChaosMachineOne();

$files=$chaos->arrayFromFolder('./machines/','jpg');

$localfolder='./machines/';
$destinationfolder='./databasefile/';

$filesWithoutExtension=$chaos->arrayFromFolder($localfolder,'jpg',false); 

$sql="CREATE TABLE `products` (
  `IdProduct` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) NOT NULL DEFAULT '',
  `Price` int(11) NOT NULL DEFAULT '0',
  `Image` varchar(50) NOT NULL DEFAULT '',
  `Description` varchar(2000) NOT NULL DEFAULT '',
  `Weight` int(11) NOT NULL DEFAULT '0',
  `IdCategory` int(11) NOT NULL,
  PRIMARY KEY (`IdProduct`)
) ENGINE=InnoDB";


$db=new DaoOne("localhost","root","abc.123","chicago");
$db->open();
try {
	$db->runRawQuery($sql);
} catch(Exception $exception) {
	// table already created
}

$numFiles=count($files);
echo "<h1>Creating $numFiles products</h1>";

// this examples works with files. It does the next tasks.
// * it fills the database with information based on the files (products)
// * it copies the files from the source /machines to destination /databasefile changing the filename

$chaos->table('products',$numFiles) // the table products must exist!.
	->setDb($db)
	->field('IdProduct','int', 'identity', 0)
	->field('Name', 'string', 'database', '', 0, 50)
	->field('Price', 'int', 'database', 0, 0, 45)
	->field('Image', 'string', 'database', '') // it is store on the db.
	->field('ImageSource', 'string', 'variable', '') // it is the local file (it is not store on the db)
	->field('ImageDestination', 'string', 'variable', '') // it is the destination file. (it is not store on the db)
	->field('Description', 'string', 'database', '', 0, 100)
	->field('Weight', 'int', 'database', 0)
	->field('IdCategory', 'int', 'database', 0)
	->setArray('productimages',  $files)
	->setArray('productimagescode', $filesWithoutExtension)
	->setArray('productnames', ['Augers','Concrete Tools','Compactors','Demolition Tools','Drain Cleaners','Plumbing Tools','Floor Cleaners','Floor Care','Refinishers'])
	->setArray('loremIpsum',PersonContainer::$loremIpsum)
	->setFormat('productformat', ['{{productnames}} {{productimagescode}}', '{{productnames}}'])
	->setFormat('imageformat', ['{{IdProduct}}_{{productimages}}'])
	->gen('when always set IdProduct.add=1') // it increases 1 by one.
	->gen('when always set Name.value=randommask("? Model #####-00","productformat")')
	->gen('when always set Price.value=random(10,100)')
	->gen('when always set Image.value=randomformat("imageformat")')
	->gen('when always set ImageSource.value=$localfolder + arrayindex("productimages")')
	->gen('when always set ImageDestination.value=$destinationfolder + Image.getvalue')
	->gen('when always set ImageDestination.copyfilefrom=ImageSource.getvalue')
	//->gen('when always set Image.value=IdProduct.getvalue and Image.add="_" and Image.add=arrayindex("productimages")') // it's the same than randomformat("imageformat") 
	->gen('when always then Description.value=randomtext("Lorem ipsum dolor","loremIpsum",1,4,30)')
	->gen('when always set Weight.value=random(2,10)')
	->gen('when always set IdCategory.value=random(1,4)')
	->show(['IdProduct','Name','Price','Image','ImageSource','ImageDestination','Description','Weight','IdCategory'])
	//->insert()
	;

	

