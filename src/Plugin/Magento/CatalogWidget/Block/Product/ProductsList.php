<?php
/**
 * @author    Sutunam
 * @copyright Copyright (c) 2024 Sutunam (http://www.sutunam.com/)
 */

declare(strict_types=1);

namespace Sutunam\LinkedProduct\Plugin\Magento\CatalogWidget\Block\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Sutunam\LinkedProduct\Model\Config;

class ProductsList
{
    /**
     * After create collection
     *
     * @param \Magento\CatalogWidget\Block\Product\ProductsList $subject
     * @param Collection $result
     * @return Collection
     */
    public function afterCreateCollection(
        \Magento\CatalogWidget\Block\Product\ProductsList $subject,
        Collection $result
    ) {
        $result->setFlag(Config::FLAG_IS_LOADED_LINKED_PRODUCTS, false);
        return $result;
    }
}
