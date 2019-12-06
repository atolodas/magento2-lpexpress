<?php
/**
 * PostOffice display for adminhtml_order_create
 *
 * @package    Eshoper/LPExpress/Block/Adminhtml/Order/Create
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Block\Adminhtml\Order\Create;

class PostOffice extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * @var \Eshoper\LPExpress\Api\ZipToPost
     */
    protected $_zipToPost;

    /**
     * PostOffice constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Eshoper\LPExpress\Api\ZipToPost $zipToPost
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Eshoper\LPExpress\Api\ZipToPost $zipToPost,
        array $data = []
    ) {
        $this->_zipToPost = $zipToPost;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
    }

    /**
     * Show only when post office method is selected
     *
     * @return bool
     */
    public function showPostOffice ()
    {
        return $this->getCreateOrderModel ()->getShippingAddress ()->getShippingMethod ()
            === 'lpexpress_lpexpress_post_office';
    }

    /**
     * Return zip2post string
     *
     * @return string
     */
    public function getZipToPost ()
    {
        return $this->_zipToPost->getPostOffice (
            $this->getCreateOrderModel ()->getShippingAddress ()->getPostcode ()
        );
    }
}
