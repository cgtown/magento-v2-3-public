<?php
/**
 * @category    Katapult
 * @package     Katapult_Payment
 */

namespace Katapult\Payment\Helper;

use Magento\Eav\Model\AttributeManagement;
use Magento\Eav\Model\Entity\Attribute\GroupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Attribute\Group;

/**
 * Class for Data
 *
 * Package Katapult\Payment\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var GroupFactory
     */
    protected $attributeGroupFactory;

    /**
     * @var AttributeManagement
     */
    protected $attributeManagement;

    /**
     * @var SetFactory
     */
    protected $attributeSetFactory;

    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var TypeFactory
     */
    protected $eavTypeFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param AttributeFactory $attributeFactory
     * @param SetFactory $attributeSetFactory
     * @param TypeFactory $typeFactory
     * @param GroupFactory $attributeGroupFactory
     * @param AttributeManagement $attributeManagement
     */
    public function __construct(
        Context $context,
        AttributeFactory $attributeFactory,
        SetFactory $attributeSetFactory,
        TypeFactory $typeFactory,
        GroupFactory $attributeGroupFactory,
        AttributeManagement $attributeManagement
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->eavTypeFactory = $typeFactory;
        $this->attributeGroupFactory = $attributeGroupFactory;
        $this->attributeManagement = $attributeManagement;
        parent::__construct($context);
    }

    /**
     * @param $path
     *
     * @return string
     */
    public function getUrl($path, $param = [])
    {
        return $this->_getUrl($path, $param);
    }

    /**
     * @param $attributeCode
     * @param $attributeGroupCode
     *
     * @return bool
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function addAttributeToAllAttributeSets($attributeCode, $attributeGroupCode)
    {
        $entityType = $this->eavTypeFactory->create()->loadByCode('catalog_product');
        $attribute = $this->attributeFactory->create()->loadByCode($entityType->getId(), $attributeCode);
        if (!$attribute->getId()) {
            return false;
        }
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $setCollection */
        $setCollection = $this->attributeSetFactory->create()->getCollection();
        $setCollection->addFieldToFilter('entity_type_id', $entityType->getId());
        /** @var Set $attributeSet */
        foreach ($setCollection as $attributeSet) {
            /** @var Group $group */
            $group = $this->attributeGroupFactory->create()->getCollection()
                ->addFieldToFilter('attribute_group_code', ['eq' => $attributeGroupCode])
                ->addFieldToFilter('attribute_set_id', ['eq' => $attributeSet->getId()])
                ->getFirstItem();
            $groupId = $group->getId() ?: $attributeSet->getDefaultGroupId();
            // Assign:
            $this->attributeManagement->assign(
                'catalog_product',
                $attributeSet->getId(),
                $groupId,
                $attributeCode,
                $attributeSet->getCollection()->count() * 10
            );
        }
        return true;
    }
}
