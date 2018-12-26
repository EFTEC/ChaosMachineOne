<?php


namespace eftec\chaosmachineone;


use Composer\Installers\VanillaInstaller;

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
	
	var $table;
	var $maxId;

	/**
	 * ChaosMachineOne constructor.
	 */
	public function __construct()
	{
		$this->reset();
		$this->miniLang=new MiniLang([],[],$this);
	}
	
	public function gen($script) {
		$this->miniLang->separate($script);

		return $this;
	}
	public function show() {
		for($i=0;$i<$this->maxId;$i++) {
			$this->values['_index']=$i;
			$this->miniLang->evalAllLogic($this, $this->values);
			echo str_replace('.',',',$this->values['idtable']->curValue)."<br>";
			/*echo $this->values['idtable']->curValue
				.",speed:".$this->values['idtable']->curSpeed
				.",accel:".$this->values['idtable']->curAccel."<br>";*/
			$this->values['idtable']->reEval();
		
		}
	}
	public function field($name,$type,$special='database',$initValue=0,$min=PHP_INT_MIN,$max=PHP_INT_MAX) {
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
		return $this;
	}


	public function speed(ChaosField $field,$v2=null) {
		$field->curSpeed=$v2;
	}
	public function accel(ChaosField $field,$v2=null) {
		$field->curAccel=$v2;
	}
	public function add(ChaosField $field,$v2=null) {
		$field->curValue+=$v2;
	}
	public function value(ChaosField $field,$v2=null) {
		$field->curValue=$v2;
	}

	public function reset() {
		$this->pipeFieldName=null;
		$this->pipeFieldType=null;
		$this->pipeFieldTypeSize=null;
		$this->pipeFieldSpecial=null;
		$this->pipeValue=null;
	}

	public function table($table, int $maxId)
	{
		$this->table=$table;
		$this->maxId=$maxId;
		return $this;
	}
	#region Range functions
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
	public function atan($startX,$startY,$speed=1,$scale=1) {

		$idx=$this->values['_index']; // 10
		$idxDelta=$idx-$startX; // 10-0 = 10
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
	
	public function random($from,$to,$jump=1) {
		$r='';
		switch ($this->pipeFieldType) {
			case '':
			case 'int':
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