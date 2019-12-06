<?php
/**
 * Rates resource model
 *
 * Used to save Price vs Country information from csv file
 *
 * @package    Eshoper/LPExpress/Model/ResourceModel
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\ResrouceModel;

class OverseasRates extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_fileSystem;

    /**
     * @var \Eshoper\LPExpress\Model\OverseasRatesFactory
     */
    protected $_overseasRatesFactory;

    /**
     * Rates constructor.
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Eshoper\LPExpress\Model\OverseasRatesFactory $overseasRatesFactory
     * @param null $connectionName
     */
    public function __construct (
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Eshoper\LPExpress\Model\OverseasRatesFactory $overseasRatesFactory,
        $connectionName = null
    ) {
        $this->_fileSystem = $filesystem;
        $this->_overseasRatesFactory = $overseasRatesFactory;

        parent::__construct($context, $connectionName);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init ( 'lpexpress_overseas_rates', 'id' );
    }

    /**
     * Delete from main table by condition
     *
     * @param array $condition
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function deleteByCondition ( array $condition )
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        $connection->delete($this->getMainTable(), $condition);
        $connection->commit();
        return $this;
    }

    /**
     * Upload csv file and import to main table
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function uploadAndImport ( \Magento\Framework\DataObject $object )
    {
        /**
         * @var \Magento\Framework\App\Config\Value $object
         */
        if ( empty ( $_FILES [ 'groups' ][ 'tmp_name' ][ 'lpexpress' ][ 'fields' ][ 'overseas_import' ][ 'value' ] ) ) {
            return $this;
        }

        $filePath = $_FILES [ 'groups' ][ 'tmp_name' ][ 'lpexpress' ][ 'fields' ][ 'overseas_import' ][ 'value' ];
        $file = $this->getCsvFile ( $filePath );

        $data = $this->getData ( $file );

        if ( ! empty ( $data ) ) {
            // Truncate data
            $this->deleteByCondition ( [] );

            foreach ( $data as $rate ) {
                /** @var \Eshoper\LPExpress\Model\Rates $rates */
                $this->_overseasRatesFactory->create ()
                    ->setCountryId ( $rate [ 'country_id' ] )
                    ->setPrice ( $rate [ 'price' ] )
                    ->save ();
            }
        }
    }

    /**
     * Get shipping rates by presented weight and method
     *
     * @param string $countryId
     * @return string | bool
     */
    public function getRate ( $countryId )
    {
        $rates     = $this->_overseasRatesFactory->create ()->getCollection ()
                                ->addFieldToFilter ( 'country_id', $countryId );

        if ( ! empty ( $rates->getData () ) ) {
            return $rates->getData ()[ 0 ][ 'price' ];
        }

        return false;
    }

    /**
     * Open for reading csv file
     *
     * @param $filePath
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getCsvFile ( $filePath )
    {
        $pathInfo = pathinfo ( $filePath );
        $dirName = isset ( $pathInfo['dirname'] ) ? $pathInfo [ 'dirname' ] : '';
        $fileName = isset ( $pathInfo['basename'] ) ? $pathInfo [ 'basename' ] : '';

        $directoryRead = $this->_fileSystem->getDirectoryReadByPath ( $dirName );

        return $directoryRead->openFile ( $fileName );
    }

    /**
     * Get data from csv file
     *
     * @param \Magento\Framework\Filesystem\File\ReadInterface $file
     * @return array
     */
    private function getData ( \Magento\Framework\Filesystem\File\ReadInterface $file )
    {
        $data = [];

        while ( false !== ( $csvLine = $file->readCsv() ) ) {
            if ( @$line++ == 0 ) continue; // Skip first line

            /**
             * Columns
             * 0 - weight_to
             * 1 - courier_price
             * 2 - terminal_price
             * 3 - post_office_price
             */
            array_push (
                $data,
                [
                    'country_id' => $csvLine [ 0 ],
                    'price' => $csvLine [ 1 ]
                ]
            );
        }

        return $data;
    }
}
