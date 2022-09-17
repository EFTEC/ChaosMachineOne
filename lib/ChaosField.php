<?php

namespace eftec\chaosmachineone;

use DateInterval;
use eftec\PdoOne;

/**
 * Class ChaosField
 *
 * @package  eftec\chaosmachineone
 * @author   Jorge Patricio Castro Castillo <jcastro arroba eftec dot cl>
 * @version  1.0 2018-12-26
 * @link     https://github.com/EFTEC/ChaosMachineOne
 * @license  LGPL v3 (or commercial if it's licensed)
 */
class ChaosField
{
    public $name;
    public $type;
    public $typeSize;
    public $special;
    /** @var mixed */
    public $curValue = 0;
    public $curSpeed = 0;
    public $curAccel = 0;
    public $allowNull = false;
    public $alreadyEvaluated = false;
    public $min;
    public $max;
    /** @var bool if true and the operation fails, then this value is re-calculated */
    public $retry = false;

    public $statSum = 0;
    public $statMin = 0;
    public $statMax = 0;

    /**
     * ChaosField constructor.
     * @param $name
     * @param $type
     * @param $typeSize
     * @param $special
     * @param $curValue
     */
    public function __construct($name, $type, $typeSize, $special, $curValue)
    {
        $this->name = $name;
        $this->type = $type;
        $this->typeSize = $typeSize;
        $this->special = $special;
        $this->curValue = $curValue;
        switch ($type) {
            case 'decimal':
            case 'int':
                $this->statMin = 2147483647;
                $this->statMax = -2147483647;
                $this->statSum = 0;
                break;
            case 'date':
            case 'datetime':
                $this->statMin = 2147483647;
                $this->statMax = 0;
                $this->statSum = 0;
                break;
            case 'string':
                $this->statMin = 0;
                $this->statMax = 0;
                $this->statSum = 0;
                break;
        }
    }

    /**
     * We reevaluated the statistic (unless it is a null)
     */
    public function reEval(): void
    {
        if ($this->allowNull && $this->curValue === null) {
            return;
        }
        if ($this->alreadyEvaluated) {
            return;
        }
        $this->alreadyEvaluated = true;
        switch ($this->type) {
            case 'int':
                @$this->curValue += $this->curSpeed;
                $this->curValue = round($this->curValue);
                $this->curSpeed += $this->curAccel;
                if ($this->curValue < $this->statMin) {
                    $this->statMin = $this->curValue;
                }
                if ($this->curValue > $this->statMax) {
                    $this->statMax = $this->curValue;
                }
                $this->statSum += $this->curValue;
                break;
            case 'datetime':
            case 'date':
                //var_dump($this->curValue);
                /** @noinspection PhpUnhandledExceptionInspection */
                $di = new DateInterval('PT' . $this->curSpeed . 'S');
                if (is_string($this->curValue)) {
                    $this->curValue = PdoOne::dateConvert($this->curValue, 'sql', 'class');
                } else if(is_numeric($this->curValue)) {
                    $this->curValue = PdoOne::dateConvert($this->curValue, 'timestamp', 'class');
                }
                $this->curValue->add($di);
                //var_dump($this->curSpeed);
                $this->curSpeed += $this->curAccel;
                //if ($this->curValue<$this->statMin) $this->statMin=$this->curValue;
                //if ($this->curValue>$this->statMax) $this->statMax=$this->curValue;
                $this->statSum += $this->curValue->getTimestamp();
                break;
            case 'decimal':
                @$this->curValue += $this->curSpeed;
                $this->curSpeed += $this->curAccel;
                if ($this->curValue < $this->statMin) {
                    $this->statMin = $this->curValue;
                }
                if ($this->curValue > $this->statMax) {
                    $this->statMax = $this->curValue;
                }
                $this->statSum += $this->curValue;
                break;
            case 'string':
                $l = strlen($this->curValue);
                if ($l < $this->statMin) {
                    $this->statMin = $l;
                }
                if ($l > $this->statMax) {
                    $this->statMax = $l;
                }
                break;
        }
    }

    public function __toString()
    {
        return (string)$this->curValue;
    }

}
