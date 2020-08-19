<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Gateway\Http\Client;

use Psr\Log\LoggerInterface;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Api\OrderRepositoryInterface;
use Katapult\Payment\Model\Api as KatapultApi;

/**
 * Class for AbstractClient
 * Package Katapult\Payment\Gateway\Http\Client
 */
abstract class AbstractClient implements ClientInterface
{
    /**
     * @var KatapultApi
     */
    protected $katapultApi;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Logger
     */
    protected $customLogger;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param Logger $customLogger
     */
    public function __construct(
        LoggerInterface $logger,
        Logger $customLogger,
        OrderRepositoryInterface $orderRepository,
        KatapultApi $katapultApi
    ) {
        $this->logger = $logger;
        $this->customLogger = $customLogger;
        $this->orderRepository = $orderRepository;
        $this->katapultApi = $katapultApi;
    }

    /**
     * @inheritdoc
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $data = $transferObject->getBody();
        $log = [
            'request' => $data,
            'client' => static::class
        ];
        $response['object'] = [];

        try {
            $response['object'] = $this->process($data);
        } catch (\Exception $e) {
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong');
            throw new ClientException($message);
        } finally {
            $log['response'] = (array) $response['object'];
        }

        return $response;
    }

    /**
     * Process http request
     * @param array $data
     * @return mixed
     */
    abstract protected function process(array $data);
}
