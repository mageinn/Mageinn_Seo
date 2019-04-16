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

namespace Mageinn\Seo\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class Category
 * @package Mageinn\Seo\Observer
 */
class Category implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Helper\Category
     */
    private $categoryHelper;
    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $pageConfig;
    /*
     *
     */
    private $urlBuilder;
    /**
     * @var array
     */
    private $matchedHandlers = array('catalog_category_view');

    /**
     * @var \Mageinn\Seo\Helper\Data
     */
    private $helper;
    /**
     * Category constructor.
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Mageinn\Seo\Helper\Data $helper
    )
    {
        $this->categoryHelper = $categoryHelper;
        $this->pageConfig = $pageConfig;
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($this->helper->getActiveFlag()) {
            if (!in_array($observer->getEvent()->getFullActionName(), $this->matchedHandlers)) {
                return $this;
            }
            /** @var \Magento\Catalog\Block\Product\ListProduct $productListBlock */
            $productListBlock = $observer->getEvent()->getLayout()->getBlock('category.products.list');
            if ($productListBlock) {
                $category = $productListBlock->getLayer()->getCurrentCategory();
            } else {
                return $this;
            }
            /** remove default canonical tag */
            if ($this->categoryHelper->canUseCanonicalTag()) {
                $this->pageConfig->getAssetCollection()->remove($category->getUrl());
            }
            /** @var \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbarBlock */
            $toolbarBlock = $productListBlock->getToolbarBlock();
            /** @var \Magento\Theme\Block\Html\Page $pageBlock */
            $pagerBlock = $toolbarBlock->getChildBlock('product_list_toolbar_pager');
            $pagerBlock->setAvailableLimit($toolbarBlock->getAvailableLimit())
                ->setCollection($productListBlock->getLayer()->getProductCollection());
            /** Add rel canonical with page  */
            $this->pageConfig->addRemotePageAsset(
                $this->getPageUrl(),
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
            $title = $observer->getEvent()->getLayout()->getBlock('page.main.title')->getPageTitle();
            /** Add rel prev and rel next */
            if (1 < $pagerBlock->getCurrentPage()) {
                $observer->getEvent()->getLayout()->getBlock('page.main.title')->setPageTitle($title . " - Page " . $pagerBlock->getCurrentPage());
                $this->pageConfig->getTitle()->set($title . " - Page " . $pagerBlock->getCurrentPage());
                $prevPage = $pagerBlock->getCollection()->getCurPage(-1);
                if ($prevPage == 1) {
                    $params = [];
                } else {
                    $params = [
                        $pagerBlock->getPageVarName() => $prevPage
                    ];
                }
                $this->pageConfig->addRemotePageAsset(
                    $this->getPageUrl($params),
                    'link_rel',
                    ['attributes' => ['rel' => 'prev']]
                );
            }
            if ($pagerBlock->getCurrentPage() < $pagerBlock->getLastPageNum()) {
                $this->pageConfig->addRemotePageAsset(
                    $this->getPageUrl([
                        $pagerBlock->getPageVarName() => $pagerBlock->getCollection()->getCurPage(+1)
                    ]),
                    'link_rel',
                    ['attributes' => ['rel' => 'next']]
                );
            }
        }
    }
    /**
     * @param array $params
     * @return string
     */
    protected function getPageUrl($params = [])
    {
        $urlParams = [];
        $urlParams['_current'] = false;
        $urlParams['_escape'] = true;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_query'] = $params;
        return $this->urlBuilder->getUrl('*/*/*', $urlParams);
    }
}