<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Gateway\Http\Client;

/**
 * Class for TransactionConfirm
 * Package Katapult\Payment\Gateway\Http\Client
 */
class TransactionConfirm extends AbstractClient
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        return 'ok';
    }
}
