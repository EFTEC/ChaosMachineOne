<?php
use eftec\chaosmachineone\ChaosMachineOne;

include 'common.php';

// invoice has at least one detail

class ChaosDetail1 extends ChaosMachineOne {
    public function getMonth($idInvoice) {
        global $db;
        try {
            $r = $db->runRawQuery('select month(creationdate) v from invoices where idinvoice=' . $idInvoice,[],true);  
            if(is_array($r) || count($r)>0) {
                $r=$r[0]['v'];
            } else {
                $r=1;
            }
        } catch (Exception $e) {
            var_dump($e->getMessage());
            $r=1;
        }
        return $r;
    }
}

$chaos=new ChaosDetail1();
$chaos->setDb($db);

$chaos->table('InvoiceDetails',17000)
		->setDb($db)
        ->setFormat('refcodeformat',['{{randomnumber}}'=>1,''=>99])
		->field('idInvoiceDetail', 'int','identity', 0)
		->field('idInvoice', 'int','database')
		->field('sku', 'int','database')
		->field('quantity', 'decimal','database')
		->field('unitPrice', 'decimal','database')
		->field('refCode', 'string','database','',0,50)
		->field('lastUpdate', 'datetime','database',new DateTime('now'))
        ->field('indexsku', 'int','local')
        ->field('randomnumber','int','local')
        ->field('invoicemonth','int','local')
		->setArrayFromDBTable('array_idInvoice','Invoices','idInvoice')
		->setArrayFromDBTable2('array_sku','array_skuprice','Skus','sku','unitPrice','leftbias')
        ->gen('when always set randomnumber.value=random(5000,99999)')
		->gen('when always set idInvoice.value=_index+1')
        ->gen('when always set invoicemonth.value=getMonth(idInvoice.value)')
        ->gen('when always set indexsku.value=randomArrayKey("array_skuprice")')
		->gen('when always set sku.value=getarray("array_sku",indexsku.value)')
		->gen('when invoicemonth.value>=4 and invoicemonth.value<=8 set quantity.value=random(1,5,1,50,1) else quantity.value=random(1,10,1,30,30)')
		->gen('when always set unitPrice.value=getarray("array_skuprice",indexsku.value)')
		->gen('when always set refCode.value=randomformat("refcodeformat")')
		->gen('when always set lastUpdate.speed=0')
		->setInsert(false)
		->showTable(['idInvoiceDetail','idInvoice','sku','invoicemonth','quantity','unitPrice','refCode','lastUpdate'],true)
		->run(true);
