<?php
/**
 * Rates resource model
 *
 * Used to save Weight VS Price information from csv file
 * Also gather rate information based on presented weight
 *
 * @package    Eshoper/LPExpress/Model/ResourceModel
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\ResrouceModel;

use Magento\Framework\Filesystem\DirectoryList;

class Rates extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_fileSystem;

    /**
     * @var \Eshoper\LPExpress\Model\RatesFactory
     */
    protected $_ratesFactory;

    /**
     * Rates constructor.
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Eshoper\LPExpress\Model\RatesFactory $ratesFactory
     * @param null $connectionName
     */
    public function __construct (
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Eshoper\LPExpress\Model\RatesFactory $ratesFactory,
        $connectionName = null
    ) {
        $this->_fileSystem = $filesystem;
        $this->_ratesFactory = $ratesFactory;

        parent::__construct($context, $connectionName);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init ( 'lpexpress_table_rates', 'id' );
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
        if ( empty ( $_FILES [ 'groups' ][ 'tmp_name' ][ 'lpexpress' ][ 'fields' ][ 'import' ][ 'value' ] ) ) {
            return $this;
        }

        $filePath = $_FILES [ 'groups' ][ 'tmp_name' ][ 'lpexpress' ][ 'fields' ][ 'import' ][ 'value' ];
        $file = $this->getCsvFile ( $filePath );

        $data = $this->getData ( $file );

        if ( ! empty ( $data ) && $this->validate ( $data ) ) {
            // Truncate data
            $this->deleteByCondition ( [] );

            foreach ( $data as $rate ) {
                /** @var \Eshoper\LPExpress\Model\Rates $rates */
                $this->_ratesFactory->create ()
                        ->setWeightTo ( $rate [ 'weight_to' ] )
                        ->setCourierPrice ( $rate [ 'courier_price' ] )
                        ->setTerminalPrice ( $rate [ 'terminal_price' ] )
                        ->setPostOfficePrice ( $rate [ 'post_office_price' ] )
                        ->save ();
            }
        }
    }

    /**
     * Get shipping rates by presented weight and method
     *
     * @param string $quoteWeight
     * @param string $shippingMethod
     * @return string | bool
     */
    public function getRate ( $quoteWeight, $shippingMethod )
    {
        $weights = $result = [];
        $rates = $this->_ratesFactory->create ()->getCollection ()->getData ();

        for ( $i = count ( $rates ) - 1; $i >= 0; $i-- ) {
            // Search for weight that fits
            if ( $rates [ $i ]['weight_to'] >= $quoteWeight  ) {
                $weights [ $i ] = $rates [ $i ][ 'weight_to' ];
            }
        }

        if ( ! empty ( $weights ) ) {
            // Result is the minimum weight index
            $result = $rates [ array_search ( min ( $weights ), $weights ) ];
            return $result [ $shippingMethod . '_price' ];
        }

        return false;
    }

    /**
     * Validate CSV file format
     *
     * @param $data
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function validate ( $data )
    {
        $values = [];

        foreach ( $data as $rate ) {
            if ( $rate [ 'weight_to' ] === null || $rate [ 'weight_to' ] === '' ) {
                throw new \Magento\Framework\Exception\LocalizedException (
                    __( 'LP Express: Weight cell in the csv file cannot be empty.' )
                );
            }
            array_push ( $values, $rate [ 'weight_to' ] );
        }

        // Check if there is any duplicates
        if ( count ( array_unique ( $values ) ) < count ( $data ) ) {
            throw new \Magento\Framework\Exception\LocalizedException (
                __( 'LP Express: Error. Please check for weight duplicates in the csv file.' )
            );
        }

        return true;
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
        $tmpDirectory = $this->_fileSystem->getDirectoryRead(DirectoryList::SYS_TMP);
        $path = $tmpDirectory->getRelativePath($filePath);
        return $tmpDirectory->openFile($path);
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
             * 4 - overseas_price
             */
            array_push (
                $data,
                [
                    'weight_to' => $csvLine [ 0 ],
                    'courier_price' => $csvLine [ 1 ],
                    'terminal_price' => $csvLine [ 2 ],
                    'post_office_price' => $csvLine [ 3 ]
                ]
            );
        }

        return $data;
    }
}
