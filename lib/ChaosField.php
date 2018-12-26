<?php

namespace eftec\chaosmachineone;


class ChaosField
{
	var $name;
	var $type;
	var $typeSize;
	var $special;
	var $curValue=0;
	var $curSpeed=0;
	var $curAccel=0;

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
	}
	public function reEval() {
		$this->curValue+=$this->curSpeed;
		$this->curSpeed+=$this->curAccel;
	}

}