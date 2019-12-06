<?php
/**
 * Import Price vs Country from csv file
 *
 * @package    Eshoper/LPExpress/Model/Config/Backend
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\Config\Backend;

class OverseasRates extends \Magento\Framework\App\Config\Value
{
    /**
     * Resource model for custom rates
     *
     * @var \Eshoper\LPExpress\Model\ResrouceModel\OverseasRatesFactory
     */
    protected $_overseasRatesFactory;

    /**
     * Rates constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Eshoper\LPExpress\Model\ResrouceModel\OverseasRatesFactory $overseasRatesFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct (
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Eshoper\LPExpress\Model\ResrouceModel\OverseasRatesFactory $overseasRatesFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_overseasRatesFactory = $overseasRatesFactory;

        parent::__construct (
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * After config save information call resource model
     * to save price vs country table
     *
     * @return \Magento\Framework\App\Config\Value
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave ()
    {
        /** @var \Eshoper\LPExpress\Model\ResrouceModel\OverseasRates $overseasRates */
        $overseasRates = $this->_overseasRatesFactory->create ();
        $overseasRates->uploadAndImport ( $this );
        return parent::afterSave();
    }
}
