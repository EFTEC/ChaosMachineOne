<?php
use eftec\chaosmachineone\ChaosMachineOne;

include 'common.php';

@set_time_limit(2000);

/*
  * SET @i=0;
UPDATE Invoices SET idInvoice=(@i:=@i+1);
  */

//$db->from('invoicedetails')->where('1=1')->delete();

$invoices=$db->select('idinvoice,year(creationdate) year,month(creationdate) month')->from('invoices')->toList();


foreach($invoices as $kounter=>$inv) {
    $invoice=$inv['idinvoice'];
    $yearinflation=($inv['year']-2006)*5/100; // %5 annual
    $month=($inv['month']); // used to determine a trend.

    $chaos = new ChaosMachineOne(123);
    $empnu = $chaos->random(1, 7, 1, 50, 1); // from 1 to 7 invoices details per invoice


    $chaos->table('InvoiceDetails', $empnu)
        ->setDb($db)
        ->setFormat('refcodeformat',['{{randomnumber}}'=>1,''=>99])
        ->field('idInvoice', 'int', 'database')
        ->field('idInvoiceDetail', 'int', 'identity', 0)
        ->field('lastUpdate', 'datetime', 'database', new DateTime('now'))
        ->field('quantity', 'decimal', 'database')
        ->field('refCode', 'string', 'database', '', 0, 50)
        ->field('idProduct', 'int', 'database')
        ->field('unitPrice', 'decimal', 'database')
        ->field('keyproduct','int','local')
        ->field('randomnumber','int','local')
        ->field('invoicemonth','int','local',$month) // it is calculated to mark some trends (more purchased during some months)
        //->setArrayFromDBTable('array_idInvoice', 'Invoices', 'idInvoice')
        ->setArrayFromDBTable2('array_product','array_productprice', 'Products','IdProduct','unitprice','leftbias')
        ->gen('when always set randomnumber.value=random(5000,99999)')
        ->gen('when always set keyproduct.value=randomArrayKey("array_product")') /** @see \eftec\chaosmachineone\ChaosMachineOne::randomArrayKey **/
        ->gen('when always set idInvoice.value='.$invoice)
        ->gen('when always set idProduct.value=getArray("array_product",keyproduct.value)')
        ->gen('when always set unitPrice.value=getArray("array_productprice",keyproduct.value,15)*$yearinflation')
        ->gen('when always set lastUpdate.speed=1')
        ->gen('when invoicemonth.value>=4 and invoicemonth.value<=8 set quantity.value=random(1,5,1,50,1) else quantity.value=random(1,10,1,30,30)')
        ->gen('when always set refCode.value=randomformat("refcodeformat")')

        ->setInsert(true)
        /*->showTable([
            'idInvoice',
            'idInvoiceDetail',
            'lastUpdate',
            'quantity',
            'refCode',
            'idProduct',
            'unitPrice'
        ], true)*/
        ->run(true);
    echo "<h1>$invoice $kounter of ".count($invoices)." : $empnu</h1>";
}