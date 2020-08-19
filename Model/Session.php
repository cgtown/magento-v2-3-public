<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Model;

use Katapult\Payment\Logger\Logger;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\App\Response\Http as Response;
use Magento\Framework\App\State;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Session\Generic;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\Session\StorageInterface;
use Magento\Framework\Session\ValidatorInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Class for Session
 *
 * Package Katapult\Payment\Model
 */
class Session extends SessionManager
{
    /**
     * Quote ID Session Key
     */
    const SESSION_KEY_QUOTE_ID = 'quote_id';

    /**
     * Order ID Session Key
     */
    const SESSION_KEY_ORDER_ID = 'order_id';

    /**
     * @var Generic
     */
    protected $session;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var
     */
    protected $sessionManager;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Session constructor.
     *
     * @param Request $request
     * @param SidResolverInterface $sidResolver
     * @param ConfigInterface $sessionConfig
     * @param SaveHandlerInterface $saveHandler
     * @param ValidatorInterface $validator
     * @param StorageInterface $storage
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param State $appState
     * @param Generic $session
     * @param Response $response
     * @param Logger $logger
     * @throws SessionException
     */
    public function __construct(
        Request $request,
        SidResolverInterface $sidResolver,
        ConfigInterface $sessionConfig,
        SaveHandlerInterface $saveHandler,
        ValidatorInterface $validator,
        StorageInterface $storage,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        State $appState,
        Generic $session,
        Response $response,
        Logger $logger
    ) {
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState
        );

        $this->session = $session;
        $this->logger = $logger;
        $this->response = $response;
    }

    /**
     * Retrieves Quote Id from katapult session
     *
     * @return int
     */
    public function getQuoteId()
    {
        $this->logger->debug(
            'Getting Quote Id: ' . $this->getData(self::SESSION_KEY_QUOTE_ID)
        );

        return $this->getData(self::SESSION_KEY_QUOTE_ID);
    }

    /**
     * Saves quote id into katapult session
     *
     * @param mixed $quoteId
     */
    public function setQuoteId($quoteId)
    {
        $quoteId = (int) $quoteId;
        $this->setData(self::SESSION_KEY_QUOTE_ID, $quoteId);

        $this->logger->debug(
            'Saving Quote Id on the current session: ' . $quoteId
        );
    }

    /**
     * Retrieves Order Id from katapult session
     *
     * @return int
     */
    public function getOrderId()
    {
        $this->logger->debug(
            'Getting Order Id: ' . $this->getData(self::SESSION_KEY_ORDER_ID)
        );
        return $this->getData(self::SESSION_KEY_ORDER_ID);
    }

    /**
     * Saves Order id into katapult session
     *
     * @param mixed $orderId
     */
    public function setOrderId($orderId)
    {
        $orderId = (int) $orderId;
        $this->setData(self::SESSION_KEY_ORDER_ID, $orderId);

        $this->logger->debug(
            'Saving Order Id on the current session: ' . $orderId
        );
    }
}
