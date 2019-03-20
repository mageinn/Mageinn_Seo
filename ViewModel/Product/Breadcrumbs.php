<?php
/**
 * Mageinn_Seo extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Mageinn
 * @package     Mageinn_Seo
 * @copyright   Copyright (c) 2017 Mageinn. (http://mageinn.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Mageinn
 * @package    Mageinn_Seo
 * @author     Mageinn
 */

namespace Mageinn\Seo\ViewModel\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Escaper;
/**
 * Class Breadcrumbs
 * @package Diamondart\Core\ViewModel\Product
 */
class Breadcrumbs extends DataObject implements ArgumentInterface
{
    /**
     * Catalog data.
     *
     * @var Data
     */
    private $catalogData;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * Breadcrumbs constructor.
     * @param Data $catalogData
     * @param ScopeConfigInterface $scopeConfig
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Json|null $json
     * @param Escaper|null $escaper
     */
    public function __construct(
        Data $catalogData,
        ScopeConfigInterface $scopeConfig,
        CategoryRepositoryInterface $categoryRepository,
        Json $json = null,
        Escaper $escaper = null
    ) {
        parent::__construct();
        $this->catalogData = $catalogData;
        $this->scopeConfig = $scopeConfig;
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);
        $this->categoryRepository = $categoryRepository;
    }
    /**
     * Returns category URL suffix.
     *
     * @return mixed
     */
    public function getCategoryUrlSuffix()
    {
        return $this->scopeConfig->getValue(
            'catalog/seo/category_url_suffix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Checks if categories path is used for product URLs.
     *
     * @return bool
     */
    public function isCategoryUsedInProductUrl(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'catalog/seo/product_use_categories',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Returns product name.
     *
     * @return string
     */
    public function getProductName(): string
    {
        return $this->catalogData->getProduct() !== null
            ? $this->catalogData->getProduct()->getName()
            : '';
    }
    /**
     * Return category item to use in breadcrumbs
     *
     * @return array|null
     */
    public function getProductMainCategory()
    {
        $categoryItem = null;
        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        $product = $this->catalogData->getProduct();
        $rootCategoryId = $product->getStore()->getRootCategoryId();
        $categoryIds = $product->getCategoryIds();
        if( ($key = array_search($rootCategoryId, $categoryIds)) !== false ) {
            unset($categoryIds[$key]);
        }
        if( !empty($categoryIds) ) {
            $mainCategoryId = min($categoryIds);
            try {
                $category = $this->categoryRepository->get($mainCategoryId, $product->getStoreId());
                if( $category->getIsActive() ) {
                    $categoryItem = [
                        'name' => 'mainCategory',
                        'label' => $category->getName(),
                        'link' => $category->getUrl(),
                        'title' => ''
                    ];
                }
            } catch (NoSuchEntityException $exception) {}
        }
        return $categoryItem;
    }
    /**
     * Returns breadcrumb json with html escaped names
     *
     * @return string
     */
    public function getJsonConfigurationHtmlEscaped() : string
    {
        return json_encode(
            [
                'breadcrumbs' => [
                    'categoryUrlSuffix' => $this->escaper->escapeHtml($this->getCategoryUrlSuffix()),
                    'userCategoryPathInUrl' => (int)$this->isCategoryUsedInProductUrl(),
                    'product' => $this->escaper->escapeHtml($this->getProductName()),
                    'mainCategory' => $this->getProductMainCategory()
                ]
            ],
            JSON_HEX_TAG
        );
    }
    /**
     * Returns breadcrumb json.
     *
     * @return string
     * @deprecated in favor of new method with name {suffix}Html{postfix}()
     */
    public function getJsonConfiguration()
    {
        return $this->getJsonConfigurationHtmlEscaped();
    }
}
