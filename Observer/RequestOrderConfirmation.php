<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Observer;

use Katapult\Payment\Gateway\Config\Config as KatapultConfig;
use Magento\Sales\Model\Order\Invoice;
use Psr\Log\LoggerInterface;
use Katapult\Payment\Model\Api as KatapultApi;
use Katapult\Payment\Gateway\SubjectReader;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for RequestOrderConfirmation
 * Package Katapult\Payment\Observer
 */
class RequestOrderConfirmation extends AbstractDataAssignObserver
{
    /**
     * @var KatapultApi
     */
    private $katapultApi;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * RequestOrderConfirmation constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param SubjectReader $subjectReader
     * @param KatapultApi $katapultApi
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        KatapultApi $katapultApi
    ) {
        $this->katapultApi = $katapultApi;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Invoice $invoice */
        $invoice = $observer->getData('invoice');

        try {
            $order = $invoice->getOrder();
            $payment = $order->getPayment();

            if ($payment->getMethod() !== KatapultConfig::CODE) {
                return;
            }

            if ($payment) {
                $additionalInformation = $payment->getAdditionalInformation();

                $order->setKatapultPaymentUid($additionalInformation['katapult_payment_uid']);
                $order->setKatapultPaymentId($additionalInformation['katapult_payment_id']);
            } else {
                throw new LocalizedException(
                    sprintf(
                        'KATAPULT order %s payment is missing additional information',
                        $order->getIncrementId()
                    )
                );
            }

            $this->orderRepository->save($order);
            $this->katapultApi->confirmOrder($order);
        } catch (\Exception $exception) {
            $this->logger->error(
                sprintf(
                    'KATAPULT order %s could not be confirmed %s',
                    $order->getId(),
                    $exception->getMessage()
                )
            );
        }
    }
}
