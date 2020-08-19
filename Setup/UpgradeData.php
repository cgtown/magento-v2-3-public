<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Setup;

use Katapult\Payment\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class for UpgradeData
 * Package Katapult\Payment\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * UpgradeData constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param Data $helper
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        Data $helper
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->helper = $helper;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            if (!$eavSetup->getAttribute(Product::ENTITY, 'cognical_zibby_leasable')) {
                $eavSetup->addAttribute(
                    Product::ENTITY,
                    'cognical_zibby_leasable',
                    [
                        'type' => 'int',
                        'backend' => '',
                        'frontend' => '',
                        'label' => 'Leasable with Zibby',
                        'input' => 'select',
                        'class' => '',
                        'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                        'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                        'visible' => true,
                        'required' => false,
                        'user_defined' => true,
                        'default' => '',
                        'searchable' => true,
                        'filterable' => false,
                        'comparable' => true,
                        'visible_on_front' => true,
                        'used_in_product_listing' => false,
                        'unique' => false,
                        'apply_to' => ''
                    ]
                );
            }
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $this->helper->addAttributeToAllAttributeSets('cognical_zibby_leasable', 'general');
        }

        if (version_compare($context->getVersion(), '1.0.9') < 0) {
            // Update custom attribute to not be visible on FE by default
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                Product::ENTITY,
                'cognical_zibby_leasable',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Leasable with Zibby',
                    'input' => 'select',
                    'class' => '',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => true,
                    'filterable' => false,
                    'comparable' => true,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.1.0') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->updateAttribute(
                Product::ENTITY,
                'cognical_zibby_leasable',
                [
                    'attribute_code' => 'katapult_payment_leasable',
                    'label' => 'Leasable with Katapult',
                    'frontend_label' => 'Leasable with Katapult'
                ]
            );
        }

        $setup->endSetup();
    }
}
