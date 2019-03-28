<?php

namespace MarketplaceService\Services;

use Plenty\Plugin\Application;
use Plenty\Modules\Account\Models\Account;
use Plenty\Modules\Account\Address\Models\Address;
use Plenty\Modules\Account\Contact\Models\Contact;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Modules\Account\Address\Models\AddressOption;
use Plenty\Modules\Account\Contact\Models\ContactOption;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\System\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactAddressRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactAccountRepositoryContract;

/**
 * Class ContactService
 * @package MarketplaceService\Services
 */
class ContactService {

    const ADDRESS_BILLING_TYPE  = 1;
    const CONTACT_CUSTOMER_TYPE = 1;

    /** @var ContactRepositoryContract $contactRepository */
    public $contactRepository;

    /** @var ContactAddressRepositoryContract $contactAddressRepository */
    public $contactAddressRepository;

    /**
     * ContactService constructor.
     * @param ContactRepositoryContract $contactRepository
     * @param ContactAddressRepositoryContract $contactAddressRepository
     */
    public function __construct(ContactRepositoryContract $contactRepository,
                                ContactAddressRepositoryContract $contactAddressRepository)
    {
        $this->contactRepository        = $contactRepository;
        $this->contactAddressRepository = $contactAddressRepository;
    }

    /**
     * @param null $data
     * @return array|Contact
     */
    public function getOrCreateContact($data = null)
    {
        $contactData = [];
        $addressData = [];

        $contactId = $this->contactRepository->getContactIdByEmail($data['resource']['contact']['email']);

        if( (int)$contactId > 0 ) {
            return ['id' => $contactId];
        }

        if(!is_null($data)) {
            $contactData = [
                "referrerId" => $this->getWebStoreId(),
                "typeId"     => self::CONTACT_CUSTOMER_TYPE,
                "options" => [
                    "typeId" => [
                        "typeId"    => 2,
                        "subTypeId" => 4,
                        "value"     => $data['resource']['contact']['email'],
                        "password"  => 'plenty123',
                        "priority"  => 0
                    ]
                ]
            ];

            $addressData = [
                "address1"                 => $data['resource']['address']['street'],
                "address2"                 => $data['resource']['address']['houseNumber'],
                "address3"                 => $data['resource']['address']['additional'] ?? null,
                "gender"                   => $data['resource']['contact']['gender'] ?? null,
                "name1"                    => $data['resource']['address']['companyName'] ?? '',
                "name2"                    => $data['resource']['contact']['firstName'],
                "name3"                    => $data['resource']['contact']['lastName'],
                "postalCode"               => $data['resource']['address']['postalCode'],
                "town"                     => $data['resource']['address']['town'],
                "telephone"                => $data['resource']['address']['phone'],
                "stateId"                  => null,
                "countryId"                => 1,
                "vatNumber"                => "",
                "useAddressLightValidator" => true,
                'options'                  => $this->getContactOptions(
                                                    $data['resource']['address']['phone'],
                                                    $data['resource']['contact']['email']),
            ];
        }
        return $this->addNewContact($contactData, $addressData);
    }

    /**
     * @param array $contactData
     * @param null $addressData
     * @return mixed|Contact
     */
    public function addNewContact(array $contactData, $addressData = null)
    {
        $newAddress = null;
        $contact    = $this->createContact($contactData);

        if(!is_null($contact) && $contact->id > 0)
        {
            /** @var AuthHelper $authHelper */
            $authHelper = pluginApp(AuthHelper::class);

            if ($addressData !== null) {
                $newAddress = $authHelper->processUnguarded( function () use ($addressData, $contact) {
                    return $this->createAddress($addressData, $contact, self::ADDRESS_BILLING_TYPE);
                });
            }

            $contact = $authHelper->processUnguarded( function () use ($newAddress, $contactData, $contact) {
                if ($newAddress instanceof Address) {
                    $contactData['gender']    = $newAddress->gender;
                    $contactData['firstName'] = $newAddress->firstName;
                    $contactData['lastName']  = $newAddress->lastName;
                    $contactData['options']   = $this->getContactOptionsFromAddress($newAddress->options);

                    return $this->contactRepository->updateContact($contactData, $contact->id);
                }
                return $contact;
            });
        }
        return $contact;
    }

