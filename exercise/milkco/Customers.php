<?php
use eftec\chaosmachineone\ChaosMachineOne;
use Faker\Address;

include 'common.php';

$db->from('customers')->where('1=1')->delete();
 
$branches=$db->runRawQuery(
    'select b.idbranch,b.idcity,c.idcountry from branches b 
        inner join cities c on b.idcity=c.idcity order by idbranch',[],true);

foreach($branches as $branch) {

    $idbranch=$branch['idbranch'];
    $idcity=$branch['idcity'];
    $idcountry=$branch['idcountry'];

    $chaos = new ChaosMachineOne();

    $empnu=$chaos->random(0,150,1,1,10,5); // from 0 to 150 customers per branch
    
    $chaos->debugMode = true;
    $chaos->setDb($db);    
    
    $chaos->table('Customers', $empnu)->setDb($db)->setFormat('nameformat', ['{{firstName}} {{lastName}}'])

        ->setFormat('dirformat', Address::$streetAddressFormats)
        ->setArray('buildingNumber', Address::$buildingNumber)
        ->setArray('streetName', Address::$streetNames)
        ->setArray('secondaryAddress', Address::$secondaryAddressFormats)        
        
        ->setFormat('companyformat', CompanyContainer::$formats)
        ->setArray('nameCompany', CompanyContainer::$nameCompany)
        ->setArray('companySuffix', CompanyContainer::$companySuffix)
        ->setArray('emaildomain', PersonContainer::$domains)->setFormat('emailformat', [
            '{{firstName}}.{{lastName}}@{{emaildomain}}',
            '{{firstName}}_{{lastName}}@{{emaildomain}}',
            '{{lastName}}{{firstName}}@{{emaildomain}}',
            '{{lastName}}.{{firstName}}@{{emaildomain}}',
            '{{firstName}}{{randomnumber}}@{{emaildomain}}',
            '{{firstName}}.{{lastName}}{{randomnumber}}@{{emaildomain}}'
        ])->field('randomnumber', 'int', 'local')
        ->setArray('name_arr', array_merge(PersonContainer::$firstNameMale, PersonContainer::$firstNameFemale),
            'fakebell')->setArray('lastname_arr', PersonContainer::$lastName, 'fakebell')
        ->field('idCustomer', 'int', 'identity', 0)->field('fullName', 'string', 'database', '', 0, 200)
        ->field('address', 'string', 'database', '', 0, 200)->field('idCity', 'int', 'database')
        ->field('email', 'string', 'database', '', 0, 45)->field('isBusiness', 'int', 'database')
        ->field('password', 'string', 'database', '', 0, 64)
        ->field('lastUpdate', 'datetime', 'database', new DateTime('now'))->field('firstName', 'string', 'local')
        ->field('lastName', 'string', 'local')
        ->setArrayFromDBTable('array_idCity', 'Cities', ['idCity' => 'population'],'leftbias','idcountry='.$idcountry,' order by population')
        ->gen('when always set isBusiness.value=random(0,1,1,100,10,10)')
        ->gen('when always set firstName.value=randomarray("name_arr")')
        ->gen('when always set lastName.value=randomarray("lastname_arr")')
        ->gen('when always set idCity.value=randomarray("array_idCity")')
        ->gen('when always set address.value=randommaskformat("dirformat")')
        ->gen('when always set email.value=randomformat("emailformat")')
        ->gen('when isBusiness=0 set fullName.value=randomformat("nameformat")')
        ->gen('when isBusiness=1 set fullName.value=randomformat("companyformat")')
        ->gen('when always set password.value=randommask("########")')
        ->gen('when always set lastUpdate.speed=random(3600,86400)')
        ->setInsert(false)
        ->showTable(['idCustomer', 'fullName', 'address', 'idCity', 'email', 'isBusiness', 'password', 'lastUpdate'],
            true)->run(true);
}