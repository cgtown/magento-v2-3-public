<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Gateway\Response;

use Magento\Sales\Model\Order\Payment;

/**
 * Class for RefundHandler
 * Package Katapult\Payment\Gateway\Response
 */
class RefundHandler extends ConfirmHandler
{
    /**
     * Whether parent transaction should be closed
     *
     * @param Payment $orderPayment
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return !(bool)$orderPayment->getCreditmemo()->getInvoice()->canRefund();
    }
}
