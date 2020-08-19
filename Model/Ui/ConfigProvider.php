<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Model\Ui;

use Katapult\Payment\Gateway\Config\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class for ConfigProvider
 * Package Katapult\Payment\Model\Ui
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'katapult';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * Initialize dependencies.
     *
     * @param Config $config
     * @param SessionManagerInterface $session
     */
    public function __construct(
        Config $config,
        SessionManagerInterface $session
    ) {
        $this->config = $config;
        $this->session = $session;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $storeId = $this->session->getStoreId();

        return [
            'payment' => [
                self::CODE => [
                    'isEnable' => $this->config->isEnable($storeId),
                    'isTestMode' => $this->config->isTestMode($storeId),
                    'minOrderAmount' => $this->config->getMinimumOrderAmount($storeId),
                    'pubicKey' => $this->config->getPublicToken($storeId),
                    'environmentUrl' => $this->config->getEnvironment(),
                ]
            ]
        ];
    }
}
