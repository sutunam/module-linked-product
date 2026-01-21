<?php
/**
 * @author    Sutunam
 * @copyright Copyright (c) 2024 Sutunam (http://www.sutunam.com/)
 */

declare(strict_types=1);

namespace Sutunam\LinkedProduct\Plugin\Magento\Catalog\Related;

use Magento\Catalog\Block\Product\ProductList\Related;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Sutunam\LinkedProduct\Model\Config;

class ProductListItem
{
    /**
     * After get items
     *
     * @param Related $subject
     * @param Collection $result
     * @return Collection
     */
    public function afterGetItems(
        Related $subject,
        $result
    ) {
        $result->setFlag(Config::FLAG_IS_LOADED_LINKED_PRODUCTS, false);
        return $result;
    }
}
