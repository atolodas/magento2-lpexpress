<?php
/**
 * Export CSV button for lpexpress shipping rates
 *
 * @package    Eshoper/LPExpress/Block/Adminhtml/Form/Field
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Block\Adminhtml\Form\Field;

class Export extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * Backend url /admin
     *
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        array $data = []
    ) {
        $this->_backendUrl = $backendUrl;

        parent::__construct ( $factoryElement, $factoryCollection, $escaper, $data );
    }

    /**
     * Return html for the export button
     *
     * @return string
     */
    public function getElementHtml()
    {
        /** @var \Magento\Backend\Block\Widget\Button $buttonBlock  */
        $buttonBlock = $this->getForm ()->getParent ()->getLayout ()->createBlock (
            \Magento\Backend\Block\Widget\Button::class
        );

        $params = [ 'website' => $buttonBlock->getRequest ()->getParam( 'website' ) ];

        $url = $this->_backendUrl->getUrl ( "*/*/exportLptables", $params );
        $data = [
            'label' => __( 'Export CSV' ),
            'onclick' => "setLocation('" .
                $url .
                "lptables.csv' )",
            'class' => '',
        ];

        return $buttonBlock->setData ( $data )->toHtml ();
    }
}
