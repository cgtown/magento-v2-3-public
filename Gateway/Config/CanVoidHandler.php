<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Gateway\Config;

use Katapult\Payment\Gateway\SubjectReader;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class for CanVoidHandler
 * Package Katapult\Payment\Gateway\Config
 */
class CanVoidHandler implements ValueHandlerInterface
{
    /**
     * Payment code
     *
     * @var string
     */
    protected $code = 'katapult';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * CanVoidHandler constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Retrieve method configured value
     *
     * @param array $subject
     * @param int|null $storeId
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(array $subject, $storeId = null)
    {
        $paymentDO = $this->subjectReader->readPayment($subject);

        $payment = $paymentDO->getPayment();
        return $payment instanceof Payment && !(bool)$payment->getAmountPaid();
    }
}
