<?php
use eftec\chaosmachineone\ChaosMachineOne;

include_once 'common.php';


$roles=[
    [ 'idRole'=>1,  'name'=>'Manager',  'monthlySalary'=>10000.00],
    [ 'idRole'=>2,  'name'=>'Executive',  'monthlySalary'=>8000.00],
    [ 'idRole'=>3,  'name'=>'Technician',  'monthlySalary'=>7000.00],
    [ 'idRole'=>4,  'name'=>'Sales',  'monthlySalary'=>6000.00],
    [ 'idRole'=>5,  'name'=>'Administrative',  'monthlySalary'=>5000.00],
    [ 'idRole'=>6,  'name'=>'Operator',  'monthlySalary'=>4000.00],
    [ 'idRole'=>7,  'name'=>'Staff',  'monthlySalary'=>3000.00],
];


if($db->from('Roles')->count()>0) {
    echo "table Roles skipped<br>";
} else {
    echo "inserting Roles<br>";
    foreach($roles as $role) {
        $db->insert('Roles',$role);
    }
    echo "inserting Roles OK<br>";
}
