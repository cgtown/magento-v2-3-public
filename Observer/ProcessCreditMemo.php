<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Observer;

use Katapult\Payment\Model\Helper\Katapult;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Katapult\Payment\Model\Api;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Payment;

/**
 * Class for processing created credit memos, if order was created with katapult
 * Package Katapult\Payment\Observer
 */
class ProcessCreditMemo implements ObserverInterface
{
    /**
     * @var Api
     */
    protected $katapult;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * ProcessCreditMemo constructor.
     *
     * @param Api $katapult
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     */
    public function __construct(
        Api $katapult,
        CreditmemoRepositoryInterface $creditmemoRepository
    ) {
        $this->katapult = $katapult;
        $this->creditmemoRepository = $creditmemoRepository;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     *
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var Payment $payment */
        $payment = $observer->getData('payment');

        /** @var Creditmemo $creditMemo */
        $creditMemo = $observer->getData('creditmemo');

        if ($payment->getMethod() === Katapult::KATAPULT_PAYMENT &&
            $creditMemo->getData('katapult_processed') !== 1) {

            try {
                $this->katapult->cancelItems($creditMemo);

                $creditMemo->setData('katapult_processed', 1);
                $this->creditmemoRepository->save($creditMemo);
            } catch (\Exception $e) {
                throw new LocalizedException(
                    __(
                        'Error occurred during Katapult refund - ' .
                        $e->getMessage() .
                        ' Please check Katapult order')
                );
            }
        }

        return $this;
    }
}
