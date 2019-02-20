<?php

namespace CleverIt\UBL\Invoice\Tests;

use Greenter\Ubl\UblValidator;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
    private $invoice;

    public function setUp(){
        $xmlService = new \Sabre\Xml\Service();

        $xmlService->namespaceMap = [
            'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2' => '',
            'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2' => 'cbc',
            'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2' => 'cac'
        ];

        $invoice = new \CleverIt\UBL\Invoice\Invoice();
        $date = \DateTime::createFromFormat('d-m-Y', '12-12-1994');
        $invoice->setId('CIT1234');
        $invoice->setIssueDate($date);
        $invoice->setInvoiceTypeCode("SalesInvoice");

        $accountingSupplierParty = new \CleverIt\UBL\Invoice\Party();
        $accountingSupplierParty->setName('CleverIt');
        $supplierAddress = (new \CleverIt\UBL\Invoice\Address())
            ->setCityName("Eindhoven")
            ->setStreetName("Keizersgracht")
            ->setBuildingNumber("15")
            ->setPostalZone("5600 AC")
            ->setCountry((new \CleverIt\UBL\Invoice\Country())->setIdentificationCode("NL"));
        $legalEntity = new \CleverIt\UBL\Invoice\LegalEntity();
        $legalEntity->setRegistrationName('Some Company');
        $legalEntity->setCompanyId('12345678');
        $legalEntity->setCompanyIdSchemeId('0106');

        $accountingSupplierParty->setPostalAddress($supplierAddress);
        $accountingSupplierParty->setPhysicalLocation($supplierAddress);
        $accountingSupplierParty->setContact((new \CleverIt\UBL\Invoice\Contact())->setElectronicMail("info@cleverit.nl")->setTelephone("31402939003"));
        $accountingSupplierParty->setLegalEntity($legalEntity);

        $invoice->setAccountingSupplierParty($accountingSupplierParty);
        $invoice->setAccountingCustomerParty($accountingSupplierParty);

        $taxtotal = (new \CleverIt\UBL\Invoice\TaxTotal())
            ->setTaxAmount(30)
            ->addTaxSubTotal((new \CleverIt\UBL\Invoice\TaxSubTotal())
                ->setTaxAmount(21)
                ->setTaxableAmount(100)
                ->setTaxCategory((new \CleverIt\UBL\Invoice\TaxCategory())
                    ->setId("H")
                    ->setName("NL, Hoog Tarief")
                    ->setPercent(21.00)
                    ->setTaxScheme((new \CleverIt\UBL\Invoice\TaxScheme())
                        ->setId('VAT'))))
            ->addTaxSubTotal((new \CleverIt\UBL\Invoice\TaxSubTotal())
                ->setTaxAmount(9)
                ->setTaxableAmount(100)
                ->setTaxCategory((new \CleverIt\UBL\Invoice\TaxCategory())
                    ->setId("X")
                    ->setName("NL, Laag Tarief")
                    ->setPercent(9.00)
                    ->setTaxScheme((new \CleverIt\UBL\Invoice\TaxScheme())
                        ->setId('VAT'))));

        $invoiceLine1 = (new \CleverIt\UBL\Invoice\InvoiceLine())
            ->setId(1)
            ->setInvoicedQuantity(1)
            ->setLineExtensionAmount(100)
            ->setTaxTotal($taxtotal)
            ->setItem((new \CleverIt\UBL\Invoice\Item())
                ->setName("Test item")
                ->setDescription("test item description")
                ->setSellersItemIdentification("1ABCD")
                ->setClassifiedTaxCategory((new \CleverIt\UBL\Invoice\TaxCategory())
                    ->setId('S')
                    ->setPercent('21.00')
                    ->setTaxScheme((new \CleverIt\UBL\Invoice\TaxScheme())
                        ->setId('VAT')
                    )
                )
            );

        $invoiceLine2 = (new \CleverIt\UBL\Invoice\InvoiceLine())
            ->setId(2)
            ->setInvoicedQuantity(1)
            ->setLineExtensionAmount(100)
            ->setItem((new \CleverIt\UBL\Invoice\Item())
                ->setName("Test item")
                ->setDescription("test item description")
                ->setSellersItemIdentification("1ABCD")
                ->setClassifiedTaxCategory((new \CleverIt\UBL\Invoice\TaxCategory())
                    ->setId('S')
                    ->setPercent('21.00')
                    ->setTaxScheme((new \CleverIt\UBL\Invoice\TaxScheme())
                        ->setId('VAT')
                    )
                )
            );

        $invoice->setInvoiceLines([$invoiceLine1, $invoiceLine2]);
        $invoice->setTaxTotal($taxtotal);
        $invoice->setLegalMonetaryTotal((new \CleverIt\UBL\Invoice\LegalMonetaryTotal())
            ->setLineExtensionAmount(100)
            ->setTaxExclusiveAmount(100)
            ->setPayableAmount(-1000)
            ->setAllowanceTotalAmount(50));

        $additionalDocumentReference = new \CleverIt\UBL\Invoice\AdditionalDocumentReference();
        $additionalDocumentReference->setId('invoice.pdf');
        $additionalDocumentReference->setDocumentType('PrimaryImage');
        $additionalDocumentReference->setFilename('invoice.pdf');
        $additionalDocumentReference->setAttachment('base');

        $invoice->setAdditionalDocumentReference($additionalDocumentReference);


        $this->invoice = \CleverIt\UBL\Invoice\Generator::invoice($invoice, 'EUR');
    }

    public function testInvoiceIsGenerated()
    {

        $this->assertXmlStringEqualsXmlFile(__DIR__ . "/ubl.xml", $this->invoice);
    }

    public function testValidateSchema(){
        $validator = new UblValidator();
        $validator->isValid($this->invoice);
        $this->assertTrue($validator->isValid($this->invoice));
    }
}
