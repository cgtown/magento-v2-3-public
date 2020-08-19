<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Observer;

use Katapult\Payment\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class for Savequoteid
 *
 * Package Katapult\Payment\Observer
 */
class Savequoteid implements ObserverInterface
{
    /**
     * @var Session\Proxy
     */
    protected $katapultSession;

    /**
     * Savequoteid constructor.
     *
     * @param Session\Proxy $katapultSession
     */
    public function __construct(
        Session $katapultSession
    ) {
        $this->katapultSession = $katapultSession;
    }

    /**
     * Stores quote id in katapult's session to be used in success page
     * Needed, considering that katapult's post usually happens before the redirect
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getData('quote');

        if ($quote->getId() && $quote->getIsActive()) {
            $this->katapultSession->setQuoteId($quote->getId());
        }
    }
}
