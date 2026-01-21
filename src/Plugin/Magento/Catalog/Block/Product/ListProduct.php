<?php
/**
 * @author    Sutunam
 * @copyright Copyright (c) 2024 Sutunam (http://www.sutunam.com/)
 */

declare(strict_types=1);

namespace Sutunam\LinkedProduct\Plugin\Magento\Catalog\Block\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Sutunam\LinkedProduct\Model\Config;

class ListProduct
{
    /**
     * After get loaded product collection
     *
     * @param \Magento\Catalog\Block\Product\ListProduct $subject
     * @param Collection $result
     * @return Collection
     */
    public function afterGetLoadedProductCollection(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        Collection $result
    ) {
        $result->setFlag(Config::FLAG_IS_LOADED_LINKED_PRODUCTS, false);
        return $result;
    }
}
