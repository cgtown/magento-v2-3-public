<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Model\Helper;

use Katapult\Payment\Helper\Data;
use Katapult\Payment\Logger\Logger as KatapultLogger;
use Katapult\Payment\Model\Api as KatapultApi;
use Katapult\Payment\Gateway\Config\Config;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Helper\Data as PaymentData;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Model\Quote;

/**
 * Class for Katapult
 * Package Katapult\Payment\Model\Helper
 */
class Katapult extends AbstractMethod
{
    public const KATAPULT_PAYMENT = 'katapult';

    /**
     * Capture enabled
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'katapult';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * @var KatapultApi
     */
    protected $api;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CustomerSession\Proxy
     */
    protected $customerSession;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var ProductFactory
     */
    protected $productloader;

    /**
     * @var Data
     */
    public $helper;

    /**
     * @var SerializerInterface
     */
    protected $jsonEncoder;

    /**
     * @var KatapultLogger
     */
    protected $katapultLogger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Katapult constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param PaymentData $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param ProductFactory $productloader
     * @param SerializerInterface $jsonEncoder
     * @param KatapultApi $api
     * @param KatapultLogger $katapultLogger
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param Config $config
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        PaymentData $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        ProductFactory $productloader,
        SerializerInterface $jsonEncoder,
        KatapultApi $api,
        KatapultLogger $katapultLogger,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        Config $config
    ) {
        $this->productloader = $productloader;
        $this->jsonEncoder = $jsonEncoder;
        $this->api = $api;
        $this->katapultLogger = $katapultLogger;
        $this->config = $config;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger
        );
    }

    /**
     * Returns a json using the checkout quote, used by Katapult's JS Api
     *
     * @return string
     */
    public function getCheckoutInfoJson()
    {
        $checkoutInfo = [];

        $checkoutInfo['customer'] = $this->extractCustomerInfo();
        $checkoutInfo['items'] = $this->extractItemsInfo();
        $checkoutInfo['checkout'] = $this->extractCheckoutInfo();
        $checkoutInfo['urls'] = $this->_getReturnUrls();
        $return = $this->jsonEncode($checkoutInfo);
        $this->katapultLogger->debug('JS Api Checkout Info:', [$return]);

        return $return;
    }

    /**
     * Quote Getter
     *
     * @return Quote
     */
    protected function getQuote()
    {
        if ($this->quote === null) {
            $this->quote = $this->getCheckoutSession()->getQuote();
        }

        return $this->quote;
    }

    /**
     * Extracts customer info from quote
     *
     * @return array
     */
    protected function extractCustomerInfo()
    {
        $return = [];
        $quote = $this->getQuote();
        $billingAddress = $quote->getBillingAddress();
        $shippingAddress = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();

        $return['billing'] = [];
        $return['billing']['first_name'] = $billingAddress->getFirstname();
        $return['billing']['middle_name'] = $billingAddress->getMiddlename();
        $return['billing']['last_name'] = $billingAddress->getLastname();
        $return['billing']['address'] = $billingAddress->getStreetLine(1);
        $return['billing']['address2'] = $billingAddress->getStreetLine(2);
        $return['billing']['city'] = $billingAddress->getCity();
        $return['billing']['state'] = $billingAddress->getRegionCode();
        $return['billing']['country'] = $billingAddress->getCountryModel()->getName();
        $return['billing']['zip'] = $billingAddress->getPostcode();
        $return['billing']['phone'] = $billingAddress->getTelephone();
        $return['billing']['email'] = $billingAddress->getEmail();

        $return['shipping'] = [];
        $return['shipping']['first_name'] = $shippingAddress->getFirstname();
        $return['shipping']['middle_name'] = $shippingAddress->getMiddlename();
        $return['shipping']['last_name'] = $shippingAddress->getLastname();
        $return['shipping']['address'] = $shippingAddress->getStreetLine(1);
        $return['shipping']['address2'] = $shippingAddress->getStreetLine(2);
        $return['shipping']['city'] = $shippingAddress->getCity();
        $return['shipping']['state'] = $shippingAddress->getRegionCode();
        $return['shipping']['country'] = $shippingAddress->getCountryModel()->getName();
        $return['shipping']['zip'] = $shippingAddress->getPostcode();
        $return['shipping']['phone'] = $shippingAddress->getTelephone();
        $return['shipping']['email'] = $shippingAddress->getEmail() ?? $billingAddress->getEmail();

        return $return;
    }

    /**
     * Extracts items info from quote
     *
     * @return array
     */
    protected function extractItemsInfo()
    {
        $return = [];
        $quote = $this->getQuote();

        foreach ($quote->getAllItems() as $quoteItem) {
            if ($this->shouldSendQuoteItemToKatapult($quoteItem)) {
                $item = $this->convertQuoteItemToKatapultItem($quoteItem);
                array_push($return, $item);
            }
        }

        return $return;
    }

    /**
     * Converts a quote item to a Katapult JS API Item
     *
     * @param Quote\Item $quoteItem
     *
     * @return array
     */
    protected function convertQuoteItemToKatapultItem($quoteItem)
    {
        $return = [];
        $product = $quoteItem->getProduct();

        $return['display_name'] = $quoteItem->getName();
        $return['sku'] = $quoteItem->getSku();
        $return['unit_price'] = ((float)$quoteItem->getBaseRowTotal() / (float)$quoteItem->getQty());
        $return['quantity'] = (float)$quoteItem->getQty();
        $return['leasable'] = $this->getIsLeasable($product);

        return $return;
    }

    /**
     * @param Product $product
     * @return mixed
     */
    protected function getIsLeasable($product)
    {
        $lesableValue = $product->getData('katapult_payment_leasable');
        if ($lesableValue === null) {
            return true;
        }

        return (bool)$lesableValue;
    }

    /**
     * Extracts checkout info from quote
     *
     * @return array
     */
    protected function extractCheckoutInfo()
    {
        $return = [];
        $quote = $this->getQuote();
        $return['customer_id'] = $quote->getId();

        if ($quote->isVirtual()) {
            $return['shipping_amount'] = 0;
        } else {
            $return['shipping_amount'] = (float)$quote->getShippingAddress()->getShippingAmount();
        }

        $discountTotal = 0;
        /** @var Quote\Item $item */
        foreach ($quote->getAllItems() as $item) {
            $discountTotal += $item->getDiscountAmount();
        }

        $baseSubTotal = $quote->getBaseSubtotal();
        $baseSubTotalWithDiscount = $quote->getBaseSubtotalWithDiscount();
        $discountChecking = $baseSubTotal - $baseSubTotalWithDiscount;
        $discountTotal = $discountChecking > $discountTotal ? $discountChecking : $discountTotal;
        $return['discounts'] = [];

        if ($discountTotal > 0) {
            $return['discounts'][] = [
                'discount_name' => 'Discount',
                'discount_amount' => abs($discountTotal)
            ];
        }

        return $return;
    }

    /**
     * @return CheckoutSession
     */
    protected function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * @return CustomerSession\Proxy
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * @return array
     */
    protected function _getReturnUrls()
    {
        $return = [];

        // Return URL is false to show the modal that no success return is needed from it
        $return['return'] = false;
        $return['cancel'] = $this->config->getJavascriptCancelUrl();

        return $return;
    }

    /**
     * Encodes data in Json format
     *
     * @param array $data
     *
     * @return string
     */
    public function jsonEncode($data)
    {
        return $this->jsonEncoder->serialize($data);
    }

    /**
     * @return string
     */
    public function getPaymentMethodCode()
    {
        return $this->_code;
    }

    /**
     * Decides if product should be sent to Katapult
     *
     * @param $quoteItem
     *
     * @return bool
     */
    protected function shouldSendQuoteItemToKatapult($quoteItem)
    {
        $isTotalLargerThanZero = ($quoteItem->getRowTotal() > 0);
        $isBundle = ($quoteItem->getProductType() == Type::TYPE_BUNDLE);
        $hasParent = (bool)($quoteItem->getParentItemId());
        $isParentBundle = $hasParent &&
            $quoteItem->getParentItem()->getProductType() == Type::TYPE_BUNDLE;

        return $isTotalLargerThanZero && ((!$hasParent && !$isBundle) || ($hasParent && $isParentBundle));
    }

    /**
     * @inheritdoc
     */
    public function canRefundPartialPerInvoice()
    {
        return true;
    }
}
