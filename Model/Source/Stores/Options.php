<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_MagentoConnector
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\MagentoConnector\Model\Source\Stores;

class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    public $storeRepository;

    public function __construct(
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository
    ) {
        $this->storeRepository = $storeRepository;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $collection =  $this->storeRepository->getList();

        $data[] = ['value' => '','label' => 'Select Store'];
        if ($collection) {
            foreach ($collection as $store) {
                $data[] = ['value' => $store->getId(), 'label' => $store->getCode()];
            }
        } else {
            $data[] = ['value' => '','label' => 'No Account'];
        }
        return $data;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getAllOptions() as $option) {
            $options[$option['value']] = (string)$option['label'];
        }
        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }

    /**
     * Get MagentoConnector product status labels array with empty value
     *
     * @return array
     */
    public function getAllOption()
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }

    /**
     * Get MagentoConnector product status labels array for option element
     *
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * Get MagentoConnector product status
     *
     * @param string $optionId
     * @return null|string
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}
