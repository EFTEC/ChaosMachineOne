<?php
/**
 * @deprecated 
 */
use eftec\chaosmachineone\ChaosMachineOne;
use eftec\minilang\MiniLang;
include "../vendor/autoload.php";

class ServiceClass {
	function example($field=null) {
		echo "dummy $field";
		return "dummy $field<br>";
	}
}

// seed with microseconds
function make_seed()
{
	list($usec, $sec) = explode(' ', microtime());
	return $sec + $usec * 1000000;
}
srand(make_seed());
$randval = rand();




$x1='when a1=a2 and $b2=c3 and c2.field!=40 then d2()="123" , $d2.fff="123" , d2()=\'123\'';
$x1='when $g1=a2';
$x1='set field1 = 2.4 , counter + 1, field2=-33';
//$x1='set _field1!=rand(2,33)*10 , a=2 , field2.example=2 ';



//$x1='when 1=1 timeout 3600 fulltimeout 123';
// when time.month=2 then field.speed=random(50,200)

$helloworld='hello';

function fn1($caller) {
	return $caller;
}
function fn2($caller,$tmp2) {
	echo "fn2:";
	var_dump($caller);
}

//$x1='when wait';
$area=['timeout','fulltimeout'];
$miniLang=new MiniLang(['wait'],$area,new ServiceClass());
$miniLang->separate($x1);



class Tmp {
	function timeout($v) {
		echo "timeout $v";
		return true;
	}
	function wait() {
		echo "wait";
	}
	function m1() {
		echo "m1:";
		var_dump(func_get_args());
	}
	function rand($i1,$i2) {
		echo "rand";
		return rand($i1,$i2);
	}
}


$caller=new Tmp();

$g1=111;
$dic=['a1'=>1112,'a2'=>111,'field2'=>0,'field1'=>0,'counter'=>0];
var_dump( $miniLang->evalLogic($caller,$dic));
$miniLang->evalSet($caller,$dic);
echo "<hr>";
var_dump($miniLang->areaValue);
//die(1);
echo "dic:";
var_dump($dic);
echo "<pre>when:\n";
var_dump($miniLang->logic);
echo "set:\n";
var_dump($miniLang->set);
echo "</pre>";
echo "<hr>";


//die(1);

$f=new ChaosMachineOne();





//  when date.w=2  then random(20,300)

$f->table('table',5000)
	->join('table2','table.field1=table2.field2')
	->field('local','int','nostore')
		->fill('when time.day>=9 and time.day<=13 set speed=random(1,3) and acceleration=random(1,3)')
		->fill('when everything set speed=random(0,1) and acceleration=random(-1)')
	->field('local=')
	->field('IdField','varchar(200)')
		->switchrandom('local')
			->case(1)
				->random('name')
				->plus(' ')
				->random('surname')
			->case(2)
				->random('prefixname')
				->plus(' ')
				->random('name')
				->plus(' ')
				->random('surname')
		->endswitch()
	->field('Name','varchar(500)')
		->randomAlgo('fullname','always')
	->field('DateCreation','date')
		->randomrange('222','222')
	->field('Desc','varchar(500)')
		->randomin(['aaa','bb','cc'])
	->field('Sell','int')
		->sine(10) // 30 cicles per the whole round
		->multiply(30)
		->plus(20,30)
		->if('table2.field = 4')
			->set('male')
		->else()
			->set('female')
		->endif()
	->field('Counter',"int")
		->start(200)
		->speed(500)
		->if('row > 200')
			->acceleration(5)->random(0,3)
		->else()
			->acceleration(-5)->random(0,3)
		->endif()
		->if('date.week = 1') //week of the date
			->acceleration(5)->random(0,3)
		->if('date.week = 2') //week of the date
			->acceleration(5)->random(0,3)	
		->else()
			->acceleration(-5)->random(0,3)
		->endif()	
	->insert();
	
	
	
	
	