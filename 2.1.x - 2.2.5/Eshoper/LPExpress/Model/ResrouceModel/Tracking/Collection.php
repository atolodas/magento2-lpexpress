<?php
/**
 * Tracking collection
 *
 * Used to get tracking events collection
 *
 * @package    Eshoper/LPExpress/Model/ResourceModel/Tracking
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\ResrouceModel\Tracking;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Identification field
     *
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Connect model with resource model
     */
    protected function _construct()
    {
        $this->_init (
            \Eshoper\LPExpress\Model\Tracking::class,
            \Eshoper\LPExpress\Model\ResrouceModel\Tracking::class
        );
    }
}
