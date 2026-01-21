<?php
/**
 * @author    Sutunam
 * @copyright Copyright (c) 2024 Sutunam (http://www.sutunam.com/)
 */

declare(strict_types=1);

namespace Sutunam\LinkedProduct\ViewModel;

use function array_filter as filter;
use function array_map as map;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\LinkFactory as ProductLinkFactory;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection as ProductLinkCollection;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory as ProductLinkCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\EntityManager\MetadataPool as EntityMetadataPool;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Sutunam\LinkedProduct\Model\Product\Link;

class ProductList implements ArgumentInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var ProductLinkFactory
     */
    protected $productLinkFactory;

    /**
     * @var ProductLinkCollectionFactory
     */
    protected $productLinkCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ProductVisibility
     */
    protected $productVisibility;

    /**
     * @var EntityMetadataPool
     */
    protected $entityMetadataPool;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var string|null
     */
    private ?string $memoizedProductLinkField;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductLinkCollectionFactory $productLinkCollectionFactory
     * @param ProductLinkFactory $productLinkFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ProductVisibility $productVisibility
     * @param EntityMetadataPool $entityMetadataPool
     * @param ResourceConnection $resourceConnection
     * @param Registry $registry
     * @param string $memoizedProductLinkField
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductLinkCollectionFactory $productLinkCollectionFactory,
        ProductLinkFactory $productLinkFactory,
        CollectionProcessorInterface $collectionProcessor,
        ProductVisibility $productVisibility,
        EntityMetadataPool $entityMetadataPool,
        ResourceConnection $resourceConnection,
        Registry $registry,
        ?string $memoizedProductLinkField = null
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productLinkCollectionFactory = $productLinkCollectionFactory;
        $this->productLinkFactory = $productLinkFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->productVisibility = $productVisibility;
        $this->entityMetadataPool = $entityMetadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->registry = $registry;
        $this->memoizedProductLinkField = $memoizedProductLinkField;
    }

    /**
     * Apply criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @param AbstractDb $collection
     */
    protected function applyCriteria(SearchCriteriaInterface $criteria, AbstractDb $collection): void
    {
        $this->collectionProcessor->process($criteria, $collection);
    }

    /**
     * Extract product id
     *
     * @param mixed $item
     * @return mixed
     * @throws \Exception
     */
    protected function extractProductId(mixed $item): mixed
    {
        $linkField = $this->getProductLinkField();
        $product = $item->getProduct();
        return ($product ? $product->getData($linkField) : null)
            ?? $item->getProductId()
            ?? $item->getData($linkField)
            ?? $item->getId();
    }

    /**
     * Return either entity_id (on open source) or row_id (on commerce, for staging)
     *
     * @throws \Exception
     */
    protected function getProductLinkField(): string
    {
        if (!$this->memoizedProductLinkField) {
            $this->memoizedProductLinkField = $this->entityMetadataPool
                ->getMetadata(ProductInterface::class)
                ->getLinkField();
        }

        return $this->memoizedProductLinkField;
    }

    /**
     * Create product link collection
     *
     * @param array $productIds
     * @param array $attributes
     * @return ProductLinkCollection
     */
    protected function createProductLinkCollection(array $productIds, $attributes = []): ProductLinkCollection
    {
        /** @var ProductLinkCollection $collection */
        $collection = $this->productLinkCollectionFactory->create(['productIds' => $productIds]);
        $collection->setLinkModel($this->getLinkTypeModel())
            ->setIsStrongMode()
            ->setPositionOrder()
            ->addStoreFilter()
            ->setVisibility($this->productVisibility->getVisibleInCatalogIds())
            ->addAttributeToSelect(array_merge(
                ['small_image', 'name', 'attribute_to_link'],
                $attributes
            ));

        return $collection;
    }

    /**
     * Get linked items
     *
     * @param mixed $items
     * @return array
     */
    public function getLinkedItems($items): array
    {
        return $this->getLinkedCollection($items)->getItems();
    }

    /**
     * Get linked ids
     *
     * @param array $items
     * @return array
     */
    public function getLinkedIds($items = []): array
    {
        $connection = $this->resourceConnection->getConnection();
        $productIds = filter(map([$this, 'extractProductId'], $items));

        $select = $connection->select()
            ->from(
                [$this->resourceConnection->getTableName('catalog_product_link')],
                ['product_id', 'linked_product_id']
            )
            ->where('link_type_id = ?', Link::LINK_TYPE_LINKED);

        if (count($productIds)) {
            $select = $select->where('product_id IN (?)', $productIds);
        }

        return $connection->fetchAll($select);
    }

    /**
     * Get linked items size
     *
     * @param array $items
     * @return array
     */
    public function getLinkedItemsSize($items = []): array
    {
        $connection = $this->resourceConnection->getConnection();
        $productIds = filter(map([$this, 'extractProductId'], $items));

        $select = $connection->select()
            ->from(
                ['link' => $this->resourceConnection->getTableName('catalog_product_link')],
                ['product_id', 'COUNT(linked_product_id)']
            )
            ->joinLeft(
                ['at_visibility' => $this->resourceConnection->getTableName('catalog_product_entity_int')],
                'at_visibility.entity_id = link.linked_product_id'
            )
            ->joinLeft(
                ['eav_attribute' => $this->resourceConnection->getTableName('eav_attribute')],
                'eav_attribute.attribute_id = at_visibility.attribute_id'
            )
            ->where('eav_attribute.attribute_code = ?', 'visibility')
            ->where('at_visibility.value IN (?)', $this->productVisibility->getVisibleInCatalogIds())
            ->where('link_type_id = ?', Link::LINK_TYPE_LINKED)
            ->group('product_id');

        if (count($productIds)) {
            $select = $select->where('product_id IN (?)', $productIds);
        }

        return $connection->fetchPairs($select);
    }

    /**
     * Get linked items
     *
     * @param array $items
     * @return ProductLinkCollection
     */
    public function getLinkedCollection($items): ProductLinkCollection
    {
        return $this->loadLinkedCollection($items);
    }

    /**
     * Load linked collection
     *
     * @param array $items
     * @return ProductLinkCollection
     */
    protected function loadLinkedCollection($items): ProductLinkCollection
    {
        // $items can be anything with a getProductId() or getEntityId() or getId() method
        $productIds = filter(map([$this, 'extractProductId'], $items));

        $attributes = [];
        foreach ($items as $item) {
            /** @var Product $item */
            if (!$item->getData('attribute_to_link')) {
                continue;
            }
            $attributes[] = $item->getData('attribute_to_link');
        }

        $collection = $this->createProductLinkCollection($productIds, $attributes);

        $product = $this->getProduct();
        if ($product && $product->getId()) {
            $collection->addExcludeProductFilter([$product->getId()]);
        }

        $this->applyCriteria($this->searchCriteriaBuilder->create(), $collection);

        // Group by product id field - required to avoid duplicate products in collection
        $collection->setGroupBy();

        $collection->each('setDoNotUseCategoryId', [true]);

        return $collection;
    }

    /**
     * Get link type model
     *
     * @return Product\Link
     */
    protected function getLinkTypeModel(): Product\Link
    {
        $linkModel = $this->productLinkFactory->create();
        $linkModel->setLinkTypeId(Link::LINK_TYPE_LINKED);
        return $linkModel;
    }

    /**
     * Add filter to be applied when an item getter is called.
     *
     * Filters added by consecutive calls to addFilter() are combined with AND.
     *
     * @param string $field
     * @param mixed $value
     * @param string $conditionType
     * @return $this
     */
    public function addFilter(string $field, mixed $value, string $conditionType = 'eq'): self
    {
        $this->searchCriteriaBuilder->addFilter($field, $value, $conditionType);
        return $this;
    }

    /**
     * Set page size
     *
     * @param int $pageSize
     * @return ProductList
     */
    public function setPageSize(int $pageSize): self
    {
        $this->searchCriteriaBuilder->setPageSize($pageSize);
        return $this;
    }

    /**
     * Get current product
     *
     * @return Product|null
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Get attribute options sort order
     *
     * @return array
     */
    public function getOptionsSortOrder()
    {
        $product = $this->getProduct();

        if (!$product || !$product->getData('attribute_to_link')) {
            return [];
        }

        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(
                ['ea_option_value' => $this->resourceConnection->getTableName('eav_attribute_option_value')],
                ['option_id']
            )
            ->joinInner(
                ['ea_option' => $this->resourceConnection->getTableName('eav_attribute_option')],
                'ea_option.option_id = ea_option_value.option_id',
                ['sort_order']
            )
            ->joinInner(
                ['ea' => $this->resourceConnection->getTableName('eav_attribute')],
                'ea.attribute_id = ea_option.attribute_id'
            )
            ->where('ea.attribute_code = ?', $product->getData('attribute_to_link'))
            ->where('ea_option_value.store_id = 0')
            ->order('ea_option.sort_order ASC');

        return $connection->fetchPairs($select);
    }
}
