<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Logger;

use Magento\Framework\Logger\Handler\Base;

/**
 * Class for Handler
 * Package Katapult\Payment\Logger
 */
class Handler extends Base
{
    const FILENAME = '/var/log/katapult.log';

    /**
     * @var string
     */
    protected $fileName = self::FILENAME;

    /**
     * @var int
     */
    protected $loggerType = \Monolog\Logger::DEBUG;
}
