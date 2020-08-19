<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Block;

use Katapult\Payment\Gateway\Config\Config;
use Katapult\Payment\Helper\Data as KatapultData;
use Katapult\Payment\Model\Helper\Payment as KatapultModel;
use Magento\Framework\View\Element\Template;

/**
 * Class for Jsplugin
 * Package Katapult\Payment\Block
 */
class Jsplugin extends Template
{
    /**
     * @var KatapultData
     */
    public $config;

    /**
     * @var KatapultModel
     */
    public $model;

    /**
     * Jsplugin constructor.
     *
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }
}
