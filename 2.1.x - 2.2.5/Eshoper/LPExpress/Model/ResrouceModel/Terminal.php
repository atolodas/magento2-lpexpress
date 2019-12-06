<?php
/**
 * Terminal resource model
 *
 * @package    Eshoper/LPExpress/Model/ResourceModel
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\ResrouceModel;

class Terminal extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Connect resource model to lpexpress_terminal_list table
     */
    public function _construct()
    {
        $this->_init ( 'lpexpress_terminal_list', 'id' );
    }
}
