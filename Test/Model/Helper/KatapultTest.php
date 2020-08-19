<?php
/**
 * @category  Katapult
 * @package   Katapult\Payment
 */

namespace Katapult\Payment\Test\Model\Helper;

use Katapult\Payment\Model\Helper\Katapult;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Directory\Model\Country;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Class for KatapultTest
 * Package Katapult\Payment\Test\Model
 */
class KatapultTest extends TestCase
{
    /**
     * @var Katapult
     */
    protected $katapult;

    /**
     * @var MockObject|Katapult
     */
    protected $katapultMock;

    /**
     * @var MockObject|CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var MockObject|QuoteModel
     */
    protected $quoteModel;

    /**
     * @var MockObject|QuoteAddress
     */
    protected $quoteAddressMock;

    /**
     * @var MockObject|Country
     */
    protected $countryModelMock;

    /**
     * @var MockObject|QuoteItem
     */
    protected $quoteItemMock;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->checkoutSession = $this->createMock(CheckoutSession::class);
        $this->quoteModel = $this->createMock(QuoteModel::class);
        $this->quoteAddressMock = $this->createMock(QuoteAddress::class);
        $this->countryModelMock = $this->createMock(Country::class);

        $this->quoteAddressMock->method('getCountryModel')->willReturn($this->countryModelMock);
        $this->quoteModel->method('getBillingAddress')->willReturn($this->quoteAddressMock);
        $this->quoteModel->method('getShippingAddress')->willReturn($this->quoteAddressMock);

        $this->quoteItemMock = $this->createMock(QuoteItem::class);

        $this->quoteModel->method('getAllItems')->willReturn([$this->quoteItemMock, $this->quoteItemMock]);

        $this->checkoutSession
            ->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quoteModel);

        $this->katapultMock = $this->getMockBuilder(Katapult::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCheckoutSession'])
            ->getMock();

        $this->katapultMock->method('getCheckoutSession')->willReturn($this->checkoutSession);

        $json = $this->createTestProxy(Json::class);

        $this->katapult = $objectManager->getObject(
            Katapult::class,
            [
                'checkoutSession' => $this->checkoutSession,
                'jsonEncoder' => $json
            ]
        );
    }

    /**
     * Assert that return will be a valid JSON string
     */
    public function testGetCheckoutInfoJson()
    {
        $return = $this->katapult->getCheckoutInfoJson();

        $this->assertJson($return);
    }

    /**
     * Assert array matches the format needed by Katapult Modal
     */
    public function testExtractCustomerInfo()
    {
        $testMethod = new ReflectionMethod(
            Katapult::class,
            'extractCustomerInfo'
        );

        $testMethod->setAccessible(true);

        $neededKeys = [
            'billing' => [
                'first_name',
                'middle_name',
                'last_name',
                'address',
                'address2',
                'city',
                'state',
                'country',
                'zip',
                'phone',
                'email'
            ],
            'shipping' => [
                'first_name',
                'middle_name',
                'last_name',
                'address',
                'address2',
                'city',
                'state',
                'country',
                'zip',
                'phone',
                'email'
            ]
        ];

        foreach ($neededKeys as $main => $sub) {
            $this->assertArrayHasKey(
                $main,
                $testMethod->invoke(
                    $this->katapultMock
                )
            );

            foreach ($sub as $key) {
                $this->assertArraySubset(
                    [$main => [$key => '']],
                    $testMethod->invoke(
                        $this->katapultMock
                    )
                );
            }
        }
    }

    /**
     * Assert array matches the format needed by Katapult Modal
     */
    public function testExtractCheckoutInfo()
    {
        $testMethod = new ReflectionMethod(
            Katapult::class,
            'extractCheckoutInfo'
        );

        $testMethod->setAccessible(true);

        $neededKeys = [
            'customer_id',
            'shipping_amount',
            'discounts'
        ];

        foreach ($neededKeys as $key) {
            $this->assertArrayHasKey(
                $key,
                $testMethod->invoke(
                    $this->katapultMock
                )
            );
        }
    }

    /**
     * Assert array matches the format needed by Katapult Modal
     */
    public function testConvertQuoteItemToKatapultItem()
    {
        $testMethod = new ReflectionMethod(
            Katapult::class,
            'convertQuoteItemToKatapultItem'
        );

        $testMethod->setAccessible(true);

        $quoteItemMock = $this->getMockBuilder(QuoteItem::class)
            ->setMethods(
                [
                    'getBaseRowTotal',
                    'getQty',
                    '_getData'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $productMock = $this->createMock(Product::class);

        $quoteItemMock->method('getBaseRowTotal')->willReturn(150);
        $quoteItemMock->method('_getData')->willReturn($productMock);
        $quoteItemMock->method('getQty')->willReturn(3);

        $neededKeys = [
            'display_name',
            'sku',
            'unit_price',
            'quantity',
            'leasable'
        ];

        foreach ($neededKeys as $key) {
            $this->assertArrayHasKey(
                $key,
                $testMethod->invokeArgs(
                    $this->katapultMock,
                    [$quoteItemMock]
                )
            );
        }
    }
}
