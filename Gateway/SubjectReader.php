<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Gateway;

use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper;

/**
 * Class for SubjectReader
 */
class SubjectReader
{
    /**
     * Reads response object from subject
     *
     * @param array $subject
     *
     * @return array
     */
    public function readResponseObject(array $subject)
    {
        return Helper\SubjectReader::readResponse($subject);
    }

    /**
     * Reads payment from subject
     *
     * @param array $subject
     *
     * @return PaymentDataObjectInterface
     */
    public function readPayment(array $subject)
    {
        return Helper\SubjectReader::readPayment($subject);
    }

    /**
     * Reads amount from subject
     *
     * @param array $subject
     *
     * @return mixed
     */
    public function readAmount(array $subject)
    {
        return Helper\SubjectReader::readAmount($subject);
    }

    /**
     * Reads customer id from subject
     *
     * @param array $subject
     *
     * @return int
     */
    public function readCustomerId(array $subject)
    {
        if (!isset($subject['customer_id'])) {
            throw new \InvalidArgumentException('The "customerId" field does not exists');
        }

        return (int) $subject['customer_id'];
    }

    /**
     * Reads public hash from subject
     *
     * @param array $subject
     *
     * @return string
     */
    public function readPublicHash(array $subject)
    {
        if (empty($subject[PaymentTokenInterface::PUBLIC_HASH])) {
            throw new \InvalidArgumentException('The "public_hash" field does not exists');
        }

        return $subject[PaymentTokenInterface::PUBLIC_HASH];
    }

    /**
     * Reads store's ID, otherwise returns null.
     *
     * @param array $subject
     *
     * @return int|null
     */
    public function readStoreId(array $subject)
    {
        return $subject['store_id'] ?? null;
    }
}
