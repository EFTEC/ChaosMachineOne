<?php
use eftec\chaosmachineone\ChaosMachineOne;
use Faker\Address;

include 'common.php';
 
$branches=$db->runRawQuery('select idbranch,idcity from branches order by idbranch',[],true);

foreach($branches as $branch) {
    
    
    
    $idbranch=$branch['idbranch'];
    $idcity=$branch['idcity'];
    $empnu=mt_rand(5,50); // from 5 to 50 employees per branch

    $db->from('employees')->where('idbranch=?',['i',$idbranch])->delete();
    
    $chaos = new ChaosMachineOne();
    $chaos->debugMode = true;
    $chaos->setDb($db);
    
    $chaos->table('Employees', $empnu)
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
        ])->field('randomnumber', 'int', 'local')
        ->setArray('name_arr', array_merge(PersonContainer::$firstNameMale, PersonContainer::$firstNameFemale),
            'fakebell')->setArray('lastname_arr', PersonContainer::$lastName, 'fakebell')
        ->field('idEmployee', 'int', 'identity')->isnullable(true)->field('fullName', 'string', 'database', '', 0, 200)
        ->field('email', 'string', 'database', '', 0, 45)->field('password', 'string', 'database', '', 0, 64)
        ->field('idBranch', 'int', 'database')->field('idRole', 'int', 'database')->field('enabled', 'int', 'database')
        ->field('idCity', 'int', 'database')->field('address', 'string', 'database', '', 0, 200)
        ->field('firstName', 'string', 'local')->field('lastName', 'string', 'local')
        ->field('lastUpdate', 'datetime', 'database', new DateTime('now'))
        ->setArrayFromDBTable('array_idBranch', 'Branches', 'idBranch', 'fakebell3')
        //->setArrayFromDBTable('array_idCity', 'Cities', 'idCity', 'fakebell3')
        ->setArrayFromDBTable('array_idRole', 'Roles', 'idRole', 'rightbias')
        ->gen('when always set firstName.value=randomarray("name_arr")')
        ->gen('when always set lastName.value=randomarray("lastname_arr")')
        ->gen('when always set randomnumber.value=random(1950,2005)')
        ->gen('when always set idBranch.value='.$idbranch)
        ->gen('when always set idCity.value='.$idcity) // they live in the samcity city than the branch
        ->gen('when always set idRole.value=randomarray("array_idRole")')
        //->gen('when always set idEmployee.value=random(1,100,1,10,10)')
        ->gen('when always set fullName.value=randomformat("nameformat")')
        ->gen('when always set email.value=randomformat("emailformat")')
        ->gen('when always set password.value=randommask("########")')
        ->gen('when always set enabled.value=random(0,1,1,10,80)')
        ->gen('when always set address.value=randommaskformat("dirformat")')
        ->gen('when always set lastUpdate.speed=random(3600,86400)')
        ->setInsert(false)
        ->showTable([
            'idEmployee',
            'fullName',
            'email',
            'password',
            'idBranch',
            'idRole',
            'enabled',
            'idCity',
            'address',
            'lastUpdate'
        ], true)->run(true);
    
    // management (at least 1 management)
    $chaos = new ChaosMachineOne();
    $chaos->debugMode = true;
    $chaos->setDb($db);

    $chaos->table('Employees', 1)
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
        ])->field('randomnumber', 'int', 'local')
        ->setArray('name_arr', array_merge(PersonContainer::$firstNameMale, PersonContainer::$firstNameFemale),
            'fakebell')->setArray('lastname_arr', PersonContainer::$lastName, 'fakebell')
        ->field('idEmployee', 'int', 'identity')->isnullable(true)->field('fullName', 'string', 'database', '', 0, 200)
        ->field('email', 'string', 'database', '', 0, 45)->field('password', 'string', 'database', '', 0, 64)
        ->field('idBranch', 'int', 'database')->field('idRole', 'int', 'database')->field('enabled', 'int', 'database')
        ->field('idCity', 'int', 'database')->field('address', 'string', 'database', '', 0, 200)
        ->field('firstName', 'string', 'local')->field('lastName', 'string', 'local')
        ->field('lastUpdate', 'datetime', 'database', new DateTime('now'))
        ->setArrayFromDBTable('array_idBranch', 'Branches', 'idBranch', 'fakebell3')
        //->setArrayFromDBTable('array_idCity', 'Cities', 'idCity', 'fakebell3')
        ->setArrayFromDBTable('array_idRole', 'Roles', 'idRole', 'rightbias')
        ->gen('when always set firstName.value=randomarray("name_arr")')
        ->gen('when always set lastName.value=randomarray("lastname_arr")')
        ->gen('when always set randomnumber.value=random(1950,2005)')
        ->gen('when always set idBranch.value='.$idbranch)
        ->gen('when always set idCity.value='.$idcity) // they live in the samcity city than the branch
        ->gen('when always set idRole.value=1') // management
        //->gen('when always set idEmployee.value=random(1,100,1,10,10)')
        ->gen('when always set fullName.value=randomformat("nameformat")')
        ->gen('when always set email.value=randomformat("emailformat")')
        ->gen('when always set password.value=randommask("########")')
        ->gen('when always set enabled.value=random(0,1,1,10,80)')
        ->gen('when always set address.value=randommaskformat("dirformat")')
        ->gen('when always set lastUpdate.speed=random(3600,86400)')
        ->setInsert(false)
        ->showTable([
            'idEmployee',
            'fullName',
            'email',
            'password',
            'idBranch',
            'idRole',
            'enabled',
            'idCity',
            'address',
            'lastUpdate'
        ], true)->run(true);

    // management (at least 1 salesman)
    $chaos = new ChaosMachineOne();
    $chaos->debugMode = true;
    $chaos->setDb($db);

    $chaos->table('Employees', 1)
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
        ])->field('randomnumber', 'int', 'local')
        ->setArray('name_arr', array_merge(PersonContainer::$firstNameMale, PersonContainer::$firstNameFemale),
            'fakebell')->setArray('lastname_arr', PersonContainer::$lastName, 'fakebell')
        ->field('idEmployee', 'int', 'identity')->isnullable(true)->field('fullName', 'string', 'database', '', 0, 200)
        ->field('email', 'string', 'database', '', 0, 45)->field('password', 'string', 'database', '', 0, 64)
        ->field('idBranch', 'int', 'database')->field('idRole', 'int', 'database')->field('enabled', 'int', 'database')
        ->field('idCity', 'int', 'database')->field('address', 'string', 'database', '', 0, 200)
        ->field('firstName', 'string', 'local')->field('lastName', 'string', 'local')
        ->field('lastUpdate', 'datetime', 'database', new DateTime('now'))
        ->setArrayFromDBTable('array_idBranch', 'Branches', 'idBranch', 'fakebell3')
        //->setArrayFromDBTable('array_idCity', 'Cities', 'idCity', 'fakebell3')
        ->setArrayFromDBTable('array_idRole', 'Roles', 'idRole', 'rightbias')
        ->gen('when always set firstName.value=randomarray("name_arr")')
        ->gen('when always set lastName.value=randomarray("lastname_arr")')
        ->gen('when always set randomnumber.value=random(1950,2005)')
        ->gen('when always set idBranch.value='.$idbranch)
        ->gen('when always set idCity.value='.$idcity) // they live in the samcity city than the branch
        ->gen('when always set idRole.value=4') // salesman
        //->gen('when always set idEmployee.value=random(1,100,1,10,10)')
        ->gen('when always set fullName.value=randomformat("nameformat")')
        ->gen('when always set email.value=randomformat("emailformat")')
        ->gen('when always set password.value=randommask("########")')
        ->gen('when always set enabled.value=random(0,1,1,10,80)')
        ->gen('when always set address.value=randommaskformat("dirformat")')
        ->gen('when always set lastUpdate.speed=random(3600,86400)')
        ->setInsert(false)
        ->showTable([
            'idEmployee',
            'fullName',
            'email',
            'password',
            'idBranch',
            'idRole',
            'enabled',
            'idCity',
            'address',
            'lastUpdate'
        ], true)->run(true);
}