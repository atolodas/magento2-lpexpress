<?php
/**
 * Import CSV file field
 *
 * @package    Eshoper/LPExpress/Block/Adminhtml/Form/Field
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Block\Adminhtml\Form\Field;


class Import extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * @return void
     */
    protected function _construct ()
    {
        parent::_construct ();
        $this->setType ('file');
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getElementHtml ()
    {
        return parent::getElementHtml ();
    }
}
