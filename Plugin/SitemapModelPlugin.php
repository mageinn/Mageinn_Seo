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

namespace Mageinn\Seo\Plugin;

/**
 * Class SitemapModelPlugin
 * @package Mageinn\SiteMap\Plugin
 */
class SitemapModelPlugin
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Mageinn\Seo\Helper\Data
     */
    protected $helper;
    /**
     * SitemapModelPlugin constructor.
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Mageinn\Seo\Model\ResourceModel\Sitemap\CollectionFactory $collectionFactory,
        \Mageinn\Seo\Helper\Data $helper
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
    }
    /**
     * @param \Magento\Sitemap\Model\Sitemap $sitemap
     * @param callable $proceed
     * @return \Magento\Sitemap\Model\Sitemap
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function aroundGenerateXml(\Magento\Sitemap\Model\Sitemap $sitemap, callable $proceed)
    {
        if(!$this->helper->getActiveFlag())
        {
            return $proceed();
        }

        $collection = $this->collectionFactory->create();
        /**
         * @var $overridedSitemap \Mageinn\Seo\Model\Sitemap
         */
        $collection->addFilter($sitemap->getIdFieldName(), $sitemap->getId());
        $overriddenSitemap = $collection->getFirstItem();
        $overriddenSitemap->createSitemapFiles();

        return $sitemap;
    }
}
