<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_MagentoConnector
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\MagentoConnector\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductSaveAfter implements ObserverInterface
{

    /**
     * Message Manager
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * Request
     * @var \Magento\Framework\App\RequestInterface
     */
    public $request;

    /**
     * Registry
     * @var \Magento\Framework\Registry
     */
    public $registry;

    public $json;

    public $config;

    public $apiEndPoint;

    public $productRepository;

    public $resourceConfigurable;

    public $resourceProduct;

    public $logger;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Ced\MagentoConnector\Helper\Config $config
     * @param \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product $resourceProduct
     * @param \Ced\MagentoConnector\Helper\Logger $logger
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $resourceConfigurable
     * @param \Magento\Framework\Json\Helper\Data $json
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Ced\MagentoConnector\Helper\Config $config,
        \Ced\MagentoConnector\Helper\ApiEndPoint $apiEndPoint,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        \Ced\MagentoConnector\Helper\Logger $logger,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $resourceConfigurable,
        \Magento\Framework\Json\Helper\Data $json
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->config = $config;
        $this->apiEndPoint = $apiEndPoint;
        $this->productRepository = $productRepository;
        $this->json = $json;
        $this->resourceProduct = $resourceProduct;
        $this->resourceConfigurable = $resourceConfigurable;
        $this->logger = $logger;
    }

    /**
     * Catalog product save after event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return boolean
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $this->logger->logger(
            'Product change',
            'Observer',
            "product change observer",
            'Product save start'
        );
        if (!$this->config->isConnected()) {
            $this->logger->logger(
                'Product change',
                'Observer',
                "product change observer",
                'You are not connected'
            );
            return $observer;
        }
        $product = $observer->getEvent()->getProduct();

        if (empty($product)) {
            $this->logger->logger(
                'Product change',
                'Observer',
                "product Data not found ",
                'Product data'
            );
            return false;
        }
        $userId = $this->config->getUserId();
        if ($product->getTypeId() == 'simple' || true) {
            $compareArray = ['cost','status','visibility','is_salable'];
            $id = $product->getId();
            $productRepo = $this->productRepository->getById($id);
            $attributes = $productRepo->getAttributes();
            foreach ($attributes as $attr) {
                array_push($compareArray, $attr->getName());
            }
            $sku = $product->getSku();
            $data = [
                'user_id' => $userId,
                'sku' => $sku,
            ];

            $newValues = [];
            $a = [
                'required_options', 'has_options', 'quantity_and_stock_status', 'updated_at',
                'price', 'special_price', 'special_from_date'
            ];

            foreach ($compareArray as $value) {
                if (!in_array($value, $a)) {
                    $old = $product->getOrigData($value);
                    $new = $product->getData($value);

                    if ($old !== $new) {
                        $newValues[$value] = $new;
                        if ($value == 'sku') {
                            $newValues['sku'] = $old;
                            $newValues['old_sku'] = $old;
                            $newValues['new_sku'] = $new;

                            $data['old_sku'] = $old;
                            $data['new_sku'] = $new;
                        }
                    }
                }
            }
            //  print_r($newValues);
            //   die;
            $im = ['image_label','small_image_label', 'thumbnail_label', 'media_gallery'];
            foreach ($im as $i) {
                if (isset($newValues[$i]) && $i == "media_gallery") {
                    $flag = false;
                    if (isset($newValues['media_gallery']['images'])) {
                        foreach ($newValues['media_gallery']['images'] as $images) {
                            $flag = isset($images['new_file']) ? true : false;
                            if (isset($images['new_file'])) {
                                $flag = true;
                            }
                        }
                    }
                    if (!$flag) {
                        unset($newValues['media_gallery']);
                    }
                } elseif (isset($newValues[$i])) {
                    unset($newValues[$i]);
                }
            }

            if ($newValues) {

                $data['data_type_status'] = 'update';
                $data['product_type'] = $product->getTypeId();
                $childId = $product->getId();
                $parentIds = $this->resourceConfigurable->getParentIdsByChild($childId);
                if (!empty($parentIds)) {
                    $skus = $this->resourceProduct->getProductsSku($parentIds);
                    if (isset($skus[0]['sku'])) {
                        $sku = $skus[0]['sku'];
                        $data['product_type'] = 'configurable';
                        $data['parent_sku'] = $sku;
                    }
                }
                $newValues = [];
            } else {

                $b = ['quantity_and_stock_status',
                    'price', 'special_price', 'special_from_date'];
                foreach ($compareArray as $value) {
                    if (!in_array($value, $b)) {
                        $old = $product->getOrigData($value);
                        $new = $product->getData($value);
                        if ($old !== $new) {
                            $newValues[$value] = $new;
                        }
                    }
                }
                $im = ['image_label','small_image_label', 'thumbnail_label', 'media_gallery'];
                foreach ($im as $i) {
                    if (isset($newValues[$i])) {
                        unset($newValues[$i]);
                    }
                }

                $data['data_type_status'] = 'stock_price';
            }
            $orgQty = $product->getOrigData('quantity_and_stock_status');
            $orgQty1 = $product->getData('quantity_and_stock_status');
            $newValue = $oldValue = isset($orgQty['qty']) ? (int)$orgQty['qty'] : '';
            $postData = $this->request->getParam('product');
            if (isset($postData['quantity_and_stock_status']['qty'])) {
                $newValue = (int)$postData['quantity_and_stock_status']['qty'];
            }
            $isInStock = (int)$postData['quantity_and_stock_status']['is_in_stock'];
            //if out of stock then set value to 0
            if (!$isInStock) {
                $newValue = 0;
            }

            $specialPrice = $product->getOrigData('special_price');
            $newSpecialPrice = $product->getData('special_price');
            $price = (float)$product->getOrigData('price');
            $newPrice = (float)$product->getData('price');
            if ($oldValue != $newValue || $newSpecialPrice != $specialPrice || $price != $newPrice) {
                if (($newValue >= 0 || $oldValue >= 0) && $newValue != $oldValue) {
                    $newValues['stock_change']['old_stock'] = $oldValue;
                    $newValues['stock_change']['new_stock'] = $newValue;
                }
                if (($specialPrice || $newSpecialPrice) && $specialPrice != $newSpecialPrice) {
                    $newValues['special_price_change']['old_price'] = $specialPrice;
                    $newValues['special_price_change']['new_price'] = $newSpecialPrice;
                }
                if (($price >= 0 || $newPrice >= 0) && $price != $newPrice) {
                    $newValues['price_change']['old_price'] = $price;
                    $newValues['price_change']['new_price'] = $newPrice;
                }
                $data = array_merge($data, $newValues);

            }
            $this->logger->logger(
                'Product change',
                'Observer',
                json_encode($data)." || ".json_encode($newValues),
                'Product data'
            );

            if ($newValues || isset($data['new_sku']) ||
                (isset($data['data_type_status']) && $data['data_type_status'] == "update")) {
                $token = $this->config->getAccessToken();
                $response = $this->apiEndPoint->productChangeSend($token, $data);
            }
            return $observer;
        }
        $this->logger->logger(
            'Product change',
            'Observer',
            "product change observer",
            'Product save end'
        );
        return $observer;
    }
}
