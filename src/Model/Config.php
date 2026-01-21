<?php
/**
 * @author    Sutunam
 * @copyright Copyright (c) 2024 Sutunam (http://www.sutunam.com/)
 */

declare(strict_types=1);

namespace Sutunam\LinkedProduct\Model;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    // Flag to identify if linked products are loaded
    public const FLAG_IS_LOADED_LINKED_PRODUCTS = 'is_loaded_linked_products';

    // phpcs:disable
    private const GENERAL_ENABLE = 'linked_product/general/enable';
    private const GENERAL_SHOW_ATTRIBUTE_TEXT = 'linked_product/general/show_attribute_text';
    private const GENERAL_SHOW_AVAILABLE_PRODUCTS_COUNT = 'linked_product/general/show_available_products_count';
    private const GENERAL_SHOW_STOCK_STATUS = 'linked_product/general/show_stock_status';
    private const GENERAL_SHOW_ON_PLP = 'linked_product/general/show_on_plp';
    private const GENERAL_SHOW_ON_PDP = 'linked_product/general/show_on_pdp';
    private const GENERAL_SHOW_STOCK_STATUS_TEXT_ON_PLP = 'linked_product/general/show_stock_status_text_on_plp';
    private const GENERAL_SHOW_STOCK_STATUS_TEXT_ON_PDP = 'linked_product/general/show_stock_status_text_on_pdp';
    private const MAPPING_AVAILABLE_PRODUCTS_COUNT_TEXT = 'linked_product/mapping/products_count_text';
    // phpcs:enable

    /**
     * Is linked product enabled
     *
     * @return bool
     */
    public function isEnable(): bool
    {
        return  $this->scopeConfig->isSetFlag(
            self::GENERAL_ENABLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is show attribute text
     *
     * @return bool
     */
    public function isShowAttributeText(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::GENERAL_SHOW_ATTRIBUTE_TEXT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is show available linked products count on product listing instead of the actual products
     *
     * @return bool
     */
    public function isShowAvailableProductsCount(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::GENERAL_SHOW_AVAILABLE_PRODUCTS_COUNT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is show stock status
     *
     * @return bool
     */
    public function isShowStockStatus(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::GENERAL_SHOW_STOCK_STATUS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is linked products shown on product listing
     *
     * @return bool
     */
    public function isShowOnProductListing(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::GENERAL_SHOW_ON_PLP,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is linked products shown on product view
     *
     * @return bool
     */
    public function isShowOnProductView(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::GENERAL_SHOW_ON_PDP,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is stock status text on product listing
     *
     * @return bool
     */
    public function isStockStatusTextProductListing(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::GENERAL_SHOW_STOCK_STATUS_TEXT_ON_PLP,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is stock status text shown on product view
     *
     * @return bool
     */
    public function isStockStatusTextProductView(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::GENERAL_SHOW_STOCK_STATUS_TEXT_ON_PDP,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get available products count text
     *
     * @return array
     */
    public function getAvailableProductsCountText(): array
    {
        $value =  $this->scopeConfig->getValue(
            self::MAPPING_AVAILABLE_PRODUCTS_COUNT_TEXT,
            ScopeInterface::SCOPE_STORE
        );

        if (empty($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        $data = [];
        foreach ($decoded as $row) {
            $data[$row['attribute_code']] = [
                'singular' => $row['singular'],
                'plural' => $row['plural']
            ];
        }

        return $data;
    }
}
