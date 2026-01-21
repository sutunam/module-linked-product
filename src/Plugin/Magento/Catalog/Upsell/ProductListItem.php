<?php
/**
 * @author    Sutunam
 * @copyright Copyright (c) 2024 Sutunam (http://www.sutunam.com/)
 */

declare(strict_types=1);

namespace Sutunam\LinkedProduct\Plugin\Magento\Catalog\Upsell;

use Magento\Catalog\Block\Product\ProductList\Upsell;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Sutunam\LinkedProduct\Model\Config;

class ProductListItem
{
    /**
     * After get item collection
     *
     * @param Upsell $subject
     * @param Collection $result
     * @return Collection
     */
    public function afterGetItemCollection(
        Upsell $subject,
        $result
    ) {
        $result->setFlag(Config::FLAG_IS_LOADED_LINKED_PRODUCTS, false);
        return $result;
    }
}
