<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Model;

use Katapult\Payment\Gateway\Config\Config;
use Katapult\Payment\Logger\Logger;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;
use Zend_Http_Client;

/**
 * API model
 */
class Api
{
    /**
     * Request type
     */
    const REQUEST_CONTENT_TYPE = 'application/json';

    /**
     * Request methods
     */
    const REQUEST_METHOD_GET = 'GET';
    const REQUEST_METHOD_POST = 'POST';

    /**
     * Response - Return array key
     */
    const RESPONSE_BODY = 'body';
    const RESPONSE_HEADERS = 'headers';
    const RESPONSE_STATUS = 'status';

    /**
     * Zend_Http_Client Configurations
     */
    const HTTP_CLIENT_MAXREDIRECTS = 0;
    const HTTP_CLIENT_TIMEOUT = 30;

    /**
     * Response statuses
     */
    const HTTP_STATUS_OK = 200;
    const HTTP_STATUS_BAD_REQUEST = 400;

    /**
     * @var SerializerInterface
     */
    protected $jsonEncoder;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Zend_Http_Client
     */
    protected $client;

    /**
     * Api constructor.
     *
     * @param Logger $logger
     * @param SerializerInterface $jsonEncoder
     * @param Config $config
     * @param Zend_Http_Client $client
     */
    public function __construct(
        Logger $logger,
        SerializerInterface $jsonEncoder,
        Config $config,
        Zend_Http_Client $client
    ) {
        $this->logger = $logger;
        $this->jsonEncoder = $jsonEncoder;
        $this->config = $config;
        $this->client = $client;
    }

    /**
     * Attempts to perform a partial order cancellation
     *
     * @param Creditmemo $creditMemo
     *
     * @return bool
     * @throws \Exception
     */
    public function cancelItems(Creditmemo $creditMemo)
    {
        $return = false;

        $this->logger->debug(
            'Starting Cancel Items Request for order #' . $creditMemo->getOrder()->getIncrementId() .
            ' (Katapult UID: ' . $creditMemo->getOrder()->getKatapultPaymentUid() . ')'
        );
        $response = $this->makeRequest(
            $this->_getCancelItemEndpoint($creditMemo->getOrder()),
            self::REQUEST_METHOD_POST,
            $this->_getRequestHeaders(),
            $this->_getCancelItemRequestBody($creditMemo),
            180
        );

        if (array_key_exists(self::RESPONSE_STATUS, $response) &&
            $response[self::RESPONSE_STATUS] === self::HTTP_STATUS_OK) {
            $return = true;
        } else {
            $responseParsed = $this->jsonEncode($response);
            $this->logger->debug('Error during order refund - ' . $responseParsed);

            throw new LocalizedException(
                __('It was not possible to cancel this order\'s items at this time. Please try again later.')
            );
        }

        return $return;
    }

    /**
     * Attempts to perform an order cancellation
     *
     * @param Order $order
     *
     * @return bool
     * @throws \Exception
     */
    public function cancelOrder(Order $order)
    {
        $return = false;

        $this->logger->debug(
            'Starting Cancel Order Request for order #' . $order->getIncrementId() .
            ' (Katapult UID: ' . $order->getKatapultPaymentUid() . ')'
        );
        $response = $this->makeRequest(
            $this->_getCancelOrderEndpoint($order),
            self::REQUEST_METHOD_GET,
            $this->_getRequestHeaders()
        );

        if (array_key_exists(self::RESPONSE_STATUS, $response) &&
            $response[self::RESPONSE_STATUS] === self::HTTP_STATUS_OK) {
            $return = true;
        } else {
            throw new LocalizedException(
                __('It was not possible to cancel this order at this time. Please try again later')
            );
        }

        return $return;
    }

    /**
     * Attempts to perform an order confirmation
     *
     * @param Order $order
     *
     * @return bool
     * @throws \Exception
     */
    public function confirmOrder(Order $order)
    {
        $return = false;

        $this->logger->debug(
            'Starting Confirm Order Request for order #' . $order->getIncrementId() .
            ' (Katapult UID: ' . $order->getKatapultPaymentUid() . ')'
        );
        $response = $this->makeRequest(
            $this->_getConfirmOrderEndpoint($order),
            self::REQUEST_METHOD_POST,
            $this->_getRequestHeaders(),
            $this->_getConfirmOrderRequestBody($order)
        );

        if ($response[self::RESPONSE_STATUS] === self::HTTP_STATUS_OK) {
            $return = true;
        } else {
            throw new LocalizedException(
                __('It was not possible to confirm this order at this time. Please try again later')
            );
        }

        return $return;
    }

