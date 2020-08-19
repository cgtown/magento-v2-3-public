<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Controller\Katapult;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class for Cancel
 * Package Katapult\Payment\Controller\Katapult
 */
class Cancel extends Action
{
    /**
     * @var PageFactory $resultPageFactory
     */
    protected $resultPageFactory;

    /**
     * Cancel constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Katapult Cancel action
     *
     * @return void
     */
    public function execute()
    {
        $this->_redirect('checkout/cart');
    }
}
