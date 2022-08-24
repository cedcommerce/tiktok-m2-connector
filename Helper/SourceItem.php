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
     * Retrieves links that are assigned to $stockId
     *
     * @param array $skus
     * @return SourceItemInterface[]
     */
    public function getSourceItemDetailBySKU($skus)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, ['in' => $skus])
            ->create();
        $result = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        $returnValue = [];
        foreach ($result as $item) {
            $returnValue[] = $item->getData();
        }
        return $returnValue;
    }
}
