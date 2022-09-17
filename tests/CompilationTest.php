<?php

namespace eftec\tests;

use eftec\chaosmachineone\ChaosMachineOne;
use Exception;

class CompilationTest extends AbstractStateMachineOneTestCase
{
    /**
     * @throws Exception
     */
    public function test1(): void
    {
        $this->chaosMachineOne
            ->table('table', 1000)
            ->field('fixed', 'int', 'local', 123)
            ->field('fixedformat', 'string', 'local', '', 0, 9999)
            ->field('idtest', 'int', 'database', 123, 0, 200)
            ->field('idtest2', 'int', 'database', 10, 0, 200)
            ->field('idtest3', 'int', 'database', 10, 0, 999)
            ->field('idtest4', 'int', 'database', 10, 0, 999)
            ->field('idtest5', 'decimal', 'database', 10, 0, 999)
            ->field('idtest6', 'decimal', 'database', 10, 0, 999)
            ->field('texttest1', 'string', 'database', '', 0, 999)
            ->setArray('testarray', ['a', 'b', 'c'])
            ->setArray('testarraya', ['a', 'a', 'a'])
            ->setFormat('myformat', ['array random value:{{testarraya}} number:{{fixed}}'])
            ->gen('when always then fixedformat.value=randomformat("myformat")')
            ->gen('when _index<500 then idtest2.add=1 and idtest3.add=1')
            ->gen('when idtest3.getvalue<100 then idtest4.add=1')
            ->gen('when always then 
		    texttest1.value=randomarray("testarray") and idtest5.value=20.3
		    and idtest6.value=ramp(0,100,0,100)
		    and idtest6.value=ramp(0,100,0,100)
		    ')/** @see \eftec\chaosmachineone\ChaosMachineOne::ramp */
            ->run();
        $idTest = $this->chaosMachineOne->getDictionary('idtest');
        $idtest2 = $this->chaosMachineOne->getDictionary('idtest2');
        $idtest3 = $this->chaosMachineOne->getDictionary('idtest3');
        $idtest4 = $this->chaosMachineOne->getDictionary('idtest4');
        $idtest5 = $this->chaosMachineOne->getDictionary('idtest5');
        $testarray = $this->chaosMachineOne->getDictionary('texttest1');
        $fixedformat = $this->chaosMachineOne->getDictionary('fixedformat');
        self::assertEquals(123, $idTest->curValue); // default value
        self::assertEquals(200, $idtest2->curValue); // it's the maximum value
        self::assertEquals(510, $idtest3->curValue);
        self::assertEquals(99, $idtest4->curValue);
        self::assertEquals(20.3, $idtest5->curValue);
        self::assertContains($testarray->curValue, ['a', 'b', 'c']);
        self::assertEquals("array random value:a number:123", $fixedformat->curValue);
        $this->chaosMachineOne = new ChaosMachineOne();
        $this->chaosMachineOne
            ->table('table', 100)
            ->field('idtest', 'int', 'database', -1, 0, 9999)
            ->gen('when _index<500 then idtest.value=ramp(0,100,0,1000)')
            ->run();
        $idTest = $this->chaosMachineOne->getDictionary('idtest');
        self::assertEquals(990, $idTest->curValue); // default value
    }


    public function testSpeed(): void
    {
        $this->chaosMachineOne
            ->table('table', 10)
            ->field('v1', 'int', 'local', 1)
            ->field('v2', 'int', 'local', 1)
            ->gen('when always then v2=v1')
            ->gen('when always then v1.speed=2')

            //->showTable(['v1','v2'])
            ->run();
        $idTest = $this->chaosMachineOne->getDictionary('v1');
        $idtest2 = $this->chaosMachineOne->getDictionary('v2');
        self::assertEquals(21, $idTest->curValue); // default value
        self::assertEquals(21, $idtest2->curValue); // it's the maximum value
    }

}
