<?php
/**
 * Main module setup
 *
 * Install required tables and columns
 *
 * @package    Eshoper/LPExpress/Setup
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup ();

        $setup->getConnection ()->addColumn (
            $setup->getTable ( 'quote' ),
            'lpexpress_terminal',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Selected LPExpress terminal'
            ]
        );

        $setup->getConnection ()->addColumn (
            $setup->getTable ( 'quote' ),
            'lpexpress_post_office',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Selected LPExpress post office'
            ]
        );

        $setup->getConnection ()->addColumn (
            $setup->getTable ( 'sales_order' ),
            'lpexpress_terminal',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Selected LPExpress terminal'
            ]
        );

        $setup->getConnection ()->addColumn (
            $setup->getTable ( 'sales_order' ),
            'lpexpress_post_office',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Selected LPExpress post office'
            ]
        );

        $terminal_list_table = $setup->getConnection ()->newTable (
            $setup->getTable ( 'lpexpress_terminal_list' )
        )->addColumn (
            'id',
            Table::TYPE_INTEGER,
            null,
            [ 'identity' => true, 'nullable' => false, 'primary' => true ],
            'ID'
        )->addColumn (
            'terminal_id',
            Table::TYPE_TEXT,
            255,
            [ 'nullable' => false ],
            'LP Express terminal ID'
        )->addColumn (
            'terminal',
            Table::TYPE_TEXT,
            255,
            [ 'nullable' => false ]
        )->addIndex (
            $setup->getIdxName ( 'lpexpress_terminal_item', ['terminal_id'] ),
            [ 'terminal_id' ]
        )->setComment ( 'LP Express terminal list' );

        $setup->getConnection ()->createTable ( $terminal_list_table );

        $lpexpress_tracking_events = $setup->getConnection ()->newTable (
            $setup->getTable ('lpexpress_tracking_events' )
        )->addColumn (
            'id',
            Table::TYPE_INTEGER,
            null,
            [ 'identity' => true, 'nullable' => false, 'primary' => true ], 'ID'
        )->addColumn (
            'identcode',
            Table::TYPE_TEXT,
            255,
            [ 'nullable' => false ]
        )->addColumn(
            'event',
            Table::TYPE_TEXT,
            255,
            [ 'nullable' => false ]
        )->addColumn (
            'time',
            Table::TYPE_DATETIME,
            null,
            [ 'nullable' => false ]
        )->addIndex(
            $setup->getIdxName ( 'lpexpress_tracking_events', ['identcode'] ),
            [ 'identcode' ]
        )->setComment ( 'Tracking event list for LP Express' );

        $setup->getConnection ()->createTable ( $lpexpress_tracking_events );

        $lpexpress_table_rates = $setup->getConnection ()->newTable (
            $setup->getTable ('lpexpress_table_rates' )
        )->addColumn (
            'id',
            Table::TYPE_INTEGER,
            null,
            [ 'identity' => true, 'nullable' => false, 'primary' => true ], 'ID'
        )->addColumn (
            'weight_to',
            Table::TYPE_TEXT,
            255,
            [ 'nullable' => false ]
        )->addColumn(
            'courier_price',
            Table::TYPE_TEXT,
            255,
            [ 'nullable' => false ]
        )->addColumn (
            'terminal_price',
            Table::TYPE_TEXT,
            255,
            [ 'nullable' => false ]
        )->addColumn (
            'post_office_price',
            Table::TYPE_TEXT,
            255,
            [ 'nullable' => false ]
        )->addIndex(
            $setup->getIdxName ( 'lpexpress_table_rates', [ 'weight_to' ] ),
            [ 'weight_to' ]
        )->setComment ( 'Table rates for LP Express' );

        $setup->getConnection ()->createTable ( $lpexpress_table_rates );

        $lpexpress_overseas_destinations = $setup->getConnection ()->newTable (
            $setup->getTable ('lpexpress_overseas_destinations' )
        )->addColumn (
            'id',
            Table::TYPE_INTEGER,
            null,
            [ 'identity' => true, 'nullable' => false, 'primary' => true ], 'ID'
        )->addColumn (
            'country_id',
            Table::TYPE_TEXT,
            255,
            [ 'nullable' => false ]
        )->addColumn (
            'country_label',
            Table::TYPE_TEXT,
            255,
            [ 'nullable' => false ]
        )->addColumn (
            'terminal',
            Table::TYPE_BOOLEAN,
            null,
            [ 'nullable' => false ]
        )->addIndex (
            $setup->getIdxName ( 'lpexpress_overseas_destinations', [ 'country_id' ] ),
            [ 'country_id' ]
        )->setComment ( 'Overseas destinations for LP Express' );

        $setup->getConnection ()->createTable ( $lpexpress_overseas_destinations );

        $lpexpress_overseas_rates = $setup->getConnection ()->newTable (
            $setup->getTable ('lpexpress_overseas_rates' )
        )->addColumn (
            'id',
            Table::TYPE_INTEGER,
            null,
            [ 'identity' => true, 'nullable' => false, 'primary' => true ], 'ID'
        )->addColumn (
            'country_id',
            Table::TYPE_TEXT,
            255,
            [ 'nullable' => false ]
        )->addColumn (
            'price',
            Table::TYPE_TEXT,
            255,
            [ 'nullable' => true ]
        )->addIndex(
            $setup->getIdxName ( 'lpexpress_overseas_rates', [ 'country_id' ] ),
            [ 'country_id' ]
        )->setComment ( 'Overseas rates for LP Express' );

        $setup->getConnection ()->createTable ( $lpexpress_overseas_rates );

        $setup->endSetup ();
    }
}
