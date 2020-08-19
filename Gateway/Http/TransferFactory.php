<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Katapult\Payment\Gateway\Config\Config;

/**
 * Class for TransferFactory
 * Package Katapult\Payment\Gateway\Http
 */
class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * TransferFactory constructor.
     * @param TransferBuilder $transferBuilder
     * @param Config $config
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        Config $config
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->config = $config;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        return $this->transferBuilder
            ->setHeaders(['Content-Type' => 'application/json',
                          'Authorization' => 'Bearer ' . $this->config->getPrivateToken(),])
            ->setBody($request)
            ->build();
    }
}
