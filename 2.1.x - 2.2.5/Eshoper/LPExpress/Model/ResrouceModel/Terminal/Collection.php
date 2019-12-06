<?php
/**
 * Terminal collection
 *
 * Used to get terminal collection
 *
 * @package    Eshoper/LPExpress/Model/ResourceModel/Terminal
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\ResrouceModel\Terminal;

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
            \Eshoper\LPExpress\Model\Terminal::class,
            \Eshoper\LPExpress\Model\ResrouceModel\Terminal::class
        );
    }
}
