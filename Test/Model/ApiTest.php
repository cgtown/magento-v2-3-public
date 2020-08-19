<?php
/**
 * @category  Katapult
 * @package   Katapult\Payment
 */

namespace Katapult\Payment\Test\Model;

use Katapult\Payment\Model\Api;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zend_Http_Client;
use Zend_Http_Exception;
use Zend_Http_Response;

/**
 * Class for ApiTest
 * Package Katapult\Payment\Test\Model
 */
class ApiTest extends TestCase
{
    /**
     * @var Api
     */
    private $api;

    /**
     * @var Creditmemo|MockObject
     */
    private $creditMemoMock;

    /**
     * @var OrderFactory |MockObject
     */
    protected $orderFactory;

    /**
     * @var Creditmemo
     */
    protected $creditmemo;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $cmItemCollectionFactoryMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Zend_Http_Client|MockObject
     */
    protected $client;

    /**
     * @var MockObject|Order
     */
    protected $orderMock;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        /** @var MockObject|Order $orderMock */
        $this->orderMock = $this->createPartialMock(Order::class, ['getIncrementId']);
        $creditMemoItemsMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        /** @var MockObject|Creditmemo $creditMemoMock */
        $this->creditMemoMock = $this->createMock(Creditmemo::class);
        $this->creditMemoMock->method('getOrder')->willReturn($this->orderMock);
        $this->creditMemoMock->method('getAllItems')->willReturn($creditMemoItemsMock);

        $this->client = $this->createMock(Zend_Http_Client::class);
    }

    /**
     * @throws Zend_Http_Exception
     * @throws Exception
     */
    public function testCancelItemsFail()
    {
        $this->expectException(LocalizedException::class);

        $this->client->method('request')->willReturn(
            new Zend_Http_Response(
                Api::HTTP_STATUS_BAD_REQUEST,
                [
                    'Authorization' => 'test_private_token',
                    'Content-Type' => 'application/json'
                ]
            )
        );

        $this->api = $this->objectManager->getObject(
            Api::class,
            [
                'client' => $this->client
            ]
        );

        $return = $this->api->cancelItems($this->creditMemoMock);

        $this->assertInternalType('boolean', $return);
    }

    /**
     * @throws Zend_Http_Exception
     * @throws Exception
     */
    public function testCancelItemsSuccess()
    {
        $this->client->method('request')->willReturn(
            new Zend_Http_Response(
                Api::HTTP_STATUS_OK,
                [
                    'Authorization' => 'test_private_token',
                    'Content-Type' => 'application/json'
                ]
            )
        );

        $this->api = $this->objectManager->getObject(
            Api::class,
            [
                'client' => $this->client
            ]
        );

        $return = $this->api->cancelItems($this->creditMemoMock);

        $this->assertInternalType('boolean', $return);
    }

    /**
     * @throws Zend_Http_Exception
     * @throws Exception
     */
    public function testCancelOrderSuccess()
    {
        $this->client->method('request')->willReturn(
            new Zend_Http_Response(
                Api::HTTP_STATUS_OK,
                [
                    'Authorization' => 'test_private_token',
                    'Content-Type' => 'application/json'
                ]
            )
        );

        $this->api = $this->objectManager->getObject(
            Api::class,
            [
                'client' => $this->client
            ]
        );

        $return = $this->api->cancelOrder($this->orderMock);

        $this->assertInternalType('boolean', $return);
    }

    /**
     * @throws Zend_Http_Exception
     * @throws Exception
     */
    public function testCancelOrderFail()
    {
        $this->expectException(LocalizedException::class);

        $this->client->method('request')->willReturn(
            new Zend_Http_Response(
                Api::HTTP_STATUS_BAD_REQUEST,
                [
                    'Authorization' => 'test_private_token',
                    'Content-Type' => 'application/json'
                ]
            )
        );

        $this->api = $this->objectManager->getObject(
            Api::class,
            [
                'client' => $this->client
            ]
        );

        $return = $this->api->cancelOrder($this->orderMock);

        $this->assertInternalType('boolean', $return);
    }

    /**
     * @throws Zend_Http_Exception
     * @throws Exception
     */
    public function testConfrimOrderFail()
    {
        $this->expectException(LocalizedException::class);

        $this->client->method('request')->willReturn(
            new Zend_Http_Response(
                Api::HTTP_STATUS_BAD_REQUEST,
                [
                    'Authorization' => 'test_private_token',
                    'Content-Type' => 'application/json'
                ]
            )
        );

        $this->api = $this->objectManager->getObject(
            Api::class,
            [
                'client' => $this->client
            ]
        );

        $return = $this->api->confirmOrder($this->orderMock);

        $this->assertInternalType('boolean', $return);
    }

    /**
     * @throws Zend_Http_Exception
     * @throws Exception
     */
    public function testConfrimOrderSuccess()
    {
        $this->client->method('request')->willReturn(
            new Zend_Http_Response(
                Api::HTTP_STATUS_OK,
                [
                    'Authorization' => 'test_private_token',
                    'Content-Type' => 'application/json'
                ]
            )
        );

        $this->api = $this->objectManager->getObject(
            Api::class,
            [
                'client' => $this->client
            ]
        );

        $return = $this->api->confirmOrder($this->orderMock);

        $this->assertInternalType('boolean', $return);
    }
}
