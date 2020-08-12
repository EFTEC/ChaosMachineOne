<?php
/** @noinspection UnknownInspectionInspection */

/** @noinspection TypeUnsafeComparisonInspection */

/** @noinspection AlterInForeachInspection */

/** @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection */

/** @noinspection PhpUnused */

/** @noinspection DuplicatedCode */

namespace eftec\chaosmachineone;

use DateTime;
use eftec\minilang\MiniLang;
use eftec\PdoOne;
use Exception;
use PDO;
use PDOStatement;

/**
 * Class ChaosMachineOne
 *
 * @package  eftec\chaosmachineone
 * @author   Jorge Patricio Castro Castillo <jcastro arroba eftec dot cl>
 * @version  1.9 2020-08-12
 * @link     https://github.com/EFTEC/ChaosMachineOne
 * @license  LGPL v3 (or commercial if it's licensed)
 */
class ChaosMachineOne
{
    const NUM_OPT = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, ''];
    const NUM = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, ' '];
    const ALPHA
        = [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z'
        ];
    const ALPHA_OPT
        = [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            ''
        ];
    public $debugMode = false;
    /**
     * It sets common probabilities. [nameoftheprobability=[values]]
     *
     * @var int[]
     * @see \eftec\chaosmachineone\ChaosMachineOne::setArrayFromDBTable
     * @see \eftec\chaosmachineone\ChaosMachineOne::setArray
     */
    public $probTypes
        = [
            'fakebell'  => [10, 25, 30, 25, 10],
            'fakebell2' => [15, 22, 26, 22, 15],
            'fakebell3' => [5, 15, 60, 15, 5],
            'rightbias' => [5, 10, 20, 35, 30],
            'leftbias'  => [30, 35, 20, 10, 5]
        ];

    private $dictionary = [];
    private $dictionaryProportional = [];
    /**
     * @var array This array keeps the values obtained from an array per line of operation.<br>
     *            It creates consistence with the information read per line.<br>
     *            Example:  If we want to generate a random name and a random full name. <br>
     *                      The fullname must repeats the same name.
     */
    private $keepRandomArray = [];
    private $pipeFieldName;
    private $pipeFieldType;
    private $pipeFieldTypeSize;
    private $pipeFieldSpecial;
    private $pipeValue;
    private $showTable = false;
    private $insert = false;
    private $insertContinueOnError = false;
    private $insertMaxRetry = 3;
    private $tableCols = [];
    /** @var MiniLang */
    private $miniLang;
    /** @var PdoOne */
    private $db;
    private $formats = [];
    private $formatsProportional = [];
    private $tableDB;
    private $maxId;
    private $queryTable = "";
    private $queryPrefix = 'origin_';

    // special
    private $daysWeek = ['', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    private $bodyLoad = false;
    /** @var array that stores the results method insert() */
    private $cacheResult;

    /**
     * ChaosMachineOne constructor.
     */
    public function __construct()
    {
        $this->reset();
        $this->miniLang = new MiniLang($this, $this->dictionary, ['always'], [], $this);
    }

    public function reset()
    {
        $this->pipeFieldName = null;
        $this->pipeFieldType = null;
        $this->pipeFieldTypeSize = null;
        $this->pipeFieldSpecial = null;
        $this->pipeValue = null;
        $this->showTable = false;
        $this->insert = false;
    }

    public function always()
    {
        return true;
    }

    /**
     * @param PdoOne $db
     *
     * @return ChaosMachineOne
     */
    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }

    public function gen($script)
    {
        $this->miniLang->separate($script);
        return $this;
    }

    /**
     *
     * <b>Example:</b><br>
     * <pre>
     * $this->setArrayFromDBTable('arrayname','customers','idcustomer',[40,20,10]);
     * $this->setArrayFromDBTable('arrayname','customers','idcustomer',[40,20,10],'age>20'); // where age>20
     * $this->setArrayFromDBTable('arrayname','customers',['idcustomer'=>'chance']); // the probability is in
     *                                                                                //the column chance
     * </pre>
     *
     * @param string             $name        name of the array
     * @param string             $table       name of the table of the database
     * @param array|string       $column      name of the column. It could be a formula but it must be the first column.<br>
     *                                        If it is an array then the key is the name and the value is the probability<br>
     * @param array|string|int[] $probability =[[],'increase','decrease'][$i] It is another alternative to set an the
     *                                        probabilities but inly if $value is not an associative array.<br>
     *                                        <b>'increase'</b> sets the probability increasing in the time (ramp)<br>
     *                                        <b>'decrease'</b> sets the probability decreasing in the time (ramp)<br>
     *                                        <b>'fakebell'</b> sets the probability to [10,25,30,25,10] (a normal bell)<br>
     *                                        <b>'fakebell2'</b> sets the probability to [15,22,26,22,15] (a wide bell)<br>
     *                                        <b>'fakebell3'</b> sets the probability to [5,15,60,15,5] (a narrow bell)<br>
     *                                        <b>'righbias'</b> sets the probability to [5,10,20,35,30] (bell right bias)<br>
     *                                        <b>'leftbias'</b> sets the probability to [30,35,20,10,5] (bell left bias)<br>
     *                                        <p><b>[60,30,10..]</b> It sets 60% of chance for the first 1/3 elements, 20% for the
     *                                        second 1/3 and 10 for the last 1/3.</p>
     *                                        <p><b>Note:</b> The number of probabilities doesn't need to match the number of
     *                                        elements of the array</p>
     *
     *
     * @param null|string        $where
     * @param null |string       $order
     *
     * @return ChaosMachineOne
     */
    public function setArrayFromDBTable($name, $table, $column, $probability = [1], $where = null, $order = null)
    {
        try {
            if (!is_array($column)) {
                $values = $this->db->select($column)->from($table)->where($where)->order($order)->toList();
                $result = [];
                foreach ($values as $val) {
                    $result[] = reset($val); // first element of the array
                }
            } else {
                $column1 = array_keys($column)[0];
                $column2 = $column[$column1];
                $values = $this->db->select("$column1,$column2")->from($table)->where($where)->toList();
                $result = [];
                foreach ($values as $val) {
                    $result[$val[$column1]] = $val[$column2];
                }
            }
        } catch (Exception $e) {
            $result = [];
        }

        return $this->setArray($name, $result, $probability);
    }

    /**
     * Set an array that we could use it later:
     * <p>->setArray('drinks',['cocacola','fanta','sprite'])</p>
     * <p>->setArray('drinks',['cocacola'=>80,'fanta'=>10,'sprite'=>10]); <br>// cocacola 80% prob, fanta 10%
     *                                                          //, sprite 10%. The total could be any number</p>
     * <p>->setArray('drinks',['cocacola','fanta','sprite','seven up'],[80,30])</p>
     *
     * @param string             $name        name of the array. If the array exists then it returns an error.
     * @param array              $value       It could be a simple array with value or an associative array<br>
     *                                        If it is an associative array then the key is the value to use and the value is
     *                                        the probability.
     * @param array|string|int[] $probability =[[],'increase','decrease'][$i] It is another alternative to set an the
     *                                        probabilities but inly if $value is not an associative array.<br>
     *                                        <b>'increase'</b> sets the probability increasing in the time (ramp)<br>
     *                                        <b>'decrease'</b> sets the probability decreasing in the time (ramp)<br>
     *                                        <b>'fakebell'</b> sets the probability to [10,25,30,25,10] (a normal bell)<br>
     *                                        <b>'fakebell2'</b> sets the probability to [15,22,26,22,15] (a wide bell)<br>
     *                                        <b>'fakebell3'</b> sets the probability to [5,15,60,15,5] (a narrow bell)<br>
     *                                        <b>'righbias'</b> sets the probability to [5,10,20,35,30] (bell right bias)<br>
     *                                        <b>'leftbias'</b> sets the probability to [30,35,20,10,5] (bell left bias)<br>
     *                                        <p><b>[60,30,10..]</b> It sets 60% of chance for the first 1/3 elements, 20% for the
     *                                        second 1/3 and 10 for the last 1/3.</p>
     *                                        <p><b>Note:</b> The number of probabilities doesn't need to match the number of
     *                                        elements of the array</p>
     *
     * @return $this
     */
    public function setArray($name, $value = [], $probability = [1])
    {
        reset($value);

        $first_key = key($value);
        if ($first_key == 0) {
            // It is not an associative array so we converted into an associative array
            if (isset($this->dictionary[$name])) {
                trigger_error("arrays[$name] is already defined");
            }
            //$this->dictionary[$name] = $value;
            //$this->dictionaryProportional[$name] = null;
            $numValue = count($value);
            $tmp = $value;
            if (is_string($probability) && !isset($this->probTypes[$probability])) {
                $value = [];
                switch ($probability) {
                    case 'increase':
                        foreach ($tmp as $k => $v) {
                            $value[$v] = $k + 1;
                        }
                        break;

                    case 'decrease':
                        foreach ($tmp as $k => $v) {
                            $value[$v] = $numValue + 1 - $k;
                        }
                        break;
                    default:
                        trigger_error("probability [$probability] not defined");
                }
            } else {
                /** @noinspection NotOptimalIfConditionsInspection */
                if (is_string($probability) && isset($this->probTypes[$probability])) {
                    /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                    $probability = $this->probTypes[$probability];
                }
                $c2 = count($probability);
                $cpart = ceil($numValue / $c2);
                if (count($probability) === 0) {
                    $probability = [1];
                }
                $value = [];
                $probCounter = -1;
                foreach ($tmp as $k => $v) {
                    if ($k % $cpart === 0) {
                        $probCounter++;
                    }
                    if (is_array($v)) {
                        trigger_error('setarray: value must not be an array of associative array');
                        return $this;
                    }
                    $value[$v] = $probability[$probCounter];
                }
            }
        }
        //it's a associative array. The value is the proportion
        //[a=>10,b=>20,c=>30] => [a,b,c] [10,30,60] (0..9=a,10..29=b,30..60=c)
        $this->dictionaryProportional[$name] = [];
        $this->dictionary[$name] = [];
        $sum = 0;
        foreach ($value as $k => $v) {
            $sum += $v;
            $this->dictionary[$name][] = $k;
            $this->dictionaryProportional[$name][] = $sum;
        }

        return $this;
    }

    /**
     * It gets two array per table.<br>
     * <b>Example:</b><br>
     * <pre>
     * $this->setArrayFromDBTable2('arr_id','arr_name','product','idProduct','nameProduct',[30,50],'enabled=1');
     * $this->setArrayFromDBTable2('arr_id','arr_name',['product'=>'popularity'],'idProduct','nameProduct');
     * </pre>
     *
     * @param string             $name        name of the array
     * @param string             $name2       name of the second array
     * @param string             $table       name of the table of the database
     * @param string|array       $column      name of the column. It could be a formula.
     * @param string             $column2     name of the second column.
     * @param array|string|int[] $probability =[[],'increase','decrease'][$i] It is another alternative to set an the
     *                                        probabilities but inly if $value is not an associative array.<br>
     *                                        <b>'increase'</b> sets the probability increasing in the time (ramp)<br>
     *                                        <b>'decrease'</b> sets the probability decreasing in the time (ramp)<br>
     *                                        <b>'fakebell'</b> sets the probability to [10,25,30,25,10] (a normal bell)<br>
     *                                        <b>'fakebell2'</b> sets the probability to [15,22,26,22,15] (a wide bell)<br>
     *                                        <b>'fakebell3'</b> sets the probability to [5,15,60,15,5] (a narrow bell)<br>
     *                                        <b>'righbias'</b> sets the probability to [5,10,20,35,30] (bell right bias)<br>
     *                                        <b>'leftbias'</b> sets the probability to [30,35,20,10,5] (bell left bias)<br>
     *                                        <p><b>[60,30,10..]</b> It sets 60% of chance for the first 1/3 elements, 20% for the
     *                                        second 1/3 and 10 for the last 1/3.</p>
     *                                        <p><b>Note:</b> The number of probabilities doesn't need to match the number of
     *                                        elements of the array</p>
     * @param null|string        $where       (optional) a sql condition.
     *
     * @return ChaosMachineOne
     */
    public function setArrayFromDBTable2($name, $name2, $table, $column, $column2, $probability = [1], $where = null)
    {
        if (!is_array($column)) {
            try {
                $values = $this->db->select("$column,$column2")->from($table)->where($where)->toList();
                $result = [];
                $result2 = [];
                foreach ($values as $val) {
                    $result[] = $val[$column];
                    $result2[] = $val[$column2];
                }
            } catch (Exception $e) {
                $result = [];
                $result2 = [];
            }
        } else {
            try {
                $column1 = array_keys($column)[0];
                $column1b = $column[$column1];
                $values = $this->db->select("$column1,$column1b,$column2")->from($table)->where($where)->toList();
                $result = [];
                $result2 = [];
                foreach ($values as $val) {
                    $result[$val[$column1]] = $val[$column1b];
                    $v2 = $val[$column2];
                    for ($rep = 0; $rep < 9999; $rep++) {
                        if (in_array($v2, $result2, true)) {
                            $v2 .= "($rep)"; // rename repeated
                        } else {
                            break;
                        }
                    }
                    $result2[] = $v2;
                }
            } catch (Exception $e) {
                $result = [];
                $result2 = [];
            }
        }
        /*echo "<pre>";
        global $db;
        var_dump($db->lastError());
        var_dump($result);
        var_dump($result2);
        echo "</pre>";
        */
        $this->setArray($name, $result, $probability);
        $this->setArray($name2, $result2, $probability);

        return $this;
    }

    /**
     * Retrusn the first column of a query
     * Example ->setArrayFromDBQuery('myarray','select * from table');
     *
     * @param string     $name  name of the array
     * @param string     $query example: select col from table
     * @param array      $probability
     * @param null|array $queryParam
     *
     * @return ChaosMachineOne
     */
    public function setArrayFromDBQuery($name, $query, $probability = [1], $queryParam = null)
    {
        try {
            $values = $this->db->runRawQuery($this->parseFormat($query), $queryParam, true);
            $result = [];
            foreach ($values as $val) {
                $result[] = reset($val); // first element of the array
            }
        } catch (Exception $e) {
            $result = [];
        }
        return $this->setArray($name, $result, $probability);
    }

    /**
     * @param string $string
     *
     * @return string|string[]|null
     * @see \eftec\chaosmachineone\ChaosMachineOne::randomarray
     */
    public function parseFormat($string)
    {
        //$this->usePreviousValue = $usePreviousValue;
        return preg_replace_callback('/{{\s?(\w+)\s?}}/u', array($this, 'callRandomArray'), $string);
    }


    public function setFormat($name, $value = [])
    {
        reset($value);
        $first_key = key($value);
        if (is_numeric($first_key)) {
            if (isset($this->formats[$name])) {
                trigger_error("formats[$name] is already defined");
            }
            $this->formats[$name] = $value;
            $this->formatsProportional[$name] = null;
        } else {
            $this->formatsProportional[$name] = [];
            $this->formats[$name] = [];
            $sum = 0;
            foreach ($value as $k => $v) {
                $sum += $v;
                $this->formats[$name][] = $k;
                $this->formatsProportional[$name][] = $sum;
            }
        }
        return $this;
    }

    public function runSql($sql)
    {
        if ($this->db === null) {
            return '';
        }
        try {
            $array = $this->db->runRawQuery($sql, null, true);
        } catch (Exception $e) {
            if ($this->debugMode) {
                $this->debug($e->getMessage());
            }
            return null;
        }
        if (is_array($array) || count($array) > 0) {
            return end($array[0]);
        }
        return null;
    }

    public function debug($msg)
    {
        if ($this->debugMode) {
            echo $msg . "<br>";
        }
    }

    /**
     * We run the evaluation.
     *
     * @param bool $storeCache
     *
     * @return ChaosMachineOne
     */
    public function run($storeCache = false)
    {
        if ($storeCache) {
            $this->cacheResult = [];
        } // deleted the cache
        if ($this->showTable) {
            $this->tableHead();
        }
        if (is_string($this->queryTable)) {
            if ($this->db === null) {
                $this->debug('WARNING: No database is set');
                return $this;
            }
            /** @var PDOStatement $statement */
            try {
                $statement = $this->db->runRawQuery($this->queryTable, null, false);
            } catch (Exception $e) {
                if ($this->debugMode) {
                    $this->debug($e->getMessage());
                }
            }
            $maxId = PHP_INT_MAX; // infinite loop
        } else {
            $statement = null;
            $maxId = $this->maxId;
        }


        $retryNum = 0;
        for ($i = 0; $i < $maxId; $i++) {
            $retry = false;
            do {
                if ($retry && $this->debugMode) {
                    $this->debug('Retrying value #' . $i);
                }
                $dictTmp = $this->dictionary;
                //die(1);

                $this->keepRandomArray = [];
                if (is_string($this->queryTable)) {
                    try {
                        $row = $statement->fetch(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {
                        $row = false;
                        if ($this->debugMode) {
                            $this->debug($e->getMessage());
                        }
                    }
                    if ($row === false) {
                        break(2); // break while and for.
                    }
                    foreach ($row as $key2 => $value2) {
                        $this->field($this->queryPrefix . $key2, 'string', 'local', $value2);
                    }
                }
                if (is_array($this->queryTable)) {
                    $this->field($this->queryPrefix, 'string', 'local', $this->queryTable[$i]);
                    //echo "setting ".$this->queryPrefix." = ".$this->queryTable[$i]."<br>";
                }

                $this->dictionary['_index'] = $i;
                $this->miniLang->evalAllLogic(false);


                $this->cleanAndCut();
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
                        /** @var $obj ChaosField */
                        $obj->reEval();
                    }
                }
                if ($this->showTable) {
                    //$this->dictionary=$tmp;
                    $this->tableRow();
                }
                if ($this->insert) {
                    $ok = $this->insertRow($dictTmp);
                    if (!$ok) {
                        $retryNum++;
                        $retry = ($retryNum < $this->insertMaxRetry); // it keeps retrying.
                    } else {
                        $retry = false;
                        $retryNum = 0;
                    }
                } else {
                    $retry = false;
                }
            } while ($retry);
        } // for
        if ($this->showTable) {
            $this->tableFooter();
        }
        return $this;
    }

    private function tableHead()
    {
        $cols = $this->tableCols;
        $this->startBody();
        echo "<table class='table table-striped table-sm'>";
        echo "<thead class='thead-dark'><tr>";
        foreach ($cols as $col) {
            if (isset($this->dictionary[$col]->name)) {
                echo "<th>" . $this->dictionary[$col]->name . "</th>";
            } else {
                echo "<th>($col)</th>";
            }
        }
        echo "</tr></thead>";
        echo "<tbody>";
    }

    public function startBody()
    {
        if ($this->bodyLoad) {
            return;
        }
        $this->bodyLoad = true;
        echo "<!doctype html>
		<html lang='en'>
		  <head>
		    <meta charset='utf-8'>
		    <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
            <link rel=\"stylesheet\" href=\"https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css\" integrity=\"sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh\" crossorigin=\"anonymous\">		   
		    <title>Show table</title>
		  </head>
		  <body><div class='container-fluid>'><div class='row'><div class='col'>";
    }

    /**
     * @param string $name      Name of the field. Example "IdProduct" or "NameCustomer"
     * @param string $type      =['int','decimal','datetime','string'][$i]
     * @param string $special   =['database','identity','local'][$i] Indicates if the field will be store into the database
     * @param int    $initValue Initial value. Example: 0, 20.5, "some text"
     * @param int    $min       Maxium value. If the value is of the type string then it is the minimum length.
     * @param int    $max       Maximum value. If the value is the type string then it is the maximum length.
     *
     * @return $this
     */
    public function field($name, $type, $special = 'database', $initValue = 0, $min = -2147483647, $max = 2147483647)
    {
        $this->pipeFieldName = $name;
        if (strpos($type, '(') !== false) {
            $x = explode('(', $type);
            $this->pipeFieldType = $x[0];
            $this->pipeFieldTypeSize = substr($x[1], 0, -1);
        } else {
            $this->pipeFieldType = $type;
            $this->pipeFieldTypeSize = 0;
        }
        if (in_array($special, ['database', 'identity', 'local']) === false) {
            trigger_error('field: special argument incorrect');
            return $this;
        }
        $this->pipeFieldSpecial = $special;
        $this->dictionary[$name] = new ChaosField($name, $this->pipeFieldType, $this->pipeFieldTypeSize, $special,
            $initValue);
        $this->dictionary[$name]->min = $min;
        $this->dictionary[$name]->max = $max;
        return $this;
    }

    /**
     * We limit the results for each constrains defined by the fields.
     */
    public function cleanAndCut()
    {
        foreach ($this->dictionary as &$obj) {
            if ($this->isChaosField($obj)) {
                switch ($obj->type) {
                    case 'int':
                        if ($obj->allowNull && $obj->curValue === null) {
                            $obj->curValue = null;
                        } else {
                            $obj->curValue = round($obj->curValue, 0);
                            if ($obj->curValue < $obj->min) {
                                $obj->curValue = $obj->min;
                            }
                            if ($obj->curValue > $obj->max) {
                                $obj->curValue = $obj->max;
                            }
                        }
                        break;
                    case 'decimal':
                        if ($obj->allowNull && $obj->curValue === null) {
                            $obj->curValue = null;
                        } else {
                            if ($obj->curValue < $obj->min) {
                                $obj->curValue = $obj->min;
                            }
                            if ($obj->curValue > $obj->max) {
                                $obj->curValue = $obj->max;
                            }
                        }
                        break;
                    case 'string':
                        if ($obj->allowNull && $obj->curValue === null) {
                            $obj->curValue = null;
                        } else {
                            $l = strlen($obj->curValue);
                            if ($l < $obj->min && $obj->min > 0) {
                                $obj->curValue .= str_repeat(' ', $obj->min - $l);
                            }
                            if ($l > $obj->max) {
                                $obj->curValue = $this->trimText($obj->curValue, $obj->max);
                            }
                        }
                        break;
                }
            }
        }
    }

    private function isChaosField($obj)
    {
        return is_object($obj) && $obj instanceof ChaosField;
    }

    private function trimText($txt, $l)
    {
        $txt = substr($txt, 0, $l);
        $pLast = strrpos($txt, ' ');
        if ($pLast !== false) {
            $txt = substr($txt, 0, $pLast) . '.';
        }
        return $txt;
    }

    private function tableRow()
    {
        $cols = $this->tableCols;
        echo "<tr>\n";

        foreach ($cols as $col) {
            echo "<td>";
            if (is_object($this->dictionary[$col])) {
                switch ($this->dictionary[$col]->type) {
                    case 'datetime':
                        //echo date('Y-m-d H:i:s l(N)', $this->dictionary[$col]->curValue) . "<br>";
                        echo $this->dictionary[$col]->curValue->format('Y-m-d H:i:s l(N)') . "<br>";
                        break;
                    case 'int':
                    case 'string':
                    case 'decimal':
                        echo $this->showNull($this->dictionary[$col]->curValue);
                        break;
                }
            } else {
                echo $this->dictionary[$col];
            }
            echo "</td>\n";
            /** @var $this ->dictionary ChaosField[] */
            if (is_object($this->dictionary[$col])) {
                $this->dictionary[$col]->reEval();
            }
        }
        echo "</tr>\n";
    }

    /**
     * Used by show
     *
     * @param $value
     *
     * @return string
     * @see \eftec\chaosmachineone\ChaosMachineOne::show
     */
    private function showNull($value)
    {
        if ($value === null) {
            return "(null)";
        }
        return $value;
    }

    private function insertRow($dicTmp)
    {
        $arr = [];
        foreach ($this->dictionary as &$obj) {
            if ($this->isChaosField($obj)) {
                /** @var $obj ChaosField */
                $obj->reEval();
                if ($obj->special === 'database') {
                    if ($obj->type === 'datetime') {
                        if ($obj->curValue instanceof DateTime) {
                            $arr[$obj->name] = PdoOne::dateTimePHP2Sql($obj->curValue);
                        } else {
                            $arr[$obj->name]
                                = PdoOne::unixtime2Sql($obj->curValue); // date('Ymd h:i:s', $obj->curValue);//date('Y-m-d H:i:s', $obj->curValue);
                        }
                    } else {
                        $arr[$obj->name] = $obj->curValue;
                    }
                }
            }
        }
        try {
            $id = $this->db->insert($this->tableDB, $arr);

            $this->debug("Debug: Inserting #$id");
            return true; // exit rtry
        } catch (Exception $ex) {
            $this->dictionary = $dicTmp; //rollback data
            if ($this->insertContinueOnError) {
                $this->debug("Error: Inserting failed" . $ex->getMessage());
                $this->debug("Query:" . $this->db->lastQuery);
                $this->debug("args: " . print_r($arr, true));
                return false;
            }

            echo $ex->getMessage() . "\n";
            echo($ex->getTraceAsString());
            die(1);
        }
    }

    private function tableFooter()
    {
        echo "</tbody>\n";
        echo "</table>\n";
    }

    public function endBody()
    {
        echo "</div></div></div></body></html>";
    }

    public function showTable($columns, $show = true)
    {
        $this->tableCols = $columns;
        $this->showTable = $show;
        return $this;
    }

    /**
     * It shows the cache (if any)
     *
     * @return $this
     */
    public function show()
    {
        $this->tableHead();
        for ($i = 0; $i < $this->maxId; $i++) {
            $this->dictionary['_index'] = $i;
            if ($this->cacheResult != null) {
                $this->dictionary = $this->cacheResult[$i];
            } else {
                $this->miniLang->evalAllLogic(false);

                $this->cleanAndCut();
            }
            $this->tableRow();
        }
        $this->tableFooter();
        return $this;
    }

    /**
     * It sets to insert the values into the database.  The values are inserted when they are run so it doesn't
     * need cache
     *
     * @param bool $continueOnError if true then the insert continues if it happens an error.
     * @param int  $maxRetry        Maximum number of retries. If an insert fails, then it tries to insert it again.
     *
     * @return ChaosMachineOne
     * @see \eftec\chaosmachineone\ChaosMachineOne::run
     */
    public function setInsert($continueOnError = false, $maxRetry = 3)
    {
        $this->insert = true;
        $this->insertContinueOnError = $continueOnError;
        $this->insertMaxRetry = $maxRetry;
        return $this;
    }

    /**
     * Inserts the rows to the database.
     *
     * @param bool        $storeCache      If true, then the result will be store in the cache.
     * @param null|string $echoProgress    It uses sprintf for show the progress. Example '%s<br>'
     * @param bool        $continueOnError if true then the insert continues if it happens an error.
     * @param int         $maxRetry        Maximum number of retries. If an insert fails, then it tries to insert it again.
     *
     * @return $this
     * @deprecated
     */
    public function insert($storeCache = false, $echoProgress = null, $continueOnError = false, $maxRetry = 3)
    {
        if ($storeCache) {
            $this->cacheResult = [];
        } // deleted the cache
        if ($this->showTable) {
            $this->tableHead();
        }
        if ($this->db === null) {
            $this->debug('WARNING: No database is set');
            return $this;
        }
        if (is_string($this->queryTable)) {
            if ($this->db === null) {
                $this->debug('WARNING: No database is set');
                return $this;
            }
            try {
                /** @var PDOStatement $statement */
                $statement = $this->db->runRawQuery($this->queryTable, null, false);
            } catch (Exception $e) {
                if ($this->debugMode) {
                    $this->debug($e->getMessage());
                }
            }
            $maxId = PHP_INT_MAX; // infinite loop
        } else {
            $statement = null;
            $maxId = $this->maxId;
        }
        for ($i = 0; $i < $maxId; $i++) {
            if (is_string($this->queryTable)) {
                try {
                    $row = $statement->fetch(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    $row = false;
                    if ($this->debugMode) {
                        $this->debug($e->getMessage());
                    }
                }
                if ($row === false) {
                    break; // break for.
                }
                foreach ($row as $key2 => $value2) {
                    $this->field($this->queryPrefix . $key2, 'string', 'local', $value2);
                    echo "setting " . $this->queryPrefix . $key2 . " = $value2<br>";
                }
            }
            if (is_array($this->queryTable)) {
                $this->field($this->queryPrefix, 'string', 'local', $this->queryTable[$i]);
            }
            if ($echoProgress) {
                echo sprintf($echoProgress, $i);
                @flush();
                @ob_flush();
            }
            $retry = 0;
            $dicTmp = $this->dictionary;
            while ($retry < $maxRetry) {
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
                        /** @var $obj ChaosField */
                        $obj->reEval();
                        if ($obj->special === 'database') {
                            if ($obj->type === 'datetime') {
                                $arr[$obj->name]
                                    = PdoOne::unixtime2Sql($obj->curValue); // date('Ymd h:i:s', $obj->curValue);//date('Y-m-d H:i:s', $obj->curValue);
                            } else {
                                $arr[$obj->name] = $obj->curValue;
                            }
                        }
                    }
                }
                try {
                    $retry++;
                    $id = $this->db->insert($this->tableDB, $arr);
                    $retry = 0;
                    $this->debug("Debug: Inserting #$id");
                    break; // exit retry
                } catch (Exception $ex) {
                    $this->dictionary = $dicTmp; //rollback data
                    if ($continueOnError) {
                        $this->debug("Error: Inserting failed" . $ex->getMessage());
                    } else {
                        echo $ex->getMessage() . "\n";
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
     *
     * @return $this
     */
    public function stat()
    {
        $this->startBody();
        foreach ($this->dictionary as &$obj) {
            if ($this->isChaosField($obj)) {
                echo "<hr>Stat <b>" . $obj->name . '</b>:<br>';
                switch ($this->dictionary[$obj->name]->type) {
                    case 'int':
                    case 'decimal':
                    case 'varchar':
                        echo "Min:" . $obj->statMin . "<br>";
                        echo "Max:" . $obj->statMax . "<br>";
                        echo "Sum:" . $obj->statSum . "<br>";
                        if ($obj->statSum > 0) {
                            echo "Avg:" . ($obj->statSum / $this->maxId) . "<br>";
                        }
                        break;
                    case 'datetime':
                        echo "Min:" . date('Y-m-d H:i:s l(N)', $obj->statMin) . "<br>";
                        echo "Max:" . date('Y-m-d H:i:s l(N)', $obj->statMax) . "<br>";
                        echo "Sum:" . $obj->statSum . "<br>";
                        if ($obj->statSum > 0) {
                            echo "Avg:" . date('Y-m-d H:i:s l(N)', ($obj->statSum / $this->maxId)) . "<br>";
                        }
                        break;
                }
            }
        }
        return $this;
    }

    /**
     * If true then this value is nullable.
     *
     * @param bool $bool
     *
     * @return $this
     */
    public function isNullable($bool = true)
    {
        $this->dictionary[$this->pipeFieldName]->allowNull = $bool;
        return $this;
    }

    /**
     * It must be set after field.  If true and if insert fails, then it field is re-calculated.
     *
     * @param bool $bool
     *
     * @return ChaosMachineOne
     */
    public function retry($bool = true)
    {
        $this->dictionary[$this->pipeFieldName]->retry = $bool;
        return $this;
    }

    /**
     * If true then the field allows nulls. If false (the default value), then every null value is converted to another value (0 or '')
     *
     * @param bool $bool
     *
     * @return ChaosMachineOne
     */
    public function allowNull($bool = true)
    {
        $this->dictionary[$this->pipeFieldName]->allowNull = $bool;
        return $this;
    }

    public function speed(ChaosField $field, $v2 = null)
    {
        if (func_num_args() == 1) {
            return $field->curSpeed;
        }

        $field->curSpeed = $v2;
        return null;
    }

    public function accel(ChaosField $field, $v2 = null)
    {
        if (func_num_args() == 1) {
            return $field->curAccel;
        }

        $field->curAccel = $v2;
        return null;
    }

    public function stop(ChaosField $field, $v2 = null)
    {
        $field->curSpeed = 0;
        $field->curAccel = 0;
        $field->curValue = $v2;
    }

    public function concat(ChaosField $field, $v2 = null)
    {
        $field->curValue .= $v2;
    }

    public function plus(ChaosField $field, $v2 = null)
    {
        $this->add($field, $v2);
    }

    public function add(ChaosField $field, $v2 = null)
    {
        if ($field->type === 'datetime' && !is_numeric($v2)) {
            $last = substr($v2, -1);
            $number = substr($v2, 0, -1);
            switch ($last) {
                case 'h':
                    $field->curValue += $number * 3600; // hours
                    break;
                case 'm':
                    $field->curValue += $number * 60; // hours
                    break;
                case 'd':
                    $field->curValue += $number * 86400; // days
                    break;
                default:
                    trigger_error("add type not defined [$last] for datetime ");
            }
            return;
        }
        if ($field->type === 'string') {
            $field->curValue .= $v2;
        } else {
            $field->curValue += $v2;
        }
    }

    /**
     * this function is used for setter and getter.
     *
     * @param ChaosField $field
     * @param null       $v2
     *
     * @return int|null
     */
    public function value(ChaosField $field, $v2 = null)
    {
        if (func_num_args() == 1) {
            return $field->curValue;
        }

        $field->curValue = $v2;
        return null;
    }

    /**
     * @param ChaosField $destination
     * @param mixed      $source
     */
    public function copyfilefrom(ChaosField $destination, $source = null)
    {
        if ($this->isChaosField($source)) {
            copy($source->curValue, $destination->curValue);
        } else {
            copy($source, $destination->curValue);
        }
    }

    public function getvalue(ChaosField $field)
    {
        return $field->curValue;
    }

    public function valueabs(ChaosField $field)
    {
        $field->curValue = abs($field->curValue);
    }

    public function year(ChaosField $field)
    {
        return $this->datepart($field, 'Y');
    }

    public function datepart(ChaosField $field, $v2 = null)
    {
        //Y-m-d H:i:s
        if ($field->curValue instanceof DateTime) {
            return (int)$field->curValue->format($v2);
        }

        return (int)date($v2, $field->curValue);
    }

    public function day(ChaosField $field)
    {
        return $this->datepart($field, 'd');
    }

    /**
     * @param ChaosField $field
     * @param string     $v2 =['day','hour','monday','tuesday','wednesday','thursday','friday','saturday','sunday','month'][$i]
     */
    public function skip(ChaosField $field, $v2 = 'day')
    {
        switch ($v2) {
            case "day":
                $curhour = $this->hour($field);
                $curhour = ($curhour == 0) ? 24 : $curhour;
                $field->curValue += (24 - $curhour) * 3600; // we added the missing hours.
                break;
            case "hour":
                $curMinute = $this->minute($field);
                $curMinute = ($curMinute == 0) ? 60 : $curMinute;
                $field->curValue += (60 - $curMinute) * 60; // we added the missing minutes.
                break;
            case "month":
                $curMonth = $this->month($field);
                $curhour = $this->hour($field);
                $curhour = ($curhour == 0) ? 24 : $curhour;
                $field->curValue += (24 - $curhour) * 3600 - $this->minute($field) * 60
                    - $this->second($field); // we added the missing hours and we are close to midnight.
                if ($this->month($field) == $curMonth) {
                    for ($i = 0; $i < 31; $i++) {
                        $field->curValue += 86400; // we add a day.
                        if ($this->month($field) != $curMonth) {
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
                $p = array_search($v2, $this->daysWeek); //1 monday
                $curhour = $this->hour($field);
                $curhour = ($curhour == 0) ? 24 : $curhour;
                $field->curValue += (24 - $curhour) * 3600 - $this->minute($field) * 60
                    - $this->second($field); // we added the missing hours and we are close to midnight.
                //echo "skip ".date('Y-m-d H:i:s l(N)',$field->curValue)."<br>";
                $curweek = $this->weekday($field);
                //echo "skipping to $p $curhour curweek $curweek ".((24-$curhour)*3500)."<br>";
                //die(1);
                if ($curweek != $p) {
                    $curday = $this->weekday($field);
                    $field->curValue += (7 + $p - $curday) * 86400; // we added the missing days.
                }
                //echo "skip ".date('Y-m-d H:i:s l(N)',$field->curValue)."<br>";
                break;
        }
    }

    public function hour(ChaosField $field)
    {
        // hour (24 hours)
        return $this->datepart($field, 'H');
    }

    public function minute(ChaosField $field)
    {
        // hour (24 hours)
        return $this->datepart($field, 'i');
    }

    public function month(ChaosField $field)
    {
        return $this->datepart($field, 'm');
    }

    public function second(ChaosField $field)
    {
        // hour (24 hours)
        return $this->datepart($field, 's');
    }

    public function weekday(ChaosField $field)
    {
        // 1= monday, 7=sunday
        return $this->datepart($field, 'N');
    }

    /**
     * It starts the flow with a table.
     *
     * @param string           $table  name of the table
     * @param int|string|array $origin
     *                                 <p>if it is int then it sets the number of rows to generate.</p>
     *                                 <p>if it is string then it sets a table to read</p>
     *                                 <p>if it is an array then it sets the array to loop</p>
     *                                 <p>Example: table('customers','select * from people','prefix')<p>
     * @param string           $prefix prefix of the rows to read. If the origin is an array then it is used as the name of the variable
     *
     * @return $this
     */
    public function table($table, $origin, $prefix = 'origin_')
    {
        $this->tableDB = $table;
        if (is_int($origin)) {
            $this->maxId = $origin;
            $this->queryTable = null;
            $this->queryPrefix = null;
        } elseif (is_array($origin)) {
            $this->maxId = count($origin);
            $this->queryTable = $origin;
            $this->queryPrefix = $prefix;
        } else {
            $this->maxId = -1;
            if (stripos($origin, 'select') === false) {
                $origin = "select * from $origin";
            } // is the table name
            $this->queryTable = $origin;
            $this->queryPrefix = $prefix;
        }

        $this->cacheResult = null;
        return $this;
    }

    #region Range functions

    /**
     * @param string $tableName
     *
     * @return string
     * @throws Exception
     */
    public function generateCode($tableName)
    {
        $columns = $this->db->columnTable($tableName);
        $fks = $this->db->foreignKeyTable($tableName);
        $code = "\$chaos->table('$tableName', 1000)\n";
        $code .= "\t\t->setDb(\$db)\n";
        // set fields
        $colShow = '';
        foreach ($columns as $k => $column) {
            $columns[$k]['coltype'] = $this->translateColType($column['coltype']);
            $coltype = $columns[$k]['coltype'];

            if ($coltype !== 'nothing') {
                $colShow .= '\'' . $column['colname'] . '\',';
                if ($column['isidentity']) {
                    $code .= "\t\t->field('" . $column['colname'] . "', '" . $coltype . "','identity', 0)\n";
                } else {
                    $nullable = ($column['isnullable']) ? "\n\t\t\t->isnullable(true)" : '';
                    switch ($coltype) {
                        case 'datetime':
                            $code .= "\t\t->field('" . $column['colname'] . "', '" . $coltype
                                . "','database',new DateTime('now'))$nullable\n";
                            break;
                        case 'int':
                        case 'decimal':
                            $code .= "\t\t->field('" . $column['colname'] . "', '" . $coltype
                                . "','database')$nullable\n";
                            break;
                        case 'string':
                            $code .= "\t\t->field('" . $column['colname'] . "', '" . $coltype
                                . "','database','',0,{$column['colsize']})$nullable\n";
                            break;
                        default:
                            $code .= "\t\t // " . $column['colname'] . " type $coltype not defined\n";
                            break;
                    }
                }
            }
        }
        // set arrays
        foreach ($fks as $fk) {
            $code .= "\t\t->setArrayFromDBTable('array_" . $fk['collocal'] . "','" . $fk['tablerem'] . "','"
                . $fk['colrem'] . "')\n";
        }
        // generation
        foreach ($fks as $fk) {
            $code .= "\t\t->gen('when always set " . $fk['collocal'] . ".value=randomarray(\"array_" . $fk['collocal']
                . "\")')\n";
        }
        foreach ($columns as $k => $column) {
            $name = $column['colname'];
            $size = $column['colsize'];
            $found = false;
            foreach ($fks as $fk) {
                if ($fk['collocal'] == $name) {
                    $found = true; // it is a foreign key.
                    break;
                }
            }
            if (!$found && !$column['isidentity']) {
                switch ($column['coltype']) {
                    case 'int':
                        $code .= "\t\t->gen('when always set {$name}.value=random(1,100,1,10,10)')\n";
                        break;
                    case 'decimal':
                        $code .= "\t\t->gen('when always set {$name}.value=random(1,100,0.1,10,10)')\n";
                        break;
                    case 'datetime':
                        $code .= "\t\t->gen('when always set {$name}.speed=random(3600,86400)')\n";
                        break;
                    case 'string':
                        $code .= "\t\t->gen('when always set {$name}.value=random(0,{$size})')\n";
                        break;
                    case 'nothing':
                        break;
                    default:
                        $code .= "\t\t// {$name} not defined for type {$column['coltype']}\n";
                        break;
                }
            }
        }
        $colShow = rtrim($colShow, ','); // we remove the last ","
        $code .= "\t\t->setInsert(true)\n";
        $code .= "\t\t->showTable([$colShow],true)\n";
        $code .= "\t\t->run(true);\n";
        return $code;
    }

    /**
     * @param $colType
     *
     * @return string
     */
    private function translateColType($colType)
    {
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

    /**
     * It returns the current timestamp. Example: 1558625207
     *
     * @return int
     */
    public function now()
    {
        return time();
    }

    /**
     * @param string $dateString 2012-01-18 11:45:00
     *
     * @return false|int
     */
    public function date($dateString)
    {
        return date("U", strtotime($dateString));
    }

    /**
     * Returns an array with the list of files (not recursive) of a folder.
     *
     * @param              $folder
     * @param string|array $ext                 (example "jpg","doc" or ["jpg","doc"])
     * @param bool         $returnWithExtension If false then it returns the file name without the extension
     * @param bool         $shuffle             if true then the results are shuffled
     *
     * @return array
     */
    public function arrayFromFolder($folder, $ext = "*", $returnWithExtension = true, $shuffle = false)
    {
        $files = scandir($folder);
        $result = [];
        foreach ($files as $file) {
            $pi = pathinfo($file);
            if (@$pi['extension']) {
                if (is_array($ext)) {
                    if (in_array($pi['extension'], $ext, true)) {
                        if ($returnWithExtension) {
                            $result[] = $pi['basename'];
                        } else {
                            $result[] = $pi['filename'];
                        }
                    }
                } elseif ($pi['extension'] == $ext || $ext === '*') {
                    if ($returnWithExtension) {
                        $result[] = $pi['basename'];
                    } else {
                        $result[] = $pi['filename'];
                    }
                }
            }
        }
        if ($shuffle) {
            shuffle($result);
        }

        return $result;
    }

    /**
     * It converts a string to a timestamp.
     *
     * @param $dateTxt
     *
     * @return false|int
     */
    public function createDate($dateTxt)
    {
        return strtotime($dateTxt);
    }

    /**
     * It creates a ramp value.
     *
     * @param int|float $fromX
     * @param int|float $toX
     * @param int|float $fromY
     * @param int|float $toY
     *
     * @return float|int
     */
    public function ramp($fromX, $toX, $fromY, $toY)
    {
        $deltaX = $toX - $fromX; // 0 100 = 100
        $deltaY = $toY - $fromY; // 0 10 = 10
        $idx = $this->dictionary['_index']; // 10
        $idxDelta = $idx - $fromX; // 10-0 = 10
        return ($deltaY / $deltaX) * $idxDelta + $fromY;         // 10/100*10 = 1 200/990 x 100
    }

    /**
     * It creates a log value based in a range of values
     *
     * @param int|float $startX
     * @param int|float $startY
     * @param int|float $scale
     *
     * @return float|int
     */
    public function log($startX, $startY, $scale = 1)
    {
        $idx = $this->dictionary['_index']; // 10
        $idxDelta = $idx - $startX; // 10-0 = 10
        if ($idxDelta == 0) {
            $value = $startY;
        } else {
            $value = (log($idxDelta) * $scale) + $startY;
        }
        return $value;
    }

    public function exp($startX, $startY, $scale = 1)
    {
        $idx = $this->dictionary['_index']; // 10
        $idxDelta = $idx - $startX; // 10-0 = 10
        return (exp($idxDelta / $scale)) + $startY;
    }

    public function sin($startX, $startY, $speed = 1, $scale = 1)
    {
        $idx = $this->dictionary['_index']; // 10
        $idxDelta = $idx - $startX; // 10-0 = 10
        return (sin($idxDelta * 0.01745329251 * $speed) * $scale) + $startY;
    }

    public function atan($centerX, $startY, $speed = 1, $scale = 1)
    {
        $idx = $this->dictionary['_index']; // 10
        $idxDelta = $idx - $centerX; // 10-0 = 10
        return (atan($idxDelta * 0.01745329251 * $speed) * $scale) + $startY;
    }

    public function parabola($centerX, $startY, $scaleA = 1, $scaleB = 1, $scale = 1)
    {
        $idx = $this->dictionary['_index']; // 10
        $idxDelta = $idx - $centerX; // 10-0 = 10
        return ($idxDelta * $idxDelta * $scaleA + $idxDelta * $scaleB) * $scale + $startY;
    }
    #endregion
    #region fixed function

    /**
     * Bell generation of numbers.
     *
     * @param     $centerX
     * @param     $startY
     * @param int $sigma
     * @param int $scaleY
     *
     * @return float|int
     */
    public function bell($centerX, $startY, $sigma = 1, $scaleY = 1)
    {
        $idx = $this->dictionary['_index']; // 10
        return $this->normal($idx, $centerX, $sigma) * $scaleY + $startY;
    }

    /**
     * It calculates the normal of a bell operation
     *
     * @param $x
     * @param $mu
     * @param $sigma
     *
     * @return float|int
     */
    public function normal($x, $mu, $sigma)
    {
        return exp(-0.5 * ($x - $mu) * ($x - $mu) / ($sigma * $sigma)) / ($sigma * sqrt(2.0 * M_PI));
    }

    public function randommaskformat($formatName, $arrayName = '')
    {
        $txt = $this->randomformat($formatName);
        return $this->randommask($txt, $arrayName);
    }

    /**
     * @param      $formatName
     *
     * @return string|string[]|null
     */
    public function randomformat($formatName)
    {
        if (!$this->isFormat($formatName)) {
            trigger_error("Format [$formatName] not defined");
            return "";
        }
        if ($this->formatsProportional[$formatName] != null) {
            $ap = $this->formatsProportional[$formatName];
            $max = end($ap);
            $idPos = mt_rand(0, $max);
            $idx = 0;
            foreach ($ap as $k => $v) {
                if ($idPos < $v) {
                    $idx = $k;
                    break;
                }
            }
            $format = $this->formats[$formatName][$idx];
        } else {
            $c = count($this->formats[$formatName]);
            $idx = mt_rand(0, $c - 1);
            $format = $this->formats[$formatName][$idx];
        }
        return $this->parseFormat($format);
    }

    public function isFormat($formatName)
    {
        return isset($this->formats[$formatName]);
    }

    public function randommask($mask, $arrayName = '')
    {
        $txt = '';
        $c = strlen($mask);
        $escape = false;
        for ($i = 0; $i < $c; $i++) {
            $m = $mask[$i];
            if (!$escape) {
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
                    case 'v':
                        if (mt_rand(0, 1) === 0) {
                            $txt .= strtolower($this->randMiniArr(self::ALPHA));
                        } else {
                            $txt .= $this->randMiniArr(self::ALPHA);
                        }
                        break;
                    case 'w':
                        if (mt_rand(0, 1) === 0) {
                            $txt .= strtolower($this->randMiniArr(array_merge(self::ALPHA, self::NUM)));
                        } else {
                            $txt .= $this->randMiniArr(array_merge(self::ALPHA, self::NUM));
                        }
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
                        } elseif ($this->isFormat($arrayName)) {
                            $txt .= $this->randomformat($arrayName);
                        } else {
                            trigger_error("Array or Format [$arrayName] not defined for ?");
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

    private function randMiniArr($arr)
    {
        $c = count($arr) - 1;
        return $arr[mt_rand(0, $c)];
    }

    public function isArray($arrayName)
    {
        return isset($this->dictionaryProportional[$arrayName]);
    }

    /**
     * @param string      $arrayName The name contained in $this->dictionary.
     * @param null|string $fieldName [optional] the name of the field.
     *
     * @return mixed|string
     */
    public function randomarray($arrayName, $fieldName = null)
    {
        if (!$this->isArray($arrayName)) {
            trigger_error("Array [$arrayName] not defined");
            return "";
        }
        if (count($this->dictionary[$arrayName]) === 0) {
            // empty array
            return "";
        }
        if (isset($this->keepRandomArray[$arrayName])) {
            return $this->keepRandomArray[$arrayName]; // already calculated.
        }
        if ($this->dictionaryProportional[$arrayName] !== null) {
            // its an array proportional ['value1'=>30,'value2'=>20...]
            $ap = $this->dictionaryProportional[$arrayName]; // ['col'=[5,20,20]]
            $max = end($ap);
            $idPos = mt_rand(0, $max);
            $idx = 0;
            // We convert random probabilities into absolute base zero (until we reach our random position)
            // [5,10,10]=>[5,15,25] or not?
            foreach ($ap as $k => $v) {
                if ($idPos < $v) {
                    $idx = $k;
                    break;
                }
            }
        } else {
            $c = count($this->dictionary[$arrayName]);
            $idx = mt_rand(0, $c - 1);
        }

        if ($fieldName == null) {
            $this->keepRandomArray[$arrayName] = $this->dictionary[$arrayName][$idx];
            return $this->dictionary[$arrayName][$idx];
        }

        $this->keepRandomArray[$arrayName] = $this->dictionary[$arrayName][$idx]->{$fieldName};
        return $this->dictionary[$arrayName][$idx]->{$fieldName};
    }

    /**
     * It returns a random index of an array.<br>
     * <b>Example:</b><br>
     * <pre>
     * // $values_arr=['a','b','c']
     * $key=$this->randomarrayKey('values_arr'); // it returns: 0,1 or 2
     * $this->getArray('values_arr',$key); // it could returns 'b'
     * </pre>
     *
     * @param string $arrayName
     *
     * @return int|string
     */
    public function randomArrayKey($arrayName)
    {
        if (!$this->isArray($arrayName)) {
            trigger_error("Array [$arrayName] not defined");
            return "";
        }
        if (count($this->dictionary[$arrayName]) === 0) {
            // empty array
            return 0;
        }
        if ($this->dictionaryProportional[$arrayName] !== null) {
            // its an array proportional ['value1'=>30,'value2'=>20...]
            $ap = $this->dictionaryProportional[$arrayName]; // ['col'=[5,20,20]]
            $max = end($ap);
            $idPos = mt_rand(0, $max);
            $idx = 0;
            // We convert random probabilities into absolute base zero (until we reach our random position)
            // [5,10,10]=>[5,15,25] or not?
            foreach ($ap as $k => $v) {
                if ($idPos < $v) {
                    $idx = $k;
                    break;
                }
            }
        } else {
            $c = count($this->dictionary[$arrayName]);
            $idx = mt_rand(0, $c - 1);
        }
        return $idx;
    }

    /**
     * It gets a value of an array<br>
     * <b>Example:</b><br>
     * <pre>
     * // $values_arr=['a','b','c']
     * $this->getArray('values_arr',1); // it returns: 'b'
     * </pre>
     *
     * @param string     $arrayName
     * @param string|int $index
     *
     * @param null|mixed $default
     *
     * @return mixed|string
     * @see \eftec\chaosmachineone\ChaosMachineOne::randomarrayKey
     */
    public function getArray($arrayName, $index, $default = null)
    {
        if (!$this->isArray($arrayName)) {
            trigger_error("Array [$arrayName] not defined");
            return "";
        }
        if (!isset($this->dictionary[$arrayName][$index])) {
            if ($default === null) {
                // empty array
                trigger_error("Array [$arrayName][$index] out of bound");
                return "";
            }

            return $default;
        }
        return $this->dictionary[$arrayName][$index];
    }

    /**
     * Random proportional
     *
     * @param array $args . Example (1,2,3,30,30,40), where the changes are 1--30%, 2--30%, 3--40%
     *
     * @return mixed
     */
    public function randomprop(...$args)
    {
        $c = count($args);
        $m = $c / 2;
        $sum = 0;

        for ($i = $m; $i < $c; $i++) {
            $sum += $args[$i];
        }
        $rnd = mt_rand(0, $sum);
        $counter = $sum;
        for ($i = $c - 1; $i >= $m; $i--) {
            $counter -= $args[$i];
            if ($rnd >= $counter) {
                return $this->argValue($args[$i - $m]);
            }
        }
        return $args[0];
    }

    /**
     * We pass an argument number, text or variable and returned the current value.
     *
     * @param $arg
     *
     * @return int
     */
    private function argValue($arg)
    {
        if ($arg instanceof ChaosField) {
            return $arg->curValue;
        }
        return $arg;
    }

    /**
     * Returns a value from an array
     *
     * @param      $arrayName
     * @param null $index
     *
     * @return mixed|string
     */
    public function arrayindex($arrayName, $index = null)
    {
        if (!$this->isArray($arrayName)) {
            trigger_error("Array [$arrayName] not defined");
            return "";
        }
        if ($index === null) {
            $index = $this->dictionary['_index'];
        }

        if (!$this->dictionary[$arrayName][$index]) {
            trigger_error("Array [$arrayName] exists but [$index] is not defined.");
            return "";
        }
        return $this->dictionary[$arrayName][$index];
    }

    public function randomtext(
        $startLorem = 'Lorem ipsum dolor',
        $arrayName = '',
        $paragraph = false,
        $nWordMin = 20,
        $nWordMax = 40
    ) {
        if ($arrayName === '') {
            $array = $this->_loremIpsumArray();
        } else {
            $array = $this->dictionary[$arrayName];
        }

        if ($startLorem !== '') {
            $counter = 3;
            $u = false;
            $txt = $startLorem . ' ';
        } else {
            $u = true;
            $counter = 0;
            $txt = '';
        }
        $c = count($array);
        $nWords = mt_rand($nWordMin, $nWordMax);
        for ($i = $counter; $i < $nWords; $i++) {
            $r = mt_rand(0, $c - 1);
            $newWord = $array[$r];
            $newWord = ($u) ? ucfirst($newWord) : $newWord;
            $txt .= $newWord;
            $r2 = mt_rand(0, 6);
            $u = false;
            if ($i + 3 < $nWordMax) {
                $r2 = 5; // normal word (at the end of the phrase).
            }
            switch ($r2) {
                case 0:
                    $txt .= '. ';
                    if ($paragraph && mt_rand(0, 4) == 0) {
                        $txt .= "\n";
                    }
                    $u = true;
                    break;
                case 1:
                    $txt .= ', ';
                    break;
                default:
                    $txt .= ' ';
            }
        }
        $txt .= trim($txt) . '.';
        return $txt;
    }

    protected function _loremIpsumArray()
    {
        return [
            'a',
            'ac',
            'accumsan',
            'adipiscing',
            'aliquam',
            'aliquet',
            'amet',
            'ante',
            'anteinterdum',
            'arcu',
            'at',
            'augue',
            'commodo',
            'condimentum',
            'congue',
            'consectetur',
            'consequat',
            'cras',
            'cursus',
            'dapibus',
            'diam',
            'dolor',
            'donec',
            'dui',
            'duis',
            'efficitur',
            'eget',
            'eleifend',
            'elementum',
            'elit',
            'enim',
            'erat',
            'eros',
            'est',
            'et',
            'eu',
            'euismod',
            'ex',
            'facilisi',
            'facilisis',
            'fames',
            'faucibus',
            'felis',
            'fermentum',
            'feugiat',
            'finibus',
            'fusce',
            'gravida',
            'iaculis',
            'iaculisut',
            'id',
            'in',
            'integer',
            'interdum',
            'ipsum',
            'justo',
            'lacus',
            'laoreet',
            'lectus',
            'leo',
            'libero',
            'ligula',
            'lobortis',
            'lorem',
            'luctus',
            'maecenas',
            'magna',
            'malesuada',
            'massa',
            'mattis',
            'mauris',
            'maximus',
            'metus',
            'mi',
            'molestie',
            'mollis',
            'nam',
            'nec',
            'neque',
            'nibh',
            'nisi',
            'nisl',
            'non',
            'nulla',
            'nullam',
            'nunc',
            'odio',
            'orci',
            'ornare',
            'pellentesque',
            'pharetra',
            'phasellus',
            'placerat',
            'porta',
            'porttitor',
            'praesent',
            'pretium',
            'primis',
            'proin',
            'pulvinar',
            'purus',
            'quam',
            'quis',
            'quisque',
            'rhoncus',
            'risus',
            'sagittis',
            'sapien',
            'scelerisque',
            'sed',
            'sem',
            'semper',
            'sit',
            'sollicitudin',
            'suscipit',
            'suspendisse',
            'tellus',
            'tempor',
            'tempus',
            'tincidunt',
            'tinciduntnulla',
            'tortor',
            'tristique',
            'turpis',
            'ullamcorper',
            'ultrices',
            'urna',
            'ut',
            'varius',
            'vehicula',
            'vel',
            'velit',
            'venenatis',
            'vestibulum',
            'vitae',
            'vivamus',
            'viverra',
            'volutpat'
        ];
    }

    /**
     * Gets a random number<br>
     * <b>Example:</b><br>
     * <pre>
     * $this->random(0,100); // a random value that returns an integer
     * $this->random(0,100,0.1); // a random value that returns an decimal (1 decimal)
     * $this->random(0,100,1,80,20); // more chances to obtain a small number
     * $this->random(0,100,1,1,3,1); // a bell
     * </pre>
     *
     * @param int|double $from  initial number
     * @param int|double $to    final number
     * @param int|double $jump  jumps between values. If we want decimal numbers, then use 0.1
     * @param int|null   $prob0 (optional) Probability of the first 1/3
     * @param int|null   $prob1 (optional) Probability of the second 1/3
     * @param int|null   $prob2 (optional) Probability of the third 1/3
     *
     * @return float|int|string
     */
    public function random($from, $to, $jump = 1, $prob0 = null, $prob1 = null, $prob2 = null)
    {
        $segment = $this->getRandomSegment($prob0, $prob1, $prob2);
        if ($segment === null) {
            $r = mt_rand($from / $jump, $to / $jump) * $jump;
        } else {
            $numSegment = 1;
            if ($prob2 !== null) {
                $numSegment = 3;
            } elseif ($prob1 !== null) {
                $numSegment = 2;
            }
            $delta = ($to - $from) / $numSegment; // 12-24 12(delta=4) = 0+12..3+12 ,4+12..7+12,8+12..12+12

            $init = $segment * $delta + $from;
            $end = ($segment + 1) * $delta + $from;

            $r = mt_rand(round($init / $jump), round($end / $jump)) * $jump;
        }
        $this->pipeValue += $r;
        return $r;
    }

    /**
     * We have from 1 to 3 segments and we return one of them.<br>
     * <b>Example:</b><br>
     * <pre>
     * $this->getRandomSegment(10,20,30);
     * $this->getRandomSegment(10); // it always returns the first segment
     * $this->getRandomSegment(30,70); // it returns the first (30%) or second segment (70%)
     * </pre>
     *
     *
     * @param null|int $prob0 The probability to obtain the first segment
     * @param null|int $prob1 The probability to obtain the first segment
     * @param null|int $prob2 The probability to obtain the first segment
     *
     * @return int|null It returns 0,1,2, or null if no segments.
     *
     */
    private function getRandomSegment($prob0 = null, $prob1 = null, $prob2 = null)
    {
        if ($prob0 === null) {
            return null;
        }
        $segment = mt_rand(0, $prob0 + $prob1 + $prob2);
        if ($segment <= $prob0) {
            return 0;
        }
        if ($segment <= $prob0 + $prob1) {
            return 1;
        }
        return 2;
    }

    public function endPipe()
    {
        return $this;
    }

    protected function callRandomArray($matches)
    {
        if ($this->isArray($matches[1])) {
            return $this->randomarray($matches[1]);
        }

        return $this->getDictionary($matches[1])->curValue;
    }


    /**
     * @param string|null $indexName if null then it returns all dictionar
     *
     * @return ChaosField|mixed|array
     */
    public function getDictionary($indexName = null)
    {
        if ($indexName === null) {
            return $this->dictionary;
        }
        return $this->dictionary[$indexName];
    }

    #endregion

    /**
     * @param string           $index
     * @param mixed|ChaosField $values
     *
     * @return ChaosMachineOne
     */
    public function setDictionary($index, $values)
    {
        $this->dictionary[$index] = $values;
        return $this;
    }
}