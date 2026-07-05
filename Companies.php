<?php

namespace Apps\Tms\Packages\Companies;

use Apps\Tms\Packages\Companies\Companies;
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
            $this->setFFRelationsConditions(
                    [
                        'addresses' => ['package_class', '=', str_replace('\\', '_', Companies::class)],
                        'contacts' => ['package_class', '=', str_replace('\\', '_', Companies::class)]
                    ]
            );

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

            $this->updateAddresses($data, $company);
            $this->updateContacts($data, $company);

            $this->addActivityLog($company);

            if ($company['logo'] !== '') {
                $this->basepackages->storages->changeOrphanStatus(newUUID : $company['logo'], status: 0);
                $this->basepackages->storages->updatePackageInfo($company['logo'], $company['id']);
            }

            $this->addResponse('Company added');

            return true;
        }

        $this->addResponse('Error Adding Company', 1);
    }

    public function updateCompany($data)
    {
        $companyArr = $this->getCompany((int) $data['id']);

        if ($this->update($data)) {
            $company = $this->packagesData->last;

            $this->updateAddresses($data, $company);
            $this->updateContacts($data, $company);

            $this->addActivityLog($data, $companyArr);

            if ($company['logo'] !== '') {
                $this->basepackages->storages->changeOrphanStatus(newUUID : $company['logo'], status: 0);
                $this->basepackages->storages->updatePackageInfo($company['logo'], $company['id']);
            }

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

    protected function updateAddresses($data, $company)
    {
        if (isset($data['delete_address_ids'])) {
            if (is_string($data['delete_address_ids'])) {
                $data['delete_address_ids'] = $this->helper->decode($data['delete_address_ids'], true);
            }

            if (count($data['delete_address_ids']) > 0) {
                foreach ($data['delete_address_ids'] as $addressId) {
                    $dbAddress = $this->basepackages->addressbook->getById($addressId);

                    if ($dbAddress) {
                        $this->basepackages->addressbook->removeAddress($dbAddress);
                    }
                }
            }
        }

        if (isset($data['address_ids'])) {
            if (is_string($data['address_ids'])) {
                $data['address_ids'] = $this->helper->decode($data['address_ids'], true);
            }

            if (count($data['address_ids']) > 0) {
                foreach ($data['address_ids'] as $addressId => $address) {
                    if (isset($address['new']) && $address['new'] == 1) {
                        $address['package_class'] = str_replace('\\', '_', Companies::class);
                        $address['package_row_id'] = $company['id'];

                        $this->basepackages->addressbook->addAddress($address);
                    } else {
                        $dbAddress = $this->basepackages->addressbook->getById($addressId);

                        if ($dbAddress) {
                            $dbAddress = array_merge($dbAddress, $data['address_ids'][$addressId]);

                            $dbAddress['package_class'] = str_replace('\\', '_', Companies::class);
                            $dbAddress['package_row_id'] = $company['id'];

                            $this->basepackages->addressbook->updateAddress($dbAddress);
                        }
                    }
                }
            }
        }

        return true;
    }

    protected function updateContacts($data, $company)
    {
        if (isset($data['contact_ids'])) {
            if (is_string($data['contact_ids'])) {
                $data['contact_ids'] = $this->helper->decode($data['contact_ids'], true);
            }

            if (count($data['contact_ids']) > 0) {
                foreach ($data['contact_ids'] as $contactId => $contact) {
                    if (isset($contact['new']) && $contact['new'] == 1) {
                        $contact['package_class'] = str_replace('\\', '_', Companies::class);
                        $contact['package_row_id'] = $company['id'];

                        if (isset($contact['first_name']) && isset($contact['last_name'])) {
                            $contact['full_name'] = $contact['first_name'] . ' ' . $contact['last_name'];
                        } else {
                            $contact['full_name'] = $contact['first_name'];
                        }

                        $this->basepackages->contactbook->addContact($contact);

                        if ($contact['portrait'] !== '') {
                            $this->basepackages->storages->changeOrphanStatus(newUUID : $contact['portrait'], status: 0);
                        }
                    } else {
                        $dbContact = $this->basepackages->contactbook->getById($contactId);

                        if ($dbContact) {
                            $oldPortrait = null;
                            $newPortrait = null;
                            if ($dbContact['portrait'] !== '') {
                                $oldPortrait = $dbContact['portrait'];
                            }
                            if ($data['contact_ids'][$contactId]['portrait'] !== '') {
                                $newPortrait = $data['contact_ids'][$contactId]['portrait'];
                            }

                            $dbContact = array_merge($dbContact, $data['contact_ids'][$contactId]);

                            $dbContact['package_class'] = str_replace('\\', '_', Companies::class);
                            $dbContact['package_row_id'] = $company['id'];

                            if (isset($dbContact['first_name']) && isset($dbContact['last_name'])) {
                                $dbContact['full_name'] = $dbContact['first_name'] . ' ' . $dbContact['last_name'];
                            } else {
                                $dbContact['full_name'] = $dbContact['first_name'];
                            }

                            $this->basepackages->contactbook->updateContact($dbContact);

                            if ($data['contact_ids'][$contactId]['portrait'] !== '') {
                                $this->basepackages->storages->changeOrphanStatus(newUUID : $newPortrait, oldUUID: $oldPortrait, status: 0);
                            }
                        }
                    }
                }
            }
        }

        if (isset($data['delete_contact_ids'])) {
            if (is_string($data['delete_contact_ids'])) {
                $data['delete_contact_ids'] = $this->helper->decode($data['delete_contact_ids'], true);
            }

            if (count($data['delete_contact_ids']) > 0) {
                foreach ($data['delete_contact_ids'] as $contactId) {
                    $dbContact = $this->basepackages->contactbook->getById($contactId);

                    if ($dbContact) {
                        $this->basepackages->contactbook->removeContact($dbContact);

                        if ($dbContact['portrait'] !== '') {
                            $this->basepackages->storages->changeOrphanStatus(oldUUID : $dbContact['portrait'], status: 1);
                        }
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