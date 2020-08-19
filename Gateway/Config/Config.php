<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Gateway\Config;

use Katapult\Payment\Helper\Data as KatapultHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Payment\Gateway\Config\Config as GatewayConfig;
use Magento\Store\Model\ScopeInterface;

/**
 * Class for Config
 * Package Katapult\Payment\Gateway\Config
 */
class Config extends GatewayConfig
{
    const CODE = 'katapult';
    const CONFIG_KEY_ACTIVE = 'active';
    const CONFIG_KEY_TITLE = 'title';
    const CONFIG_KEY_TEST_MODE = 'test_mode';
    const CONFIG_KEY_LIVE_PRIVATE_TOKEN = 'live_private_token';
    const CONFIG_KEY_LIVE_PUBLIC_TOKEN = 'live_public_token';
    const CONFIG_KEY_TEST_PRIVATE_TOKEN = 'test_private_token';
    const CONFIG_KEY_TEST_PUBLIC_TOKEN = 'test_public_token';
    const CONFIG_KEY_LOG_ENABLED = 'log_enabled';
    const CONFIG_KEY_ALLOWSPECIFIC = 'allowspecific';
    const CONFIG_KEY_SPECIFCCOUNTRY = 'specificcountry';
    const CONFIG_KEY_INSTRUCTIONS = 'instructions';
    const CONFIG_KEY_MIN_AMOUNT = 'minimum_amount';
    const CONFIG_KEY_SORT_ORDER = 'sort_order';
    const CONFIG_PREFIX = 'payment/katapult';
    const XML_PATH_JAVASCRIPT_DOMAIN_LIVE = 'payment/katapult/js_domain_live';
    const XML_PATH_JAVASCRIPT_DOMAIN_TEST = 'payment/katapult/js_domain_test';
    const API_PATH = '/api/v3/application/';
    const URL_SSL = true;

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var ModuleListInterface
     */
    public $enabled;

    /**
     * @var KatapultHelper
     */
    protected $helper;

    /**
     * Katapult config constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param KatapultHelper $helper
     * @param null|string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        KatapultHelper $helper,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param null $storeId
     *
     * @return ModuleListInterface|mixed
     */
    public function isEnable($storeId = null)
    {
        $this->enabled = $this->getValue(self::CONFIG_KEY_ACTIVE, $storeId);

        return $this->enabled;
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isTestMode($storeId = null)
    {
        return $this->getValue(self::CONFIG_KEY_TEST_MODE, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return float
     */
    public function getMinimumOrderAmount($storeId = null)
    {
        return $this->getValue(self::CONFIG_KEY_MIN_AMOUNT, $storeId);
    }

    /**
     * @return string
     */
    public function isSsl()
    {
        return self::URL_SSL ? "https://" : "http://";
    }

    /**
     * Retrieves Log Enabled flag from store configuration
     *
     * @param null|int $storeId
     *
     * @return bool
     */
    public function getLogEnabled($storeId = null)
    {
        return (bool)$this->getValue(self::CONFIG_KEY_LOG_ENABLED, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getPublicToken($storeId = null)
    {
        if ($this->isTestMode()) {
            return $this->getValue(self::CONFIG_KEY_TEST_PUBLIC_TOKEN, $storeId);
        }
        return $this->getValue(self::CONFIG_KEY_LIVE_PUBLIC_TOKEN, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getPrivateToken($storeId = null)
    {
        if ($this->isTestMode()) {
            return $this->getValue(self::CONFIG_KEY_TEST_PRIVATE_TOKEN, $storeId);
        }
        return $this->getValue(self::CONFIG_KEY_LIVE_PRIVATE_TOKEN, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getEnvironment($storeId = null)
    {
        if ($this->isTestMode()) {
            return
                $this->isSsl() .
                $this->scopeConfig->getValue(
                    self::XML_PATH_JAVASCRIPT_DOMAIN_TEST,
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                );
        }

        return
            $this->isSsl() .
            $this->scopeConfig->getValue(
                self::XML_PATH_JAVASCRIPT_DOMAIN_LIVE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    /**
     * Returns cancel URL, to be used by the JS plugin
     *
     * @return string
     */
    public function getJavascriptCancelUrl()
    {
        return $this->helper->getUrl('katapult/katapult/cancel');
    }

    /**
     * Returns checkout info URL, to be used by the JS plugin
     *
     * @return string
     */
    public function getJavascriptCheckoutInfoUrl()
    {
        return $this->helper->getUrl('katapult/katapult/checkoutinfojson');
    }

    /**
     * Returns API end point
     *
     * @return string
     */
    public function getApiEndpoint()
    {
        return $this->getEnvironment() . self::API_PATH;
    }
}
