<?php

namespace Apps\Tms\Packages\Companies\Model;

use System\Base\BaseModel;
use System\Base\Providers\BasepackagesServiceProvider\Packages\Model\BasepackagesAddressBook;
use System\Base\Providers\BasepackagesServiceProvider\Packages\Model\BasepackagesContactBook;

class AppsTmsCompanies extends BaseModel
{
    protected $modelRelations = [];

    public $id;

    public $business_type;

    public $reference;

    public $logo;

    public $name;

    public $description;

    public $company_phone_1;

    public $company_phone_2;

    public $company_fax;

    public $company_website;

    public $company_email;

    public $gst;

    public $gst_date;

    public $pan;

    public $pan_date;

    public $reg;

    public $reg_date;

    public $vendor_codes;

    public $payment_days;

    public $payment_terms;

    public $invoice_terms;

    public $archived;

    public function initialize()
    {
        $this->modelRelations['contacts']['relationObj'] = $this->hasMany(
            'id',
            BasepackagesContactBook::class,
            'package_row_id',
            [
                'alias'                 => 'contacts',
                'params'                => [
                    'conditions'        => 'package_name = :package_name:',
                    'bind'              => [
                        'package_name'  => 'TMSContacts'
                    ]
                ]
            ]
        );

        $this->modelRelations['addresses']['relationObj'] = $this->hasMany(
            'id',
            BasepackagesAddressBook::class,
            'package_row_id',
            [
                'alias'                 => 'addresses',
                'params'                => [
                    'conditions'        => 'package_name = :package_name:',
                    'bind'              => [
                        'package_name'  => 'TMSCompanies'
                    ]
                ]
            ]
        );

        parent::initialize();
    }

    public function getModelRelations()
    {
        if (count($this->modelRelations) === 0) {
            $this->initialize();
        }

        return $this->modelRelations;
    }
}