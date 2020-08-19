<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Gateway\Response;

use Katapult\Payment\Gateway\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Payment Details Handler
 */
class ConfirmHandler implements HandlerInterface
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
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        $payment = $paymentDO->getPayment();
        $payment->setTransactionId($payment->getAdditionalInformation()['katapult_payment_uid']);
        $payment->setParentTransactionId($payment->getTransactionId());
        $payment->setIsTransactionClosed(false);
        $payment->setShouldCloseParentTransaction(true);
    }
}
