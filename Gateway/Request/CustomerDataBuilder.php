<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Gateway\Request;

use Katapult\Payment\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class for CustomerDataBuilder
 * Package Katapult\Payment\Gateway\Request
 */
class CustomerDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->isVirtual() ? $order->getBillingAddress() : $order->getShippingAddress();

        if (!$billingAddress || !$shippingAddress) {
            return [];
        }

        return [
            'billing' => [
                'first_name' => $billingAddress->getFirstname(),
                'middle_name' => $billingAddress->getMiddlename(),
                'last_name' => $billingAddress->getLastname(),
                'address' => $billingAddress->getStreetLine(1),
                'address2' => $billingAddress->getStreetLine(2),
                'city' => $billingAddress->getCity(),
                'state' => $billingAddress->getRegionCode(),
                'country' => $billingAddress->getCountryModel()->getName(),
                'zip' => $billingAddress->getPostcode(),
                'phone' => $billingAddress->getTelephone(),
                'email' => $billingAddress->getEmail()
            ],
            'shipping' => [
                'first_name' => $shippingAddress->getFirstname(),
                'middle_name' => $shippingAddress->getMiddlename(),
                'last_name' => $shippingAddress->getLastname(),
                'address' => $shippingAddress->getStreetLine(1),
                'address2' => $shippingAddress->getStreetLine(2),
                'city' => $shippingAddress->getCity(),
                'state' => $shippingAddress->getRegionCode(),
                'country' => $shippingAddress->getCountryModel()->getName(),
                'zip' => $shippingAddress->getPostcode(),
                'phone' => $shippingAddress->getTelephone(),
                'email' => $shippingAddress->getEmail() ?? $billingAddress->getEmail()
            ]
        ];
    }
}
