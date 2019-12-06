<?php
/**
 * Overseas resource model
 *
 * @package    Eshoper/LPExpress/Model/ResourceModel
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\ResrouceModel;


class OverseasDestination extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Eshoper\LPExpress\Model\OverseasDestinationFactory
     */
    protected $_overseasDestinationFactory;

    /**
     * @var \Eshoper\LPExpress\Model\Config
     */
    protected $_config;

    /**
     * OverseasDestination constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Eshoper\LPExpress\Model\OverseasDestinationFactory $overseasDestinationFactory
     * @param \Eshoper\LPExpress\Model\Config $config
     * @param null $connectionName
     */
    public function __construct (
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Eshoper\LPExpress\Model\OverseasDestinationFactory $overseasDestinationFactory,
        \Eshoper\LPExpress\Model\Config $config,
        $connectionName = null
    ) {
        $this->_overseasDestinationFactory = $overseasDestinationFactory;
        $this->_config = $config;
        parent::__construct ( $context, $connectionName );
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init ( 'lpexpress_overseas_destinations', 'id' );
    }

    /**
     * Return if country is available
     *
     * @param string $country_id
     * @return bool
     */
    public function isAvailable ( $country_id )
    {
        /** @var \Eshoper\LPExpress\Model\ResrouceModel\OverseasDestination\Collection $countryCollection */
        $countryCollection = $this->_overseasDestinationFactory->create ()->getCollection ();

        if ( $this->_config->getOverseasType() === 'CA' ) {
            $countryCollection->addFieldToFilter('country_id', $country_id)
                ->addFieldToFilter('terminal',  true );
        } else {
            $countryCollection->addFieldToFilter('country_id', $country_id );
        }

        return !empty ( $countryCollection->getData () );
    }
}
