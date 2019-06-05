<?php
namespace eftec\chaosmachineone;
use eftec\PdoOne;
use eftec\minilang\MiniLang;
use Exception;
use PDO;


/**
 * Class ChaosMachineOne
 * @package eftec\chaosmachineone
 * @author   Jorge Patricio Castro Castillo <jcastro arroba eftec dot cl>
 * @version 1.5 2019-05-21
 * @link https://github.com/EFTEC/ChaosMachineOne
 * @license LGPL v3 (or commercial if it's licensed)
 */
class ChaosMachineOne
{
	private $dictionary=[];
	private $dictionaryProportional=[];
	var $debugMode=false;
	private $pipeFieldName=null;
	private $pipeFieldType=null;
	private $pipeFieldTypeSize=null;
	private $pipeFieldSpecial=null;
	private $pipeValue=null;
	private $showTable=false;
	private $tableCols=[];
	/** @var MiniLang */
	private $miniLang;
	/** @var PdoOne */
	private $db=null;

	
	private $formats=[];
	private $formatsProportional=[];
	private $table;
	private $maxId;
	private $queryTable="";
	private $queryPrefix='origin_';
	private $daysWeek=['','monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
	private $bodyLoad=false;
	
	/** @var array that stores the results method insert() */
	private $cacheResult=null;
	/**
	 * ChaosMachineOne constructor.
	 */
	public function __construct()
	{
		$this->reset();
		$this->miniLang=new MiniLang($this,$this->dictionary, ['always'],[],$this);
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
	 * @param PdoOne $db
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

	/**
	 * Set an array that we could use it later:
	 * <p>->setArray('drinks',['cocacola','fanta','sprite'])</p>
	 * <p>->setArray('drinks',['cocacola'=>80,'fanta'=>10,'sprite'=>10]); <br>// cocacola 80% prob, fanta 10%, sprite 10%. The total could be any number</p>
	 * <p>->setArray('drinks',['cocacola','fanta','sprite','seven up'],[80,30])</p>
	 * @param string $name name of the array. If the array exists then it returns an error.
	 * @param array $value If it is an associative array then the value indicates the probability of election.
	 * @param array|string $probability.(array,'increase','decrease') It is another alternative to set an the probabilities. It is only used for non-associative array
	 * <p>[60,30,10..] It sets 60% of chance for the first 1/3 elements, 20% for the second 1/3 and 10 for the last 1/3</p>
	 * @return $this
	 */
	public function setArray($name, $value=[],$probability=[1]) {
		reset($value);
		$first_key = key($value);
		if(is_numeric($first_key)) {
			// It is not an associative array so we converted into an associative array
			if (isset($this->dictionary[$name])) {
				trigger_error("arrays[$name] is already defined");
			}
			//$this->dictionary[$name] = $value;
			//$this->dictionaryProportional[$name] = null;
			$numValue=count($value);
			$tmp=$value;
			if (is_string($probability)) {
				$value=[];
				switch ($probability) {
					case 'increase':
						foreach($tmp as $k=>$v) {
							$value[$v]=$k+1;
						}
						break;
					
					case 'decrease':
						foreach($tmp as $k=>$v) {
							$value[$v]=$numValue+1-$k;
						}
						break;
					default:
						trigger_error('probability not defined');
				}
			} else {
				$c2=count($probability);
				$cpart=ceil($numValue/$c2);
				if (count($probability)===0) {
					$probability=[1];
				}
				$value=[];
				$probCounter=-1;
				foreach($tmp as $k=>$v) {
					if ($k % $cpart ===0) {
						$probCounter++;
					}
					$value[$v]=$probability[$probCounter];
				}
			}

		}
		//it's a associative array. The value is the proportion
		//[a=>10,b=>20,c=>30] => [a,b,c] [10,30,60] (0..9=a,10..29=b,30..60=c)
		$this->dictionaryProportional[$name]=[];
		$this->dictionary[$name]=[];
		$sum=0;
		foreach($value as $k=>$v) {
			$sum+=$v;
			$this->dictionary[$name][]=$k;
			$this->dictionaryProportional[$name][]=$sum;
		}
	
		return $this;
	}

	/**
	 * @param string $name name of the array
	 * @param string $table name of the table of the database
	 * @param string $column name of the column. It could be a formula but it must be the first column.
	 * @param array $probability
	 * @return ChaosMachineOne
	 */
	public function setArrayFromDBTable($name,$table,$column,$probability=[1]) {
		try {
			$values=$this->db->select($column)->from($table)->toList();
			$result=[];
			foreach($values as $val) {
				$result[]=reset($val); // first element of the array
			}
		} catch (Exception $e) {

			$result=[];
		}

		return $this->setArray($name,$result,$probability);
	}

	/**
	 * Retrusn the first column of a query
	 * Example ->setArrayFromDBQuery('myarray','select * from table');
	 * @param string $name name of the array
	 * @param string $query example: select col from table
	 * @param array $probability
	 * @param null|array $queryParam
	 * @return ChaosMachineOne
	 */
	public function setArrayFromDBQuery($name,$query,$probability=[1],$queryParam=null) {
		try {
			$values=$this->db->runRawQuery($this->parseFormat($query), $queryParam, true);
			$result=[];
			foreach($values as $val) {
				$result[]=reset($val); // first element of the array
			}			
		} catch (Exception $e) {
			$result=[];
		}
		return $this->setArray($name,$result,$probability);
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
	private function isChaosField($obj) {
		return is_object($obj) && get_class($obj)=='eftec\chaosmachineone\ChaosField';
	}

	/**
	 * We limit the results for each constrains defined by the fields.
	 */
	public function cleanAndCut() {
		foreach($this->dictionary as &$obj) {
			if($this->isChaosField($obj)) {
				switch ($obj->type) {
					case 'int':
						if ($obj->allowNull && $obj->curValue===null) {
							$obj->curValue = null;
						} else {
							$obj->curValue = round($obj->curValue, 0);
							if ($obj->curValue < $obj->min) $obj->curValue = $obj->min;
							if ($obj->curValue > $obj->max) $obj->curValue = $obj->max;
						}
						break;
					case 'decimal':
						if ($obj->allowNull && $obj->curValue===null) {
							$obj->curValue = null;
						} else {
							if ($obj->curValue < $obj->min) $obj->curValue = $obj->min;
							if ($obj->curValue > $obj->max) $obj->curValue = $obj->max;
						}
						break;
					case 'string':
						if ($obj->allowNull && $obj->curValue===null) {
							$obj->curValue = null;
						} else {
							$l = strlen($obj->curValue);
							if ($l < $obj->min && $obj->min > 0) $obj->curValue = $obj->curValue . str_repeat(' ', $obj->min - $l);
							if ($l > $obj->max) $obj->curValue = $this->trimText($obj->curValue, $obj->max);
						}
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

	/**
	 * We then the evaluation
	 * @param bool $storeCache
	 * @return ChaosMachineOne
	 */
	public function run($storeCache=false) {
		if ($storeCache) $this->cacheResult=[]; // deleted the cache
		if ($this->showTable) {
			$this->tableHead();
		}
		if($this->queryTable) {
			if ($this->db===null) {
				$this->debug('WARNING: No database is set');
				return $this;
			}
			/** @var \PDOStatement $statement */
			try {
				$statement = $query = $this->db->runRawQuery($this->queryTable, null, false);
			} catch (Exception $e) {
				if($this->debugMode) {
					$this->debug($e->getMessage());
				}
			}
			$maxId=PHP_INT_MAX; // infinite loop
		} else {
			$statement=null;
			$maxId=$this->maxId;
		}
		for($i=0;$i<$maxId;$i++) {
			if($this->queryTable) {
				try {
					$row = $statement->fetch(PDO::FETCH_ASSOC);
				} catch (Exception $e) {
					if($this->debugMode) {
						$this->debug($e->getMessage());
					}
				}
				if ($row===false) {
					break; // break for.
				}
				foreach($row as $key2=>$value2) {
					$this->field($this->queryPrefix.$key2,'string','local',$value2);
					echo "setting ".$this->queryPrefix.$key2." = $value2<br>";
				}

			}
			
			
			$this->dictionary['_index'] = $i;
			$this->miniLang->evalAllLogic(false);
			$this->cleanAndCut();
			if ($storeCache) {
				$tmp=[];
				// clone values (avoid instancing the same objects).
				foreach($this->dictionary as $key => $obj) {
					if(is_object($obj)) {
						$tmp[$key] = clone $obj;
					} else {
						$tmp[$key] = $obj;
					}
				}
				$this->cacheResult[] =$tmp;
			}
			foreach($this->dictionary as &$obj) {
				if($this->isChaosField($obj)) {
					$obj->reEval();
				}
			}
			if ($this->showTable) {
				//$this->dictionary=$tmp;
				$this->tableRow();
			}
		}
		if ($this->showTable) {
			$this->tableFooter();
		}
		return $this;
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
	private function tableHead() {
		$cols=$this->tableCols;
		$this->startBody();
		echo "<table class='table table-striped table-sm'>";
		echo "<thead class='thead-dark'><tr>";
		foreach($cols as $col) {
			if(isset($this->dictionary[$col]->name)) {
				echo "<th>".$this->dictionary[$col]->name."</th>";
			} else {
				echo "<th>($col)</th>";
			}
		}
		echo "</tr></thead>";
		echo "<tbody>";
	}
	private function tableRow() {
		$cols=$this->tableCols;
		echo "<tr>\n";
		foreach($cols as $col) {
			echo "<td>";
			switch ($this->dictionary[$col]->type) {
				case 'datetime':
					echo date('Y-m-d H:i:s l(N)', $this->dictionary[$col]->curValue) . "<br>";
					break;
				case 'int':
				case 'decimal':
					echo $this->showNull($this->dictionary[$col]->curValue);
					break;
				case 'string':
					echo $this->showNull($this->dictionary[$col]->curValue);
					break;
			}
			echo "</td>\n";
			$this->dictionary[$col]->reEval();
		}
		echo "</tr>\n";
	}
	private function tableFooter() {
		echo "</tbody>\n";
		echo "</table>\n";

	}
	public function showTable($columns,$show=true) {
		$this->tableCols=$columns;
		$this->showTable=$show;
		return $this;
	}

	/**
	 * It shows the cache (if any)
	 * @param $cols
	 * @return $this
	 */
	public function show() {
		$this->tableHead();
		for($i=0;$i<$this->maxId;$i++) {
			$this->dictionary['_index'] = $i;
			if ($this->cacheResult!=null) {
				$this->dictionary=$this->cacheResult[$i];
			} else {
				$this->miniLang->evalAllLogic( false);
				
				$this->cleanAndCut();
			}
			$this->tableRow();
			
		}
		$this->tableFooter();
		return $this;
	}

	/**
	 * Used by show
	 * @param $value
	 * @return string
	 * @see \eftec\chaosmachineone\ChaosMachineOne::show
	 */
	private function showNull($value) {
		if ($value===null) return "(null)";
		return $value;
	}

	/**
	 * Inserts the rows to the database.
	 * @param bool $storeCache If true, then the result will be store in the cache.
	 * @param null|string $echoProgress It uses sprintf for show the progress. Example '%s<br>'
	 * @param bool $continueOnError if true then the insert continues if it happens an error.
	 * @param int $maxRetry Maximum number of retries. If an insert fails, then it tries to insert it again.
	 * @return $this
	 */
	public function insert($storeCache=false,$echoProgress=null,$continueOnError=false,$maxRetry=3) {
		if ($storeCache) $this->cacheResult=[]; // deleted the cache
		if ($this->showTable) {
			$this->tableHead();
		}
		if ($this->db===null) {
			$this->debug('WARNING: No database is set');
			return $this;
		}
		if($this->queryTable) {
			if ($this->db===null) {
				$this->debug('WARNING: No database is set');
				return $this;
			}
			try {
				/** @var \PDOStatement $statement */
				$statement = $query = $this->db->runRawQuery($this->queryTable, null, false);
			} catch (Exception $e) {
				if($this->debugMode) {
					$this->debug($e->getMessage());
				}
			}
			$maxId=PHP_INT_MAX; // infinite loop
		} else {
			$statement=null;
			$maxId=$this->maxId;
		}
		for($i=0;$i<$maxId;$i++) {
			if($this->queryTable) {
				try {
					$row = $statement->fetch(PDO::FETCH_ASSOC);
				} catch (Exception $e) {
					if($this->debugMode) {
						$this->debug($e->getMessage());
					}
				}
				if ($row===false) {
					break; // break for.
				}
				foreach($row as $key2=>$value2) {
					$this->dictionary[$this->queryPrefix.$key2]=$value2;
				}

			}
			if ($echoProgress) {
				echo sprintf($echoProgress,$i);
				@flush();
				@ob_flush();
			}
			$retry=0;
			$dicTmp=$this->dictionary;
			while($retry<$maxRetry) {
				$this->dictionary['_index'] = $i;
				$this->miniLang->evalAllLogic(false);
				$this->cleanAndCut();
				$arr = [];
				if ($storeCache) {
					$tmp = [];
					// clone values (avoid instancing the same objects).
					foreach ($this->dictionary as $key => $obj) {
						if (is_object($obj)) {
							$tmp[$key] = clone $obj;
						} else {
							$tmp[$key] = $obj;
						}
					}
					$this->cacheResult[] = $tmp;
				}
				foreach ($this->dictionary as &$obj) {
					if ($this->isChaosField($obj)) {
						$obj->reEval();
						if ($obj->special == 'database') {
							if ($obj->type == 'datetime') {
								$arr[$obj->name] = PdoOne::unixtime2Sql($obj->curValue); // date('Ymd h:i:s', $obj->curValue);//date('Y-m-d H:i:s', $obj->curValue);
							} else {
								$arr[$obj->name] = $obj->curValue;
							}
						}
					}
				}
				try {
					$retry++;
					$id = $this->db->insert($this->table, $arr);
					$retry=0;
					$this->debug("Debug: Inserting #$id");
					break; // exit retry
				} catch (Exception $ex) {
					$this->dictionary=$dicTmp; //rollback data
					if ($continueOnError) {
						$this->debug("Error: Inserting failed" . $ex->getMessage());
					} else {
						echo($ex->getTraceAsString());
						die(1);
					}
				}
				if ($this->showTable) {
					$this->tableRow();
				}
			} // while retry
		} // for
		if ($this->showTable) {
			$this->tableFooter();
		}
		return $this;
	}

	/**
	 * Show statistic of each column. For example, the minimum and maximum value.
	 * @return $this
	 */
	public function stat() {
		$this->startBody();
		foreach($this->dictionary as &$obj) {
			if($this->isChaosField($obj)) {
				echo "<hr>Stat <b>".$obj->name.'</b>:<br>';
				switch($this->dictionary[$obj->name]->type) {
					case 'int':
					case 'decimal':
					case 'varchar':
						echo "Min:".$obj->statMin."<br>";
						echo "Max:".$obj->statMax."<br>";
						echo "Sum:".$obj->statSum."<br>";
						if($obj->statSum>0) {
							echo "Avg:" . ($obj->statSum / $this->maxId) . "<br>";
						}
						break;
					case 'datetime':
						echo "Min:".date('Y-m-d H:i:s l(N)',$obj->statMin)."<br>";
						echo "Max:".date('Y-m-d H:i:s l(N)',$obj->statMax)."<br>";
						echo "Sum:".$obj->statSum."<br>";
						if($obj->statSum>0) {
							echo "Avg:" . date('Y-m-d H:i:s l(N)',($obj->statSum / $this->maxId)) . "<br>";
						}
						break;
				}
			}
		}
		return $this;
	}
	public function debug($msg) {
		if($this->debugMode) echo $msg."<br>";
	}

	/**
	 * If true then this value is nullable.
	 * @param bool $bool
	 * @return $this
	 */
	public function isNullable($bool=true) {
		$this->dictionary[$this->pipeFieldName]->allowNull=$bool;
		return $this;
	}

	/**
	 * @param string $name Name of the field. Example "IdProduct" or "NameCustomer"
	 * @param string $type=['int','decimal','datetime','string'][$i]
	 * @param string $special=['datebase','identity','local'][$i] Indicates if the field will be store into the database
	 * @param int $initValue Initial value. Example: 0, 20.5, "some text"
	 * @param int $min Maxium value. If the value is of the type string then it is the minimum length.
	 * @param int $max Maximum value. If the value is the type string then it is the maximum length.
	 * @return $this
	 */
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

	/**
	 * It must be set after field.  If true and if insert fails, then it field is re-calculated.
	 * @param bool $bool
	 * @return ChaosMachineOne
	 */
	public function retry($bool=true) {
		$this->dictionary[$this->pipeFieldName]->retry=$bool;
		return $this;
	}

	/**
	 * If true then the field allows nulls. If false (the default value), then every null value is converted to another value (0 or '')
	 * @param bool $bool
	 * @return ChaosMachineOne
	 */
	public function allowNull($bool=true) {
		$this->dictionary[$this->pipeFieldName]->allowNull=$bool;
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

	/**
	 * @param ChaosField $destination
	 * @param mixed $source
	 */
	public function copyfilefrom(ChaosField $destination, $source=null) {
		if ($this->isChaosField($source)) {
			copy($source->curValue,$destination->curValue);
		} else {
			copy($source,$destination->curValue);	
		}
		
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
		$this->showTable=false;
	}

	/**
	 * It starts the flow with a table.
	 * @param string $table name of the table
	 * @param int|string $conditions
	 * <p>if it is int then it sets the number of rows to generate.</p>
	 * <p>if it is string then it sets a table to read</p>
	 * <p>Example: table('customers','select * from people','prefix')<p>
	 * @param string $prefix prefix of the rows to read
	 * @return $this
	 */
	public function table($table, $conditions,$prefix='origin_')
	{
		$this->table=$table;
		if(is_int($conditions)) {
			$this->maxId=$conditions;
			$this->queryTable=null;
			$this->queryPrefix=null;
		} else {
			$this->maxId=-1;
			if(stripos($conditions,'select')===false) $conditions="select * from $conditions"; // is the table name
			$this->queryTable=$conditions;
			$this->queryPrefix=$prefix;
		}
		
		$this->cacheResult=null;
		return $this;
	}

	/**
	 * @param string $tableName
	 * @return string
	 * @throws Exception
	 */
	public function generateCode($tableName) {
		$columns=$this->db->columnTable($tableName);
		$fks=$this->db->foreignKeyTable($tableName);
		$code="\$chaos->table('$tableName', 1000)\n";
		$code.="\t\t->setDb(\$db)\n";
		// set fields
		foreach($columns as $k=>$column) {

			$columns[$k]['coltype']=$this->translateColType($column['coltype']);
			$coltype=$columns[$k]['coltype'];
			if ($coltype!='nothing') {
				if ($column['isidentity']) {
					$code .= "\t\t->field('" . $column['colname'] . "', '" . $coltype. "','identity', 0)\n";
				} else {
					$nullable=($column['isnullable'])?"\n\t\t\t->isnullable(true)":'';
					switch ($coltype) {
						case 'decimal':
							$code .= "\t\t->field('" . $column['colname'] . "', '" . $coltype . "','database')$nullable\n";
							break;
						case 'int':
							$code .= "\t\t->field('" . $column['colname'] . "', '" . $coltype . "','database')$nullable\n";
							break;
						case 'string':
							$code .= "\t\t->field('" . $column['colname'] . "', '" . $coltype. "','database','',0,{$column['colsize']})$nullable\n";
							break;
						case 'datetime':
							$code .= "\t\t->field('" . $column['colname'] . "', '" . $coltype . "','database')$nullable\n";
							break;
						default:
							$code .= "\t\t // " . $column['colname'] . " type $coltype not defined\n";
							break; 
					}					
					
				}
			}
		}
		// set arrays
		foreach($fks as $fk) {
			$code.="\t\t->setArrayFromDBTable('array_".$fk['collocal']."','".$fk['tablerem']."','".$fk['colrem']."')\n";
		}
		// generation
		foreach($fks as $fk) {
			$code.="\t\t->gen('when always set ".$fk['collocal'].".value=randomarray(\"array_".$fk['collocal']."\")')\n";
		}
		foreach($columns as $k=>$column) {
			$name=$column['colname'];
			$size=$column['colsize'];
			$found=false;
			foreach($fks as $fk) { 
				if ($fk['collocal']==$name) {
					$found=true; // it is a foreign key.
					break;
				}
			}
			if (!$found && !$column['isidentity']) {
				switch ($column['coltype']) {
					case 'int':
					case 'decimal':
						$code.="\t\t->gen('when always set {$name}.value=random(1,100,10,10,10)')\n";
						break;
					case 'datetime':
						$code.="\t\t->gen('when always set {$name}.speed=random(3600,86400)')\n";
						break;
					case 'string':
						$code.="\t\t->gen('when always set {$name}.value=random(0,{$size})')\n";
						break;		
					case 'nothing':
						break;
					default:
						$code.="\t\t// {$name} not defined for type {$column['coltype']}\n";
						break;
				}
			}
		}
		$code.="\t\t->insert(true);\n";
		return $code;
	}

	/**
	 * @param $colType
	 * @return string
	 */
	private function translateColType($colType) {
		switch ($colType) {
			case 'smallint':
			case 'tinyint':
				return 'int';
			case 'timestamp':
			case 'date':
				return 'datetime';
			case 'nvarchar':
			case 'varchar':
			case 'char':
			case 'nchar':
				return 'string';
			case 'decimal':
			case 'money':
				return 'decimal';
			case 'sysname':
				return 'nothing';
			default:
				return $colType;
		}
		//'int','decimal','datetime','string'
	}
	
	#region Range functions
	/**
	 * It returns the current timestamp. Example: 1558625207
	 * @return int
	 */
	public function now() {
		return time();
	}

	/**
	 * @param string $dateString 2012-01-18 11:45:00
	 * @return false|int
	 */
	public function date($dateString) {
		return date("U",strtotime($dateString));
	}

	/**
	 * Returns an array with the list of files (not recursive) of a folder.
	 * @param $folder
	 * @param string|array $ext (example "jpg","doc" or ["jpg","doc"])
	 * @param bool $returnWithExtension If false then it returns the file name without the extension
	 * @param bool $shuffle if true then the results are shuffled
	 * @return array
	 */
	public function arrayFromFolder($folder,$ext="*",$returnWithExtension=true,$shuffle=false) {
		$files = scandir($folder);
		$result=[];
		foreach($files as $file) {
			$pi=pathinfo($file);
			if (@$pi['extension']) {
				if (is_array($ext)) {
					if (in_array($pi['extension'],$ext)) {
						if ($returnWithExtension) {
							$result[] = $pi['basename'];
						} else {
							$result[] = $pi['filename'];
						}
					}
				} else {
					if ($pi['extension'] == $ext || $ext == '*') {
						if ($returnWithExtension) {
							$result[] = $pi['basename'];
						} else {
							$result[] = $pi['filename'];
						}
					}
				}
			}
		}
		if ($shuffle) shuffle($result); 
			
		return $result;
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
	
	public function randommaskformat($formatName,$arrayName='') {
		$txt=$this->randomformat($formatName);
		return $this->randommask($txt,$arrayName);
	}
	
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
						if ($this->isArray($arrayName)) {
							$txt .= $this->randomarray($arrayName);
						} else {
							if ($this->isFormat($arrayName)) {
								$txt .= $this->randomformat($arrayName);
							} else {
								trigger_error("Array or Format [$arrayName] not defined for ?");
							}
						}
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
				return $this->argValue($args[$i-$m]);
			}
		}
		return $args[0];
	}

	/**
	 * We pass an argument number, text or variable and returned the current value.
	 * @param $arg
	 * @return int
	 */
	private function argValue($arg) {
		if($arg instanceof ChaosField) {
			return $arg->curValue;
		}
		return $arg;
	}

	/**
	 * Returns a value from an array
	 * @param $arrayName
	 * @param null $index
	 * @return mixed|string
	 */
	public function arrayindex($arrayName,$index=null) {
		if (!$this->isArray($arrayName)) {
			trigger_error("Array [$arrayName] not defined");
			return "";
		}
		if ($index===null) $index=$this->dictionary['_index'];

		if (!$this->dictionary[$arrayName][$index]) {
			trigger_error("Array [$arrayName] exists but [$index] is not defined.");
			return "";
		}
		return $this->dictionary[$arrayName][$index];
	}
	public function isArray($arrayName) {
		return isset($this->dictionaryProportional[$arrayName]);
	}
	public function isFormat($formatName) {
		return isset($this->formats[$formatName]);
	}

	/**
	 * @param $arrayName
	 * @param null $fieldName
	 * @param null|array|string $proportion
	 * @return mixed|string
	 */
	public function randomarray($arrayName,$fieldName=null,$proportion=null) {
		if (!$this->isArray($arrayName)) {
			trigger_error("Array [$arrayName] not defined");
			return "";
		}
		if (count($this->dictionary[$arrayName])===0) {
			// empty array
			return "";
		}
		if ($this->dictionaryProportional[$arrayName]!=null) {
			// its an array proportional ['value1'=>30,'value2'=>20...]
			$ap=$this->dictionaryProportional[$arrayName];
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
			$c = count($this->dictionary[$arrayName]);
			$idx = rand(0, $c - 1);
		}
	
		if ($fieldName == null) {
			return $this->dictionary[$arrayName][$idx];
		} else {
			return $this->dictionary[$arrayName][$idx]->{$fieldName};
		}
	}
	public function randomformat($formatName) {
		if (!$this->isFormat($formatName)) {
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
		return $this->parseFormat($format);
	}
	
	public function parseFormat($string)
	{
		return preg_replace_callback('/\{\{\s?(\w+)\s?\}\}/u', array($this, 'callRandomArray'), $string);
	}
	protected function callRandomArray($matches)
	{
		if($this->isArray($matches[1])) {
			return $this->randomarray($matches[1]);
		} else {
			return $this->getDictionary($matches[1])->curValue;
		}
	}
	public function randomtext($startLorem='Lorem ipsum dolor',$arrayName='',$paragraph=false,$nWordMin=20,$nWordMax=40) {
		$array=$this->dictionary[$arrayName];
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

	/**
	 * Gets a random number
	 * @param int|double $from initial number 
	 * @param int|double $to final number
	 * @param int $jump jumps between values
	 * @param null $prob0 Probability of the first 1/3
	 * @param null $prob1 Probability of the second 1/3
	 * @param null $prob2 Probability of the third 1/3
	 * @return float|int|string
	 */
	public function random($from,$to,$jump=1,$prob0=null,$prob1=null,$prob2=null) {
		$r='';
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