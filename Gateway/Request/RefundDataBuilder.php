<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Gateway\Request;

use Katapult\Payment\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class for RefundDataBuilder
 * Package Katapult\Payment\Gateway\Request
 */
class RefundDataBuilder implements BuilderInterface
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
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        return [
            'order_id' => $order->getId()
        ];
    }
}
