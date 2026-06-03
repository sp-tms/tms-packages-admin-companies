<?php

namespace Apps\Tms\Packages\Companies;

use Apps\Tms\Packages\Companies\Model\AppsTmsCompanies;
use System\Base\BasePackage;

class Companies extends BasePackage
{
    protected $modelToUse = AppsTmsCompanies::class;

    protected $packageName = 'companies';

    public $companies;

    public function init()
    {
        parent::init();

        return $this;
    }

    public function getCompany($companyId)
    {
        if ($this->config->databasetype === 'db') {
            $companiesObj = $this->getFirst('id', $companyId);

            if ($companiesObj) {
                $company = $companiesObj->toArray();

                $addressObj = $companiesObj->getAddresses();

                $company['address'] = [];

                if ($addressObj) {
                    $company['address'] = $addressObj->toArray();
                }
            }
        } else {
            $this->setFFRelations(true);
            $this->setFFRelationsConditions(['addresses' => ['package_name', '=', 'Companies'], 'contacts' => ['package_name', '=', 'Companies']]);

            $company = $this->getFirst('id', $companyId, false, true, null, [], true);

        }

        if (isset($company)) {
            $this->addResponse('Company', 0, ['company' => $company]);

            return $company;
        }

        $this->addResponse('No company found with the ID provided', 1, []);

        return false;
    }

    public function addCompany($data)
    {
        if ($this->add($data)) {
            $company = $this->packagesData->last;

            $this->addAddresses($data, $company);

            $this->addResponse('Company added');

            return true;
        }

        $this->addResponse('Error Adding Company', 1);
    }

    public function updateCompany($data)
    {
        $company = $this->getCompany((int) $data['id']);

        if (!$this->removeAddresses($data, $company)) {
            $this->addResponse('Cannot remove address as it is being used!', 1);

            return false;
        }

        if ($this->update($data)) {
            $company = $this->packagesData->last;

            $this->addAddresses($data, $company);

            $this->addResponse('Company updated');

            return true;
        }

        $this->addResponse('Error Updating Company', 1);
    }

    public function removeCompany($data)
    {
        $company = $this->getCompany((int) $data['id']);

        //Archive Company and do not delete it!
        $company['archived'] = true;

        if ($this->updateCompany($company)) {
            $this->addResponse('Company archived');

            return true;
        }

        $this->addResponse('Error removing company', 1);

        return false;
    }

    protected function addAddresses($data, $company)
    {
        if (isset($data['address_ids'])) {
            if (is_string($data['address_ids'])) {
                $data['address_ids'] = $this->helper->decode($data['address_ids'], true);
            }

            if (count($data['address_ids']) > 0) {
                foreach ($data['address_ids'] as $addressId => $address) {
                    if (isset($address['new']) && $address['new'] == 1) {
                        $address['package_name'] = 'Companies';
                        $address['package_row_id'] = $company['id'];

                        $this->basepackages->addressbook->addAddress($address);
                    } else {
                        $dbAddress = $this->basepackages->addressbook->getById($addressId);

                        if ($dbAddress) {
                            $dbAddress = array_merge($dbAddress, $data['address_ids'][$addressId]);
                        }

                        $this->basepackages->addressbook->updateAddress($dbAddress);
                    }
                }
            }
        }
    }

    protected function removeAddresses($data, $company)
    {
        if (isset($data['delete_address_ids'])) {
            if (is_string($data['delete_address_ids'])) {
                $data['delete_address_ids'] = $this->helper->decode($data['delete_address_ids'], true);
            }

            if (count($data['delete_address_ids']) > 0) {
                foreach ($data['delete_address_ids'] as $addressId) {
                    $dbAddress = $this->basepackages->addressbook->getById($addressId);

                    //Check if address is being used by invoice and other locations!!!!
                    //
                    if ($dbAddress) {
                        $this->basepackages->addressbook->removeAddress($dbAddress);
                    }
                }
            }
        }

        return true;
    }

    public function getCompanyByReference($reference, $businessType = 'customers')
    {
        if ($this->config->databasetype === 'db') {
            $params =
                [
                    'conditions'    => 'reference = :reference: AND business_type = :businessType:',
                    'bind'          =>
                        [
                            'reference'         => $reference,
                            'businessType'      => $businessType,
                        ]
                ];
        } else {
            $params = ['conditions' => [['reference', '=', $reference], ['business_type', '=', $businessType]]];
        }

        $company = $this->getByParams($params);

        if ($company && count($company) > 0) {
            $company = $this->getCompany($company[0]['id']);

            return $company;
        }

        return false;
    }

    public function getCompaniesByBusinessType($businessType = 'organisations')
    {
        if ($this->config->databasetype === 'db') {
            $params =
                [
                    'conditions'    => 'business_type = :businessType:',
                    'bind'          =>
                        [
                            'businessType'      => $businessType,
                        ]
                ];
        } else {
            $params = ['conditions' => ['business_type', '=', $businessType]];
        }

        $companies = $this->getByParams($params);

        if ($companies && count($companies) > 0) {
            return $companies;
        }

        return false;
    }
}