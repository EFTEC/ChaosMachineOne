<?php
use eftec\chaosmachineone\ChaosMachineOne;
use Faker\Address;

include 'common.php';

/** @noinspection PhpUndefinedVariableInspection */
$branches=$db->runRawQuery('select idbranch,idcity from branches order by idbranch',[],true);


function createEmployees($db,$num,$idbranch,$idcity,$role=0)
{
    $chaos = new ChaosMachineOne();
    $chaos->debugMode = true;
    $chaos->setDb($db);
    $chaos->table('Employees', $num)
        ->setDb($db)->setFormat('nameformat', ['{{firstName}} {{lastName}}'])
        ->setFormat('dirformat', Address::$streetAddressFormats)
        ->setArray('buildingNumber', Address::$buildingNumber)
        ->setArray('streetName', Address::$streetNames)
        ->setArray('secondaryAddress', Address::$secondaryAddressFormats)
        ->setFormat('emailformat', [
            '{{firstName}}.{{lastName}}@milkco.dom',
            '{{firstName}}_{{lastName}}@milkco.dom',
            '{{lastName}}{{firstName}}@milkco.dom',
            '{{lastName}}.{{firstName}}@milkco.dom',
            '{{firstName}}{{randomnumber}}@milkco.dom',
            '{{firstName}}.{{lastName}}{{randomnumber}}@milkco.dom'
        ])
        ->field('randomnumber', 'int', 'local')
        ->setArray('sex_array',['female'=>30,'male'=>50,'na'=>20])
        ->setArray('male_firstname_array',PersonContainer::$firstNameMale,'fakebell')
        ->setArray('female_firstname_array',PersonContainer::$firstNameFemale,'fakebell')
        ->setArray('lastname_array',PersonContainer::$lastName,'fakebell')
        ->field('idEmployee', 'int', 'identity')->isnullable(true)
        ->field('email', 'string', 'database', '', 0, 45)
        ->field('password', 'string', 'database', '', 0, 64)
        ->field('idBranch', 'int', 'database')
        ->field('idRole', 'int', 'database')
        ->field('fixedrole', 'int', 'local',$role)
        ->field('enabled', 'int', 'database')
        ->field('idCity', 'int', 'database')
        ->field('address', 'string', 'database', '', 0, 200)
        ->field('firstName', 'string', 'database','',0,200)->allowNull()
        ->field('lastName', 'string', 'database','',0,200)->allowNull()
        ->field('age','int','database',0,0,80)->allowNull()
        ->field('sex','string','database','na',0,20)->allowNull()
        ->field('lastUpdate', 'datetime', 'database', new DateTime('now'))
        ->setArrayFromDBTable('array_idBranch', 'Branches', 'idBranch', 'fakebell3')
        //->setArrayFromDBTable('array_idCity', 'Cities', 'idCity', 'fakebell3')
        ->setArrayFromDBTable('array_idRole', 'Roles', 'idRole', 'rightbias')
        ->gen('when always set sex.value=randomarray("sex_array")')
        ->gen('when sex.value="male" 
        set firstName.value=randomarray("male_firstname_array") 
        else firstName.value=randomarray("female_firstname_array")')
        ->gen('when always set randomnumber.value=random(1940,2020,1)')
        ->gen('when always set age.value=random(18,60,1,20,40,60,20,10)')
        ->gen('when always set lastName.value=randomarray("lastname_array")')
        ->gen('when always set randomnumber.value=random(1950,2005)')
        ->gen('when always set idBranch.value=' . $idbranch)
        ->gen('when always set idCity.value=' . $idcity) // they live in the samcity city than the branch
        ->gen('when fixedrole.value=0 set idRole.value=randomarray("array_idRole") else idRole.value=fixedrole.value')
        //->gen('when always set idEmployee.value=random(1,100,1,10,10)')
        ->gen('when always set email.value=randomformat("emailformat")')
        ->gen('when always set password.value=randommask("########")')
        ->gen('when always set enabled.value=random(0,1,1,10,80)')
        ->gen('when always set address.value=randommaskformat("dirformat")')
        ->gen('when always set lastUpdate.speed=random(3600,86400)')
        ->setInsert(false)
        ->showTable([
            'idEmployee',
            'firstName',
            'lastName',
            'sex',
            'age',
            'email',
            'password',
            'idBranch',
            'idRole',
            'enabled',
            'idCity',
            'address',
            'lastUpdate'
        ], true)
        ->run(true);
}

foreach($branches as $branch) {


    $idbranch = $branch['idbranch'];
    $idcity = $branch['idcity'];
    $empnu = mt_rand(5, 50); // from 5 to 50 employees per branch

    $db->from('employees')->where('idbranch=?', [$idbranch])->delete();



    createEmployees( $db, $empnu, $idbranch, $idcity);
    // management (at least 1 management)
    createEmployees( $db, ChaosMachineOne::randomStatic(1, 1, 1), $idbranch, $idcity, 1);
    // salesman (at least 1 salesman)
    createEmployees( $db, ChaosMachineOne::randomStatic(1, 1, 1), $idbranch, $idcity, 4);
}



