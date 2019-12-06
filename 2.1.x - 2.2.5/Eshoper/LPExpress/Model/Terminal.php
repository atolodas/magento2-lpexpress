<?php
/**
 * Terminal model
 *
 * @package    Eshoper/LPExpress/Model
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model;

class Terminal extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Connect resourceModel to the model
     */
    protected function _construct()
    {
        $this->_init ( \Eshoper\LPExpress\Model\ResrouceModel\Terminal::class );
    }
}
