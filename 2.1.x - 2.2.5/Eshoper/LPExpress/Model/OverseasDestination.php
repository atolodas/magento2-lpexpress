<?php
/**
 * Overseas model
 *
 * @package    Eshoper/LPExpress/Model/ResourceModel
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model;

class OverseasDestination extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init ( \Eshoper\LPExpress\Model\ResrouceModel\OverseasDestination::class );
    }
}
