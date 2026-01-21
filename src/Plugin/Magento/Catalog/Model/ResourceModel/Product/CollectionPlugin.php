<?php
/**
 * @author    Sutunam
 * @copyright Copyright (c) 2025 Sutunam (http://www.sutunam.com/)
 */

declare(strict_types=1);

namespace Sutunam\LinkedProduct\Plugin\Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use Sutunam\LinkedProduct\Model\Config;
use Sutunam\LinkedProduct\ViewModel\ProductList;

class CollectionPlugin
{
    /**
     * @var ProductList
     */
    protected $productList;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param ProductList $productList
     * @param StoreManager $storeManager
     * @param Config $config
     */
    public function __construct(
        ProductList $productList,
        StoreManager $storeManager,
        Config $config
    ) {
        $this->productList = $productList;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * After get items
     *
     * @param Collection $subject
     * @param Collection $result
     * @return Collection
     */
    public function afterLoad(
        Collection $subject,
        $result
    ) {
        // Check if the flag is already set
        if (!$subject->hasFlag(Config::FLAG_IS_LOADED_LINKED_PRODUCTS)) {
            return $result;
        }

        // If the flag is set to true, skip the execution
        if ($subject->getFlag(Config::FLAG_IS_LOADED_LINKED_PRODUCTS)) {
            return $result;
        }

        // Set the flag to true to prevent re-execution
        $subject->setFlag(Config::FLAG_IS_LOADED_LINKED_PRODUCTS, true);

        if (!$this->config->isEnable() || !$this->config->isShowOnProductListing()) {
            return $result;
        }

        $productItems = $result->getItems();

        if (empty($productItems) || !array_values($productItems)[0] instanceof Product) {
            return $result;
        }

        if ($this->config->isShowAvailableProductsCount()) {
            $linkedItemsSize = $this->productList->getLinkedItemsSize($productItems);

            if (count($linkedItemsSize) === 0) {
                return $result;
            }

            foreach ($productItems as $product) {
                if (array_key_exists($product->getId(), $linkedItemsSize)) {
                    // +1 for parent product
                    $product->setData('available_products_count', $linkedItemsSize[$product->getId()] + 1);
                }
            }

            return $result;
        }

        $this->productList->addFilter('website_id', $this->storeManager->getStore()->getWebsiteId());
        $this->productList->addFilter('visibility', [
            Visibility::VISIBILITY_IN_CATALOG,
            Visibility::VISIBILITY_BOTH,
        ], 'in');

        $linkedCollection = $this->productList->getLinkedCollection($productItems);
        $linkedIds = $this->productList->getLinkedIds($productItems);

        $linkedProducts = [];

        foreach ($linkedIds as $item) {
            /** @var Product|null $linkedProduct */
            $linkedProduct = $linkedCollection->getItemById($item['linked_product_id']);
            if ($linkedProduct) {
                $linkedProducts[$item['product_id']][] = $linkedProduct;
            }
        }

        foreach ($productItems as $product) {
            if (array_key_exists($product->getId(), $linkedProducts)) {
                $product->setData('linked_products', $linkedProducts[$product->getId()]);
            }
        }

        return $result;
    }
}
