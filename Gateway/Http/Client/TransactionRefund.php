<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Gateway\Http\Client;

/**
 * Class for TransactionRefund
 * Package Katapult\Payment\Gateway\Http\Client
 */
class TransactionRefund extends AbstractClient
{
    /**
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    protected function process(array $data)
    {
        // placeholder, can implement additional actions that need to occur during refund here
    }
}
