<?php
/**
 * @author    Sutunam
 * @copyright Copyright (c) 2024 Sutunam (http://www.sutunam.com/)
 */

declare(strict_types=1);

namespace Sutunam\LinkedProduct\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Sutunam\LinkedProduct\Model\Product\Link;

class AddCustomProductLinkType implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
            $this->moduleDataSetup->getTable('catalog_product_link_type'),
            ['link_type_id' => Link::LINK_TYPE_LINKED, 'code' => 'linked'],
            ['code']
        );

        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('catalog_product_link_attribute'),
            [
                'link_type_id = ?' => Link::LINK_TYPE_LINKED,
            ]
        );

        $this->moduleDataSetup->getConnection()->insert(
            $this->moduleDataSetup->getTable('catalog_product_link_attribute'),
            [
                'link_type_id' => Link::LINK_TYPE_LINKED,
                'product_link_attribute_code' => 'position',
                'data_type' => 'int'
            ]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
