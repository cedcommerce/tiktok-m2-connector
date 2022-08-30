<?php

namespace Ced\MagentoConnector\Helper;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;

class SourceItem
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

     /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceItemRepositoryInterface $sourceItemRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceItemRepository = $sourceItemRepository;
    }

    /**
     * @param $sku
     * @return array
     */
    public function getSourceItemDetailBySKU($sku)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->create();
        $result = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        $returnValue = [];
        foreach ($result as $item) {
            $returnValue[] = $item->getData();
        }
        return $returnValue;
    }
}
