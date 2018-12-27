<?php
namespace eftec\chaosmachineone;

use eftec\minilang\MiniLang;

/**
 * Class ChaosMachineOne
 * @package eftec\chaosmachineone
 * @author   Jorge Patricio Castro Castillo <jcastro arroba eftec dot cl>
 * @version 1.1 2018-12-26
 * @link https://github.com/EFTEC/ChaosMachineOne
 * @license LGPL v3 (or commercial if it's licensed)
 */
class ChaosMachineOne
{
	var $pipeFieldName=null;
	var $pipeFieldType=null;
	var $pipeFieldTypeSize=null;
	var $pipeFieldSpecial=null;
	var $pipeValue=null;
	/** @var MiniLang */
	var $miniLang;
	
	var $values=[];
	
	private $arrays=[];
	private $formats=[];
	
	var $table;
	var $maxId;
	
	private $daysWeek=['','monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

	/**
	 * ChaosMachineOne constructor.
	 */
	public function __construct()
	{
		$this->reset();
		$this->miniLang=new MiniLang(['always'],[],$this);
	}
	// special
	public function always() {
		return true;
	}
	
	public function gen($script) {
		$this->miniLang->separate($script);
		return $this;
	}
	public function setArray($name, $value=[]) {
		if(isset($this->arrays[$name])) {
			trigger_error("arrays[$name] is already defined");
		}
		$this->arrays[$name]=$value;
		return $this;
	}
	public function setFormat($name, $value=[]) {
		if(isset($this->formats[$name])) {
			trigger_error("formats[$name] is already defined");
		}
		$this->formats[$name]=$value;
		return $this;
	}
	public function cleanAndCut() {
		foreach($this->values as &$obj) {
			if(is_object($obj) && get_class($obj)=='eftec\chaosmachineone\ChaosField') {
				switch ($obj->type) {
					case 'int':
						$obj->curValue=round($obj->curValue,0);
						if ($obj->curValue<$obj->min) $obj->curValue=$obj->min;
						if ($obj->curValue>$obj->max) $obj->curValue=$obj->max;
						break;
					case 'decimal':
						if ($obj->curValue<$obj->min) $obj->curValue=$obj->min;
						if ($obj->curValue>$obj->max) $obj->curValue=$obj->max;
						break;
					case 'string':
						$l=strlen($obj->curValue);
						if ($l<$obj->min && $obj->min>0) $obj->curValue=$obj->min.str_repeat(' ',$obj->min-$l);
						if ($l>$obj->max) $obj->curValue= $this->trimText($obj->curValue,$obj->max);
						break;
				}
			}
		}
	}
	private function trimText($txt,$l) {
		$txt=substr($txt,0,$l);
		$pLast=strrpos($txt,' ');
		if($pLast!==false) {
			$txt=substr($txt,0,$pLast).'.';
		}
		return $txt;
	}
	public function run() {
		for($i=0;$i<$this->maxId;$i++) {
			$this->values['_index'] = $i;
			$this->miniLang->evalAllLogic($this, $this->values, false);
			$this->cleanAndCut();
			foreach($this->values as &$obj) {
				if(is_object($obj) && get_class($obj)=='eftec\chaosmachineone\ChaosField') {
					$obj->reEval();
				}
			}
		}
	}
	public function show($cols) {
		for($i=0;$i<$this->maxId;$i++) {
			$this->values['_index']=$i;
			$this->miniLang->evalAllLogic($this, $this->values,false);
			$this->cleanAndCut();
			foreach($cols as $col) {
				echo $this->values[$col]->name.": ";
				switch ($this->values[$col]->type) {
					case 'datetime':
						echo date('Y-m-d H:i:s l(N)', $this->values[$col]->curValue) . "<br>";
						break;
					case 'int':
					case 'decimal':
						echo $this->values[$col]->curValue . "<br>";
						break;
					case 'string':
						echo $this->values[$col]->curValue . "<br>";
						break;
				}
				//echo date('Y-m-d H:i:s l(N)',$this->values[$col]->curValue)."<br>";

				/*echo $this->values['idtable']->curValue
					.",speed:".$this->values['idtable']->curSpeed
					.",accel:".$this->values['idtable']->curAccel."<br>";*/
				$this->values[$col]->reEval();
			}
		
		}
	}
	public function field($name,$type,$special='database',$initValue=0,$min=-2147483647,$max=2147483647) {
		$this->pipeFieldName=$name;
		if (strpos($type,'(')!==false) {
			$x = explode('(', $type);
			$this->pipeFieldType = $x[0];
			$this->pipeFieldTypeSize = substr($x[1], 0, -1);
		} else {
			$this->pipeFieldType=$type;

			$this->pipeFieldTypeSize=0;
		}
		$this->pipeFieldSpecial=$special;
		$this->values[$name]=new ChaosField($name
			,$this->pipeFieldType
			,$this->pipeFieldTypeSize
			,$special
			,$initValue);
		$this->values[$name]->min=$min;
		$this->values[$name]->max=$max;
		return $this;
	}


	public function speed(ChaosField $field,$v2=null) {
		$field->curSpeed=$v2;
	}
	public function accel(ChaosField $field,$v2=null) {
		$field->curAccel=$v2;
	}
	public function stop(ChaosField $field,$v2=null) {
		$field->curSpeed=0;
		$field->curAccel=0;
		$field->curValue=$v2;
	}
	public function add(ChaosField $field,$v2=null) {
		if ($field->type=='datetime' && !is_numeric($v2)) {
			$last=substr($v2,-1);
			$number=substr($v2,0,-1);
			switch ($last) {
				case 'h':
					$field->curValue+=$number*3600; // hours
					break;
				case 'm':
					$field->curValue+=$number*60; // hours
					break;
				case 'd':
					$field->curValue+=$number*86400; // days
					break;
				default:
					trigger_error("add type not defined [$last] for datetime ");
			}
			return;
		}
		$field->curValue+=$v2;
	}
	public function plus(ChaosField $field,$v2=null) {
		$this->add($field,$v2);
	}
	public function value(ChaosField $field,$v2=null) {
		$field->curValue=$v2;
	}
	public function getvalue(ChaosField $field) {
		return $field->curValue;
	}
	public function valueabs(ChaosField $field) {
		$field->curValue=abs($field->curValue);
	}
	public function year(ChaosField $field) {
		return $this->datepart($field,'Y');
	}
	public function month(ChaosField $field) {
		return $this->datepart($field,'m');
	}
	public function day(ChaosField $field) {
		return $this->datepart($field,'d');
	}
	public function weekday(ChaosField $field) {
		// 1= monday, 7=sunday
		return $this->datepart($field,'N');
	}
	public function hour(ChaosField $field) {
		// hour (24 hours)
		return $this->datepart($field,'H');
	}
	public function minute(ChaosField $field) {
		// hour (24 hours)
		return $this->datepart($field,'i');
	}
	public function second(ChaosField $field) {
		// hour (24 hours)
		return $this->datepart($field,'s');
	}
	public function datepart(ChaosField $field,$v2=null) {
		//Y-m-d H:i:s
		return intval(date($v2,$field->curValue));
	}

	/**
	 * @param ChaosField $field
	 * @param string $v2=['day','hour','monday','tuesday','wednesday','thursday','friday','saturday','sunday','month'][$i]
	 */
	public function skip(ChaosField $field,$v2='day') {
		switch ($v2) {
			case "day":
				$curhour=$this->hour($field);
				$curhour=($curhour==0)?24:$curhour;
				$field->curValue+=(24-$curhour)*3600; // we added the missing hours.
				break;
			case "hour":
				$curMinute=$this->minute($field);
				$curMinute=($curMinute==0)?60:$curMinute;
				$field->curValue+=(60-$curMinute)*60; // we added the missing minutes.
				break;
			case "month":
				$curMonth=$this->month($field);
				$curhour=$this->hour($field);
				$curhour=($curhour==0)?24:$curhour;
				$field->curValue+=(24-$curhour)*3600 - $this->minute($field)*60 - $this->second($field); // we added the missing hours and we are close to midnight.
				if($this->month($field)==$curMonth) {
					for ($i = 0; $i < 31; $i++) {
						$field->curValue+=86400; // we add a day.
						if($this->month($field)!=$curMonth) {
							break;
						}
					}
				}
				break;
			case "monday":
			case "tuesday":
			case "wednesday":
			case "thursday":
			case "friday":
			case "saturday":
			case "sunday":
				
				$p=array_search($v2,$this->daysWeek); //1 monday

				$curhour=$this->hour($field);
				$curhour=($curhour==0)?24:$curhour;
				$field->curValue+=(24-$curhour)*3600 - $this->minute($field)*60 - $this->second($field); // we added the missing hours and we are close to midnight.
				//echo "skip ".date('Y-m-d H:i:s l(N)',$field->curValue)."<br>";
				$curweek=$this->weekday($field);
				//echo "skipping to $p $curhour curweek $curweek ".((24-$curhour)*3500)."<br>";
				//die(1);
				if($curweek!=$p) {
					$curday=$this->weekday($field);
					$field->curValue+=(7+$p-$curday)*86400; // we added the missing days.
				}
				//echo "skip ".date('Y-m-d H:i:s l(N)',$field->curValue)."<br>";
				break;
		}
	}

	public function reset() {
		$this->pipeFieldName=null;
		$this->pipeFieldType=null;
		$this->pipeFieldTypeSize=null;
		$this->pipeFieldSpecial=null;
		$this->pipeValue=null;
	}

	public function table($table, $maxId)
	{
		$this->table=$table;
		$this->maxId=$maxId;
		return $this;
	}
	#region Range functions
	public function now() {
		return time();
	}
	
	public function ramp($fromX, $toX, $fromY, $toY) {
		$deltaX=$toX-$fromX; // 0 100 = 100
		$deltaY=$toY-$fromY; // 0 10 = 10
		$idx=$this->values['_index']; // 10
		$idxDelta=$idx-$fromX; // 10-0 = 10
		$value=($deltaY/$deltaX) *$idxDelta + $fromY; // 10/100*10 = 1 200/990 x 100
		return $value;
	}
	public function log($startX,$startY,$scale=1) {
		
		$idx=$this->values['_index']; // 10
		$idxDelta=$idx-$startX; // 10-0 = 10
		if ($idxDelta==0) {
			$value=$startY;	
		} else {
			$value = (log($idxDelta) * $scale) + $startY;
		}
		return $value;
	}
	public function exp($startX,$startY,$scale=1) {

		$idx=$this->values['_index']; // 10
		$idxDelta=$idx-$startX; // 10-0 = 10
		$value = (exp($idxDelta/$scale) ) + $startY;
		
		return $value;
	}
	public function sin($startX,$startY,$speed=1,$scale=1) {

		$idx=$this->values['_index']; // 10
		$idxDelta=$idx-$startX; // 10-0 = 10
		$value = (sin($idxDelta*0.01745329251*$speed)*$scale ) + $startY;
		return $value;
	}
	public function atan($centerX,$startY,$speed=1,$scale=1) {

		$idx=$this->values['_index']; // 10
		$idxDelta=$idx-$centerX; // 10-0 = 10
		$value = (atan($idxDelta*0.01745329251*$speed)*$scale ) + $startY;
		return $value;
	}
	public function parabola($centerX, $startY, $scaleA=1, $scaleB=1, $scale=1) {

		$idx=$this->values['_index']; // 10
		$idxDelta=$idx-$centerX; // 10-0 = 10
		$value = ($idxDelta*$idxDelta*$scaleA+ $idxDelta*$scaleB)*$scale + $startY;
		return $value;
	}
	public function bell($centerX, $startY, $sigma=1, $scaleY=1) {

		$idx=$this->values['_index']; // 10
		$value = $this->normal($idx,$centerX,$sigma)*$scaleY+ $startY;
		return $value;
	}

	public function normal($x, $mu, $sigma) {
		return exp(-0.5 * ($x - $mu) * ($x - $mu) / ($sigma*$sigma))
			/ ($sigma * sqrt(2.0 * M_PI));
	}
	#endregion
	
	#region fixed function
	/**
	 * Random proportional
	 * @param array $args . Example (1,2,3,30,30,40), where the changes are 1--30%, 2--30%, 3--40%
	 * @return mixed
	 */
	public function randomprop(...$args) {
		$c=count($args);
		$m=$c/2;
		$sum=0;
		for($i=$m;$i<$c;$i++) {
			$sum+=$args[$i];
		}
		$rnd=rand(0,$sum);
		$counter=$sum;
		for($i=$c-1;$i>=$m;$i--) {
			$counter-=$args[$i];
			if ($rnd>=$counter) {
				return $args[$i-$m];
			}
		}
		return $args[0];
	}
	public function randomarray($arrayName,$fieldName=null) {
		$c=count($this->arrays[$arrayName]);
		$idx=rand(0,$c-1);
		if($fieldName==null) {
			return $this->arrays[$arrayName][$idx];
		} else {
			return $this->arrays[$arrayName][$idx]->{$fieldName};
		}
	}
	public function randomformat($formatName,$fieldName=null) {
		$c=count($this->formats[$formatName]);
		$idx=rand(0,$c-1);
		$format=$this->formats[$formatName][$idx];
		if($fieldName==null) {
			return $this->parse($format);
		} else {
			//return $this->arrays[$formatName][$idx]->{$fieldName};
		}
	}
	public function randomtext($startLorem='Lorem ipsum dolor',$arrayName='',$paragraph=false,$nWordMin=20,$nWordMax=40) {

		$array=$this->arrays[$arrayName];
		if($startLorem!=='') {
			$counter=3;
			$u=false;
			$txt=$startLorem.' ';
		} else {
			$u=true;
			$counter=0;
			$txt='';
		}
		$c=count($array);
		$nWords=rand($nWordMin,$nWordMax);
		for($i=$counter;$i<$nWords;$i++) {
			$r=rand(0,$c-1);
			$newWord=$array[$r];
			$newWord=($u)?ucfirst($newWord):$newWord;
			$txt.=$newWord;
			$r2=rand(0,6);
			$u=false;
			if ($i+3<$nWordMax) {
				$r2=5; // normal word (at the end of the phrase).
			}
			switch ($r2) {
				case 0:
					$txt.='. ';
					if ($paragraph && rand(0,4)==0) {
						$txt.="\n";
					}
					$u=true;
					break;
				case 1:
					$txt.=', ';
					break;
				default:
					$txt.=' ';
			}
		}
		$txt.=trim($txt).'.';
		return $txt;

	}

	public function parse($string)
	{
		return preg_replace_callback('/\{\{\s?(\w+)\s?\}\}/u', array($this, 'callRandomArray'), $string);
	}

	protected function callRandomArray($matches)
	{
		$c=count($this->arrays[$matches[1]]);
		$idx=rand(0,$c-1);
		return $this->arrays[$matches[1]][$idx];
	}
	
	public function random($from,$to,$jump=1) {
		$r='';
		switch ($this->pipeFieldType) {
			case '':
			case "datetime":
			case 'int':
			case 'decimal':
				$r=rand($from/$jump,$to/$jump)*$jump;
				$this->pipeValue+=$r;
				break;
			default:
				trigger_error('random type ['.$this->pipeFieldType.'] not defined');
		}
		
		return $r;
	} 
	
	#endregion
	
	public function endPipe() {
		
		return $this;
	}
}