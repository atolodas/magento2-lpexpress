<?php
/**
 * Table rates model
 * Used for Weight vs Price CSV file
 *
 * @package    Eshoper/LPExpress/Block/Adminhtml/Carrier/Tablerate
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Block\Adminhtml\Carrier\Tablerate;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Website filter
     *
     * @var int
     */
    protected $_websiteId;

    /**
     * Condition filter
     *
     * @var string
     */
    protected $_conditionName;

    /**
     * @var \Eshoper\LPExpress\Model\Rates
     */
    protected $_tablerate;

    /**
     * @var \Eshoper\LPExpress\Model\ResrouceModel\Rates\Collection
     */
    protected $_collectionFactory;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Eshoper\LPExpress\Model\ResrouceModel\Rates\CollectionFactory $collectionFactory
     * @param \Eshoper\LPExpress\Model\Rates $tablerate
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Eshoper\LPExpress\Model\ResrouceModel\Rates\CollectionFactory $collectionFactory,
        \Eshoper\LPExpress\Model\Rates $tablerate,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_tablerate = $tablerate;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Define grid properties
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct ();
        $this->setId ( 'shippingTablerateGrid' );
        $this->_exportPageSize = 10000;
    }

    /**
     * Set current website
     *
     * @param $websiteId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setWebsiteId($websiteId)
    {
        $this->_websiteId = $this->_storeManager->getWebsite ( $websiteId )->getId ();
        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWebsiteId()
    {
        if ( $this->_websiteId === null ) {
            $this->_websiteId = $this->_storeManager->getWebsite ()->getId ();
        }
        return $this->_websiteId;
    }

    /**
     * Set current website
     *
     * @param string $name
     * @return $this
     */
    public function setConditionName($name)
    {
        $this->_conditionName = $name;
        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     */
    public function getConditionName()
    {
        return $this->_conditionName;
    }

    /**
     * Prepare shipping table rate collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        /** @var $collection \Eshoper\LPExpress\Model\ResrouceModel\Rates\Collection */
        $collection = $this->_collectionFactory->create ();
        $this->setCollection ( $collection );

        return parent::_prepareCollection ();
    }

    /**
     * Prepare table columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn (
            'weight_to',
            [ 'header' => __( 'Weight To' ), 'index' => 'weight_to' ]
        );

        $this->addColumn (
            'courier_price',
            [ 'header' => __( 'Courier Price' ), 'index' => 'courier_price' ]
        );

        $this->addColumn(
            'terminal_price',
            [ 'header' => __( 'Terminal Price' ), 'index' => 'terminal_price' ]
        );

        $this->addColumn (
            'post_office_price',
            [ 'header' => __('Post Office Price'), 'index' => 'post_office_price' ]
        );

        return parent::_prepareColumns ();
    }
}
