<?php

namespace eftec\tests;



use eftec\chaosmachineone\ChaosMachineOne;
use PHPUnit\Framework\TestCase;


abstract class AbstractStateMachineOneTestCase extends TestCase {
    protected $chaosMachineOne;
    public function __construct($name = null, array $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->chaosMachineOne=new ChaosMachineOne();
        //$this->statemachineone->setDebug(true);
    }
}
