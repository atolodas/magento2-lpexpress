<?php
/**
 * Export CSV file with current table rates
 *
 * Used for Weight VS Price table rates
 *
 * @package    Eshoper/LPExpress/Controller/Adminhtml/System/Config
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Controller\Adminhtml\System\Config;

class ExportLptables extends \Magento\Config\Controller\Adminhtml\System\AbstractConfig
{
    /**
     * File response
     *
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * ExportLptables constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;

        parent::__construct ( $context, $configStructure, $sectionChecker );
    }

    /**
     * Export shipping table rates in csv format
     *
     * @return \Magento\Framework\App\ResponseInterface | \Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $fileName = 'lptables.csv';

        /** @var $gridBlock \Eshoper\LPExpress\Block\Adminhtml\Carrier\Tablerate\Grid */
        $gridBlock = $this->_view->getLayout ()->createBlock (
            \Eshoper\LPExpress\Block\Adminhtml\Carrier\Tablerate\Grid::class
        );

        /**
         * Return CSV file from grid
         */
        return $this->_fileFactory->create ( $fileName, $gridBlock->getCsvFile () ,
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR );
    }
}
