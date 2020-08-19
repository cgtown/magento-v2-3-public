<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class for UpgradeSchema
 * Package Katapult\Payment\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $entityAttributesCodes = [
                'cognical_zibby_id' => Table::TYPE_TEXT
            ];

            if (!$setup->getConnection()->tableColumnExists($setup->getTable('sales_order'), 'cognical_zibby_id')) {
                foreach ($entityAttributesCodes as $code => $type) {
                    $setup->getConnection()
                        ->addColumn(
                            $setup->getTable('sales_order'),
                            $code,
                            [
                                'type' => $type,
                                'default' => null,
                                'nullable' => true,
                                'length'    => 255,
                                'comment' => 'Zibby ID',
                            ]
                        );
                }
            }
        }

        if (version_compare($context->getVersion(), '1.0.8') < 0) {
            $entityAttributesCodes = [
                'cognical_zibby_uid' => Table::TYPE_TEXT
            ];

            if (!$setup->getConnection()->tableColumnExists($setup->getTable('sales_order'), 'cognical_zibby_uid')) {
                foreach ($entityAttributesCodes as $code => $type) {
                    $setup->getConnection()
                        ->addColumn(
                            $setup->getTable('sales_order'),
                            $code,
                            [
                                'type' => $type,
                                'default' => null,
                                'nullable' => true,
                                'length'    => 255,
                                'comment' => 'Zibby ID',
                            ]
                        );
                }
            }
        }

        if (version_compare($context->getVersion(), '1.1.1') < 0) {
            $installer
                ->getConnection()
                ->changeColumn('sales_order', 'cognical_zibby_uid', 'katapult_payment_uid', [
                    'type' => Table::TYPE_TEXT,
                    'comment' => 'Katapult UID'
                ]);
            $installer
                ->getConnection()
                ->changeColumn('sales_order', 'cognical_zibby_id', 'katapult_payment_id', [
                    'type' => Table::TYPE_TEXT,
                    'comment' => 'Katapult ID'
                ]);
        }

        if (version_compare($context->getVersion(), '1.1.2') < 0) {
            $creditMemoTable = $setup->getTable('sales_creditmemo');
            $code = 'katapult_processed';

            if (!$setup->getConnection()->tableColumnExists($creditMemoTable, $code)) {
                $setup->getConnection()
                    ->addColumn(
                        $creditMemoTable,
                        $code,
                        [
                            'type' => Table::TYPE_BOOLEAN,
                            'default' => null,
                            'nullable' => true,
                            'length'    => 255,
                            'comment' => 'Processed by Katapult',
                        ]
                    );
            }
        }

        $installer->endSetup();
    }
}
