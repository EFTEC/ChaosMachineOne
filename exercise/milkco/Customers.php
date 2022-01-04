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

    $chaos->table('Customers', $empnu)
        ->setDb($db)
        ->setFormat('nameformat', ['{{firstName}} {{lastName}}'])
        ->setFormat('dirformat', Address::$streetAddressFormats)
        ->setArray('buildingNumber', Address::$buildingNumber)
        ->setArray('streetName', Address::$streetNames)
        ->setArray('secondaryAddress', Address::$secondaryAddressFormats)

        ->setFormat('companyformat', CompanyContainer::$formats)
        ->setArray('nameCompany', CompanyContainer::$nameCompany)
        ->setArray('companySuffix', CompanyContainer::$companySuffix)
        ->setArray('emaildomain', PersonContainer::$domains)
        ->setFormat('emailformat', [
            '{{firstName}}.{{lastName}}@{{emaildomain}}',
            '{{firstName}}_{{lastName}}@{{emaildomain}}',
            '{{lastName}}{{firstName}}@{{emaildomain}}',
            '{{lastName}}.{{firstName}}@{{emaildomain}}',
            '{{firstName}}{{randomnumber}}@{{emaildomain}}',
            '{{firstName}}.{{lastName}}{{randomnumber}}@{{emaildomain}}'
        ])
        ->field('randomnumber', 'int', 'local')
        ->setArray('sex_array',['female'=>68,'male'=>30,'na'=>2])
        ->setArray('male_firstname_array',PersonContainer::$firstNameMale)
        ->setArray('female_firstname_array',PersonContainer::$firstNameFemale)
        ->setArray('lastname_array',PersonContainer::$lastName)
        //->setArray('name_arr', array_merge(PersonContainer::$firstNameMale, PersonContainer::$firstNameFemale), 'fakebell')
        //->setArray('lastname_arr', PersonContainer::$lastName, 'fakebell')
        ->field('idCustomer', 'int', 'identity', 0)
        //->field('fullName', 'string', 'database', '', 0, 200)
        ->field('address', 'string', 'database', '', 0, 200)
        ->field('idCity', 'int', 'database')
        ->field('email', 'string', 'database', '', 0, 45)
        ->field('isBusiness', 'int', 'database')
        ->field('password', 'string', 'database', '', 0, 64)
        ->field('lastUpdate', 'datetime', 'database', DateTime::createFromFormat('Y-m-d h:i:s', '2010-01-01 00:00:00'))
        ->field('firstName', 'string', 'database','',0,200)->allowNull()
        ->field('lastName', 'string', 'database','',0,200)->allowNull()
        ->field('age','int','database',0,0,80)->allowNull()
        ->field('sex','string','database','na',0,20)->allowNull()
        ->setArrayFromDBTable('array_idCity', 'Cities', ['idCity' => 'population'],'leftbias','idcountry='.$idcountry,' order by population')
        ->gen('when always set sex.value=randomarray("sex_array")')
        ->gen('when always set isBusiness.value=random(0,1,1,100,10,10)')
        ->gen('when sex.value="male" 
        set firstName.value=randomarray("male_firstname_array") 
        else firstName.value=randomarray("female_firstname_array")')
        ->gen('when always set randomnumber.value=random(1940,2020,1)')
        ->gen('when always set age.value=random(15,80,1,20,40,60,20,10)')
        ->gen('when always set lastName.value=randomarray("lastname_array")')
        ->gen('when always set idCity.value=randomarray("array_idCity")')
        ->gen('when always set address.value=randommaskformat("dirformat")')
        ->gen('when always set email.value=randomformat("emailformat")')
        ->gen('when isBusiness=1 set firstName.value=randomformat("companyformat") and lastName.value="" and sex.value="na"')
        ->gen('when always set password.value=randommask("###-###-###")')
        ->gen('when always set lastUpdate.speed=random(3600,86400)')
        ->setInsert(false)
        ->showTable(['idCustomer', 'firstName','lastName','sex', 'address', 'idCity', 'email', 'isBusiness', 'password', 'lastUpdate'],
            true)->run(true);
}