    /**
     * Returns Cancel Item API Endpoint
     *
     * @param Creditmemo $creditmemo
     *
     * @return string
     */
    protected function _getCancelItemRequestBody(Creditmemo $creditmemo)
    {
        $return = [];
        $return['items'] = [];

        /** @var CreditmemoItem $item */
        foreach ($creditmemo->getAllItems() as $item) {
            if (($item->getRowTotal() > 0) && !($item->getParentItemId())) {
                $returnItem = [];
                $returnItem['sku'] = $item->getSku();
                $returnItem['display_name'] = $item->getName();
                $returnItem['unit_price'] = $item->getRowTotal() / $item->getQty();
                $returnItem['quantity'] = (int) $item->getQty();

                $return['items'][] = $returnItem;
            }
        }

        return $this->jsonEncode($return);
    }

    /**
     * Returns Cancel Order API Endpoint
     *
     * @param Order $order
     *
     * @return string
     */
    protected function _getCancelOrderEndpoint(Order $order)
    {
        return $this->config->getApiEndpoint() . $order->getKatapultPaymentUid() . '/cancel_order/';
    }

    /**
     * Returns Confirm Order API Endpoint
     *
     * @param Order $order
     *
     * @return string
     */
    protected function _getConfirmOrderEndpoint(Order $order)
    {
        return $this->config->getApiEndpoint() . $order->getKatapultPaymentUid() . '/confirm_order/';
    }

    /**
     * Returns Cancel Item API Endpoint
     *
     * @param Order $order
     *
     * @return string
     */
    protected function _getCancelItemEndpoint(Order $order)
    {
        return $this->config->getApiEndpoint() . $order->getKatapultPaymentUid() . '/cancel_item/';
    }

    /**
     * Returns Confirm Order Request Body
     *
     * @param Order $order
     *
     * @return string
     */
    protected function _getConfirmOrderRequestBody(Order $order)
    {
        $return = [];
        $return['order_id'] = $order->getIncrementId();

        return $this->jsonEncode($return);
    }

    /**
     * Returns request headers for katapult integration calls
     *
     * @return string[]
     */
    protected function _getRequestHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->config->getPrivateToken(),
            'Content-Type' => self::REQUEST_CONTENT_TYPE
        ];
    }

    /**
     * Encodes given $data in Json format
     *
     * @param $data
     *
     * @return string
     */
    protected function jsonEncode($data)
    {
        return $this->jsonEncoder->serialize($data);
    }

    /**
     * Makes a request
     *
     * @param string $url
     * @param string $method
     * @param string[] $headers
     * @param string|null $body
     * @param int $timeout
     *
     * @return array - Contains keys 'status', 'headers' and 'body' with response's status header and body
     * @throws \Zend_Http_Client_Exception
     */
    protected function makeRequest($url, $method, $headers, $body = null, $timeout = self::HTTP_CLIENT_TIMEOUT)
    {
        $return = [];

        $this->logger->debug(' - URL: ' . $url);
        $this->logger->debug(' - Method: ', [$method]);
        $this->logger->debug(' - Body: ', [$body]);
        $client = $this->client;
        $client
            ->setConfig(
                [
                    'maxredirects' => self::HTTP_CLIENT_MAXREDIRECTS,
                    'timeout' => $timeout
                ]
            );

        $client->setUri($url);
        $client->setMethod($method);
        $client->setHeaders($headers);

        if ($body !== null) {
            $client->setRawData($body);
        }

        $response = $client->request();

        if ($response) {
            $return[self::RESPONSE_STATUS] = $response->getStatus();
            $return[self::RESPONSE_HEADERS] = $response->getHeaders();
            $return[self::RESPONSE_BODY] = $response->getRawBody();
        }

        $this->logger->debug('Response: ', $return);

        return $return;
    }
}
