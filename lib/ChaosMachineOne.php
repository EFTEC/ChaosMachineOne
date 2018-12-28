<?php
namespace eftec\chaosmachineone;
use eftec\DaoOne;
use eftec\minilang\MiniLang;
/**
 * Class ChaosMachineOne
 * @package eftec\chaosmachineone
 * @author   Jorge Patricio Castro Castillo <jcastro arroba eftec dot cl>
 * @version 1.2 2018-12-28
 * @link https://github.com/EFTEC/ChaosMachineOne
 * @license LGPL v3 (or commercial if it's licensed)
 */
class ChaosMachineOne
{
	private $dictionary=[];
	var $debugMode=false;
	private $pipeFieldName=null;
	private $pipeFieldType=null;
	private $pipeFieldTypeSize=null;
	private $pipeFieldSpecial=null;
	private $pipeValue=null;
	/** @var MiniLang */
	private $miniLang;
	/** @var DaoOne */
	private $db=null;
	private $arrays=[];
	private $arraysProportional=[];
	private $formats=[];
	private $formatsProportional=[];
	private $table;
	private $maxId;
	private $daysWeek=['','monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
	private $bodyLoad=false;
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

	/**
	 * @param string $index
	 * @return ChaosField|mixed
	 */
	public function getDictionary($index)
	{
		return $this->dictionary[$index];
	}

	/**
	 * @param string $index
	 * @param mixed|ChaosField $values
	 * @return ChaosMachineOne
	 */
	public function setDictionary($index, $values)
	{
		$this->dictionary[$index] = $values;
		return $this;
	}
	
	
	/**
	 * @param DaoOne $db
	 * @return ChaosMachineOne
	 */
	public function setDb($db) {
		$this->db=$db;
		return $this;
	}
	public function gen($script) {
		$this->miniLang->separate($script);
		return $this;
	}
	public function setArray($name, $value=[]) {
		reset($value);
		$first_key = key($value);
		if(is_numeric($first_key)) {
			if (isset($this->arrays[$name])) {
				trigger_error("arrays[$name] is already defined");
			}
			$this->arrays[$name] = $value;
			$this->arraysProportional[$name] = null;
		} else {
			//it's a associative array. The value is the proportion
			//[a=>10,b=>20,c=>30] => [a,b,c] [10,30,60] (0..9=a,10..29=b,30..60=c)
			$this->arraysProportional[$name]=[];
			$this->arrays[$name]=[];
			$sum=0;
			foreach($value as $k=>$v) {
				$sum+=$v;
				$this->arrays[$name][]=$k;
				$this->arraysProportional[$name][]=$sum;
			}
		}
		return $this;
	}
	public function setFormat($name, $value=[]) {
		reset($value);
		$first_key = key($value);
		if(is_numeric($first_key)) {
			if (isset($this->formats[$name])) {
				trigger_error("formats[$name] is already defined");
			}
			$this->formats[$name] = $value;
			$this->formatsProportional[$name] = null;
		} else {
			$this->formatsProportional[$name]=[];
			$this->formats[$name]=[];
			$sum=0;
			foreach($value as $k=>$v) {
				$sum+=$v;
				$this->formats[$name][]=$k;
				$this->formatsProportional[$name][]=$sum;
			}
		}
		return $this;
	}
	public function cleanAndCut() {
		foreach($this->dictionary as &$obj) {
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
						if ($l<$obj->min && $obj->min>0) $obj->curValue=$obj->curValue.str_repeat(' ',$obj->min-$l);
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
			$this->dictionary['_index'] = $i;
			$this->miniLang->evalAllLogic($this, $this->dictionary, false);
			$this->cleanAndCut();
			foreach($this->dictionary as &$obj) {
				if(is_object($obj) && get_class($obj)=='eftec\chaosmachineone\ChaosField') {
					$obj->reEval();
				}
			}
		}
	}
	public function startBody() {
		if ($this->bodyLoad) return;
		$this->bodyLoad=true;
		echo "<!doctype html>
		<html lang='en'>
		  <head>
		    <meta charset='utf-8'>
		    <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
		    <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css' integrity='sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO' crossorigin='anonymous'>
		    <title>Show table</title>
		  </head>
		  <body><div class='container-fluid>'><div class='row'><div class='col'>";
	}
	public function endBody() {
		echo "</div></div></div></body></html>";
	}
	public function show($cols) {
		$this->startBody();
		echo "<table class='table'>";
		echo "<thead><tr>";
		foreach($cols as $col) {
			echo "<th>".$this->dictionary[$col]->name."</th>";
		}
		echo "</tr></thead>";
		echo "<tbody>";
		for($i=0;$i<$this->maxId;$i++) {
			$this->dictionary['_index']=$i;
			$this->miniLang->evalAllLogic($this, $this->dictionary,false);
			$this->cleanAndCut();
			echo "<tr>\n";
			foreach($cols as $col) {
				echo "<td>";
				switch ($this->dictionary[$col]->type) {
					case 'datetime':
						echo date('Y-m-d H:i:s l(N)', $this->dictionary[$col]->curValue) . "<br>";
						break;
					case 'int':
					case 'decimal':
						echo $this->dictionary[$col]->curValue . "<br>";
						break;
					case 'string':
						echo $this->dictionary[$col]->curValue . "<br>";
						break;
				}
				echo "</td>\n";
				$this->dictionary[$col]->reEval();
			}
			echo "</tr>\n";
		}
		echo "</tbody>\n";
		echo "</table>\n";
		return $this;
	}
	/**
	 * Inserts the rows to the database.
	 * @return $this
	 * @throws \Exception
	 */
	public function insert() {
		if ($this->db===null) {
			$this->debug('WARNING: No database is set');
			return $this;
		}
		for($i=0;$i<$this->maxId;$i++) {
			$this->dictionary['_index'] = $i;
			$this->miniLang->evalAllLogic($this, $this->dictionary, false);
			$this->cleanAndCut();
			$arr=[];
			foreach($this->dictionary as &$obj) {
				if(is_object($obj) && get_class($obj)=='eftec\chaosmachineone\ChaosField') {
					$obj->reEval();
					if ($obj->special=='database') {
						if ($obj->type == 'datetime') {
							$arr[$obj->name] = date('Y-m-d H:i:s', $obj->curValue);
						} else {
							$arr[$obj->name] = $obj->curValue;
						}
					}
				}
			}
			$id=$this->db->insert($this->table,$arr);
			$this->debug("Debug: Inserting #$id");
		}
		return $this;
	}
	public function stat() {
		$this->startBody();
		foreach($this->dictionary as &$obj) {
			if(is_object($obj) && get_class($obj)=='eftec\chaosmachineone\ChaosField') {
				echo "<hr>Stat <b>".$obj->name.'</b>:<br>';
				echo "Min:".$obj->statMin."<br>";
				echo "Max:".$obj->statMax."<br>";
				echo "Sum:".$obj->statSum."<br>";
				if($obj->statSum>0) {
					echo "Avg:" . ($obj->statSum / $this->maxId) . "<br>";
				}
			}
		}
	}
	public function debug($msg) {
		if($this->debugMode) echo $msg."<br>";
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
		$this->dictionary[$name]=new ChaosField($name
			,$this->pipeFieldType
			,$this->pipeFieldTypeSize
			,$special
			,$initValue);
		$this->dictionary[$name]->min=$min;
		$this->dictionary[$name]->max=$max;
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
		if ($field->type=='string') {
			$field->curValue .= $v2;
		} else {
			$field->curValue += $v2;
		}
	}
	public function concat(ChaosField $field,$v2=null) {
		$field->curValue .= $v2;
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
	/**
	 * It returns the current timestamp.
	 * @return int
	 */
	public function now() {
		return time();
	}
	/**
	 * It converts a string to a timestamp.
	 * @param $dateTxt
	 * @return false|int
	 */
	public function createDate($dateTxt) {
		return strtotime($dateTxt);
	}
	public function ramp($fromX, $toX, $fromY, $toY) {
		$deltaX=$toX-$fromX; // 0 100 = 100
		$deltaY=$toY-$fromY; // 0 10 = 10
		$idx=$this->dictionary['_index']; // 10
		$idxDelta=$idx-$fromX; // 10-0 = 10
		$value=($deltaY/$deltaX) *$idxDelta + $fromY; // 10/100*10 = 1 200/990 x 100
		return $value;
	}
	public function log($startX,$startY,$scale=1) {
		$idx=$this->dictionary['_index']; // 10
		$idxDelta=$idx-$startX; // 10-0 = 10
		if ($idxDelta==0) {
			$value=$startY;
		} else {
			$value = (log($idxDelta) * $scale) + $startY;
		}
		return $value;
	}
	public function exp($startX,$startY,$scale=1) {
		$idx=$this->dictionary['_index']; // 10
		$idxDelta=$idx-$startX; // 10-0 = 10
		$value = (exp($idxDelta/$scale) ) + $startY;
		return $value;
	}
	public function sin($startX,$startY,$speed=1,$scale=1) {
		$idx=$this->dictionary['_index']; // 10
		$idxDelta=$idx-$startX; // 10-0 = 10
		$value = (sin($idxDelta*0.01745329251*$speed)*$scale ) + $startY;
		return $value;
	}
	public function atan($centerX,$startY,$speed=1,$scale=1) {
		$idx=$this->dictionary['_index']; // 10
		$idxDelta=$idx-$centerX; // 10-0 = 10
		$value = (atan($idxDelta*0.01745329251*$speed)*$scale ) + $startY;
		return $value;
	}
	public function parabola($centerX, $startY, $scaleA=1, $scaleB=1, $scale=1) {
		$idx=$this->dictionary['_index']; // 10
		$idxDelta=$idx-$centerX; // 10-0 = 10
		$value = ($idxDelta*$idxDelta*$scaleA+ $idxDelta*$scaleB)*$scale + $startY;
		return $value;
	}
	public function bell($centerX, $startY, $sigma=1, $scaleY=1) {
		$idx=$this->dictionary['_index']; // 10
		$value = $this->normal($idx,$centerX,$sigma)*$scaleY+ $startY;
		return $value;
	}
	public function normal($x, $mu, $sigma) {
		return exp(-0.5 * ($x - $mu) * ($x - $mu) / ($sigma*$sigma))
			/ ($sigma * sqrt(2.0 * M_PI));
	}
	#endregion
	#region fixed function
	
	const NUM_OPT=[0,1,2,3,4,5,6,7,8,9,''];
	const NUM=[0,1,2,3,4,5,6,7,8,9,' '];
	const ALPHA=['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
	const ALPHA_OPT=['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',''];
	public function randommask($mask,$arrayName='') {
		$txt='';
		$c=strlen($mask);
		$escape=false;
		for($i=0;$i<$c;$i++) {
			$m=$mask[$i];
			if(!$escape) {
				switch ($m) {
					case '#':
						$txt .= $this->randMiniArr(self::NUM_OPT);
						break;
					case '0':
						$txt .= $this->randMiniArr(self::NUM);
						break;
					case 'u':
						$txt .= $this->randMiniArr(self::ALPHA);
						break;
					case 'l':
						$txt .= strtolower($this->randMiniArr(self::ALPHA));
						break;
					case 'x':
						$txt .= strtolower($this->randMiniArr(self::ALPHA_OPT));
						break;
					case 'X':
						$txt .= $this->randMiniArr(self::ALPHA_OPT);
						break;
					case '\\':
						$escape = true;
						break;
					case '?':
						$txt .= $this->randomarray($arrayName);
						break;
					default:
						$txt .= $m;
				}
			} else {
				$txt .= $m;
				$escape = false;
			}
		}
		return $txt;
	}
	private function randMiniArr($arr) {
		$c=count($arr)-1;
		return $arr[rand(0,$c)];
	}
	
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
		if (!isset($this->arrays[$arrayName])) {
			trigger_error("Array [$arrayName] not defined");
			return "";
		}
		if ($this->arraysProportional[$arrayName]!=null) {
			$ap=$this->arraysProportional[$arrayName];
			$max=end($ap);
			$idPos=rand(0,$max);
			$idx=0;
			foreach($ap as $k=>$v) {
				if($idPos<$v) {
					$idx=$k;
					break;
				}
			}
		} else {
			$c = count($this->arrays[$arrayName]);
			$idx = rand(0, $c - 1);
		}
		if ($fieldName == null) {
			return $this->arrays[$arrayName][$idx];
		} else {
			return $this->arrays[$arrayName][$idx]->{$fieldName};
		}
	}
	public function randomformat($formatName) {
		if (!isset($this->formats[$formatName])) {
			trigger_error("Format [$formatName] not defined");
			return "";
		}
		if ($this->formatsProportional[$formatName]!=null) {
			$ap=$this->formatsProportional[$formatName];
			$max=end($ap);
			$idPos=rand(0,$max);
			$idx=0;
			foreach($ap as $k=>$v) {
				if($idPos<$v) {
					$idx=$k;
					break;
				}
			}
			$format=$this->formats[$formatName][$idx];
		} else {
			$c = count($this->formats[$formatName]);
			$idx = rand(0, $c - 1);
			$format=$this->formats[$formatName][$idx];
		}
		return $this->parse($format);
	}
	public function parse($string)
	{
		return preg_replace_callback('/\{\{\s?(\w+)\s?\}\}/u', array($this, 'callRandomArray'), $string);
	}
	protected function callRandomArray($matches)
	{
		return $this->randomarray($matches[1]);
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
	public function arrayIndex($nameArray)
	{
		$idx=$this->dictionary['_index'];
		return $this->arrays[$nameArray][$idx];
	}
	public function random($from,$to,$jump=1,$prob0=null,$prob1=null,$prob2=null) {
		$r='';
		switch ($this->pipeFieldType) {
			case '':
			case "datetime":
			case 'int':
			case 'decimal':
				$segment=$this->getRandomSegment($prob0,$prob1,$prob2);
				if($segment===null) {
					$r=rand($from/$jump,$to/$jump)*$jump;
				} else {
					$delta=($to-$from)/3; // 12-24 12(delta=4) = 0+12..3+12 ,4+12..7+12,8+12..12+12
					$init=$segment*$delta +$from;
					$end=($segment+1)*$delta-1 +$from;
					$r=rand($init/$jump,$end/$jump)*$jump;
				}
				$this->pipeValue+=$r;
				break;
			default:
				trigger_error('random type ['.$this->pipeFieldType.'] not defined');
		}
		return $r;
	}
	private function getRandomSegment($prob0=null,$prob1=null,$prob2=null) {
		if($prob0===null) return null;
		$segment=rand(0,$prob0+$prob1+$prob2);
		if($segment<=$prob0) return 0;
		if($segment<=$prob0+$prob1) return 1;
		return 2;
	}
	#endregion
	public function endPipe() {
		return $this;
	}
}