<?php
/**
 * Created by PhpStorm.
 * User: baselbers
 * Date: 26-10-2017
 * Time: 21:45
 */

namespace CleverIt\UBL\Invoice;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

class LegalEntity implements XmlSerializable
{

    /**
     * @var string
     */
    private $registrationName;

    /**
     * @var int
     */
    private $companyId;

    /**
     * @var string
     */
    private $schemeID;

    public function getRegistrationName()
    {
        return $this->registrationName;
    }

    public function setRegistrationName($registrationName)
    {
        $this->registrationName = $registrationName;
    }

    public function getCompanyId()
    {
        return $this->companyId;
    }

    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }

    public function setCompanyIdSchemeId($schemeID)
    {
        $this->schemeID = $schemeID;
    }

    public function xmlSerialize(Writer $writer)
    {
        $writer->write([
            Schema::CBC . 'RegistrationName' => $this->registrationName
        ]);
        $writer->write([
            'name' => Schema::CBC . 'CompanyID',
            'value' => $this->companyId,
            'attributes' => [
                'schemeID' => $this->schemeID
            ]
        ]);
    }
}
