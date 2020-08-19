<?php
/**
 * @category  Katapult
 * @package   Katapult\Payment
 */

namespace Katapult\Payment\Test\Controller;

use Katapult\Payment\Controller\Katapult\CheckoutInfoJson;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class for CheckoutInfoJsonTest
 * Package Katapult\Payment\Test\Controller
 */
class CheckoutInfoJsonTest extends TestCase
{
    /**
     * @var CheckoutInfoJson
     */
    private $checkoutInfoJson;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $jsonResult = $objectManager->getObject(Json::class);

        $resultJsonFactory = $this->createMock(JsonFactory::class);
        $resultJsonFactory->method('create')->willReturn($jsonResult);

        $this->checkoutInfoJson = $objectManager->getObject(
            CheckoutInfoJson::class,
            ['resultJsonFactory' => $resultJsonFactory]
        );
    }

    /**
     * Assert that response will be an instance of Json::class
     */
    public function testExecute()
    {
        $result = $this->checkoutInfoJson->execute();

        $this->assertInstanceOf(Json::class, $result);
    }
}
