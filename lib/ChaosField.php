<?php

namespace eftec\chaosmachineone;

/**
 * Class ChaosField
 * @package eftec\chaosmachineone
 * @author   Jorge Patricio Castro Castillo <jcastro arroba eftec dot cl>
 * @version 1.0 2018-12-26
 * @link https://github.com/EFTEC/ChaosMachineOne
 * @license LGPL v3 (or commercial if it's licensed)
 */
class ChaosField
{
	var $name;
	var $type;
	var $typeSize;
	var $special;
	var $curValue=0;
	var $curSpeed=0;
	var $curAccel=0;
	
	var $min;
	var $max;
	
	var $statSum=0;
	var $statMin=0;
	var $statMax=0;

	/**
	 * ChaosField constructor.
	 * @param $name
	 * @param $type
	 * @param $typeSize
	 * @param $special
	 * @param $curValue
	 */
	public function __construct($name, $type, $typeSize, $special,$curValue)
	{
		$this->name = $name;
		$this->type = $type;
		$this->typeSize = $typeSize;
		$this->special = $special;
		$this->curValue=$curValue;
		switch ($type) {
			case 'int':
				$this->statMin=2147483647;
				$this->statMax=-2147483647;
				$this->statSum=0;
				break;
			case 'decimal':
				$this->statMin=2147483647;
				$this->statMax=-2147483647;
				$this->statSum=0;
				break;
			case 'date':
			case 'datetime':
				$this->statMin=2147483647;
				$this->statMax=0;
				$this->statSum=0;
				break;
			case 'string':
				$this->statMin=0;
				$this->statMax=0;
				$this->statSum=0;
				break;
		}
	}
	public function reEval() {
		switch ($this->type) {
			case 'int':
			case 'datetime':
			case 'date':
				$this->curValue +=$this->curSpeed;
				$this->curValue=round($this->curValue,0);
				$this->curSpeed += $this->curAccel;
				if ($this->curValue<$this->statMin) $this->statMin=$this->curValue;
				if ($this->curValue>$this->statMax) $this->statMax=$this->curValue;
				$this->statSum+=$this->curValue;
				break;
			case 'decimal':
				$this->curValue += $this->curSpeed;
				$this->curSpeed += $this->curAccel;
				if ($this->curValue<$this->statMin) $this->statMin=$this->curValue;
				if ($this->curValue>$this->statMax) $this->statMax=$this->curValue;
				$this->statSum+=$this->curValue;
				break;
			case 'string':
				$l=strlen($this->curValue);
				if ($l<$this->statMin) $this->statMin=$l;
				if ($l>$this->statMax) $this->statMax=$l;
				break;
		}
	}

}