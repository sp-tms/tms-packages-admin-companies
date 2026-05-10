<?php

namespace Apps\Tms\Packages\Companies;

use System\Base\BasePackage;

class Companies extends BasePackage
{
    //protected $modelToUse = ::class;

    protected $packageName = 'companies';

    public $companies;

    public function getCompaniesById($id)
    {
        $companies = $this->getById($id);

        if ($companies) {
            //
            $this->addResponse('Success');

            return;
        }

        $this->addResponse('Error', 1);
    }

    public function addCompanies($data)
    {
        //
    }

    public function updateCompanies($data)
    {
        $companies = $this->getById($id);

        if ($companies) {
            //
            $this->addResponse('Success');

            return;
        }

        $this->addResponse('Error', 1);
    }

    public function removeCompanies($data)
    {
        $companies = $this->getById($id);

        if ($companies) {
            //
            $this->addResponse('Success');

            return;
        }

        $this->addResponse('Error', 1);
    }
}