    /**
     * Create a new contact
     * @param array $contactData
     * @return Contact
     */
    public function createContact(array $contactData)
    {
        $contact                              = null;
        $contactData['checkForExistingEmail'] = true;
        $contactData['lang']                  = 'de';

        try {
            $contact = $this->contactRepository->createContact($contactData);
        }
        catch(\Exception $e) {
            $contact = [
                'code'      => 1,
                'message'   => 'email already exists'
            ];
        }
        return $contact;
    }

    /**
     * Create an address with the specified address type
     * @param array $addressData
     * @param Contact $contact
     * @param int $type
     * @return Address
     */
    public function createAddress(array $addressData, Contact $contact, int $type):Address
    {
        /** @var AuthHelper $authHelper */
        $authHelper  = pluginApp(AuthHelper::class);
        $addressRepo = $this->contactAddressRepository;

        $newAddress = $authHelper->processUnguarded( function() use ($addressData, $contact, $addressRepo, $type) {
            return $addressRepo->createAddress($addressData, $contact->id, $type);
        });

        if($type == self::ADDRESS_BILLING_TYPE && isset($addressData['name1']) && strlen($addressData['name1'])) {
            $this->createAccount([
                'companyName' => $addressData['name1'],
            ], $contact->id);
        }
        return $newAddress;
    }

    /**
     * Update a contact
     * @param array $contactData
     * @param int $contactId
     * @return null|Contact
     */
    private function updateContact(array $contactData, int $contactId)
    {
        if($contactId > 0) {
            return $this->contactRepository->updateContact($contactData, $contactId);
        }
        return null;
    }

    /**
     * @param $accountData
     * @param int $contactId
     */
    private function createAccount($accountData, int $contactId)
    {
        /** @var AuthHelper $authHelper */
        $authHelper  = pluginApp(AuthHelper::class);

        /** @var ContactAccountRepositoryContract $accountRepo */
        $accountRepo = pluginApp(ContactAccountRepositoryContract::class);

        $account = $authHelper->processUnguarded( function() use ($accountData, $contactId, $accountRepo) {
            return $accountRepo->createAccount($accountData, (int)$contactId);
        });

        if($account instanceof Account && (int)$account->id > 0) {
            $this->updateContact([
                'classId' => 1
            ], $contactId);
        }
    }

    /**
     * @param string $tel
     * @param string $email
     * @return array
     */
    private function getContactOptions($tel = '', $email = '')
    {
        $options = [];

        if(strlen($email)) {
            $options[] = [
                'typeId' => AddressOption::TYPE_EMAIL,
                'value' => $email
            ];
        }
        if(strlen($tel)) {
            $options[] = [
                'typeId' => AddressOption::TYPE_TELEPHONE,
                'value'  => $tel
            ];
        }
        return $options;
    }

    /**
     * @param $addressOptions
     * @return array
     */
    private function getContactOptionsFromAddress($addressOptions)
    {
        $options = [];
        $addressToContactOptionsMap =
            [
                AddressOption::TYPE_TELEPHONE =>
                    [
                        'typeId'    => ContactOption::TYPE_PHONE,
                        'subTypeId' => ContactOption::SUBTYPE_PRIVATE
                    ],

                AddressOption::TYPE_EMAIL =>
                    [
                        'typeId'    => ContactOption::TYPE_MAIL,
                        'subTypeId' => ContactOption::SUBTYPE_PRIVATE
                    ]
            ];

        foreach ($addressOptions as $key => $addressOption)
        {
            $mapItem = $addressToContactOptionsMap[$addressOption->typeId];

            if (!empty($mapItem))
            {
                $options[] =
                    [
                        'typeId'    => $mapItem['typeId'],
                        'subTypeId' => $mapItem['subTypeId'],
                        'priority'  => 0,
                        'value'     => $addressOption->value
                    ];
            }
        }
        return $options;
    }

    /**
     * @return WebstoreConfiguration
     */
    public function getWebStoreId()
    {
        $app                      = pluginApp(Application::class);
        $webStoreConfigRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);

        /** @var WebstoreConfiguration $webStoreConfig */
        $webStoreConfig           = null;
        $webStoreConfig           = $webStoreConfigRepository->findByPlentyId($app->getPlentyId());

        return $webStoreConfig->webstoreId;
    }
}
