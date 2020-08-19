<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Controller\Katapult;

use Katapult\Payment\Model\Helper\Katapult;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class for CheckoutInfoJson
 *
 * Package Katapult\Payment\Controller\Katapult
 */
class CheckoutInfoJson extends Action
{
    /**
     * @var Katapult
     */
    private $model;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * CheckoutInfoJson constructor.
     *
     * @param Context $context
     * @param Katapult $model
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Katapult $model,
        JsonFactory $resultJsonFactory
    ) {
        $this->model = $model;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Katapult Checkout Info Json
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $data = json_decode($this->model->getCheckoutInfoJson(), true);

        return $result->setData($data);
    }
}
