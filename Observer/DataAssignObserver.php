<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Class for DataAssignObserver
 * Package Katapult\Payment\Observer
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * Additional data received from modal
     */
    private const KATAPULT_PAYMENT_CUSTOMER_ID = 'katapult_payment_customer_id';
    private const KATAPULT_PAYMENT_UID = 'katapult_payment_uid';
    private const KATAPULT_PAYMENT_ID = 'katapult_payment_id';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::KATAPULT_PAYMENT_CUSTOMER_ID,
        self::KATAPULT_PAYMENT_UID,
        self::KATAPULT_PAYMENT_ID,
    ];

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (isset($additionalData[$additionalInformationKey])) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            }
        }
    }
}
