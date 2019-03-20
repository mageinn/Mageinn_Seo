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

namespace Mageinn\Seo\Model;

use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapConfigReaderInterface;
use Magento\Sitemap\Model\SitemapItemInterface;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
/**
 * Class Sitemap
 * @package Mageinn\SiteMap\Model
 */
class Sitemap extends \Magento\Sitemap\Model\Sitemap
{
    /**
     * @var UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * Sitemap constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Sitemap\Helper\Data $sitemapData
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory $categoryFactory
     * @param \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory $productFactory
     * @param \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory $cmsFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $modelDate
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param UrlFinderInterface $urlFinder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param \Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot|null $documentRoot
     * @param ItemProviderInterface|null $itemProvider
     * @param SitemapConfigReaderInterface|null $configReader
     * @param \Magento\Sitemap\Model\SitemapItemInterfaceFactory|null $sitemapItemFactory
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Sitemap\Helper\Data $sitemapData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory $categoryFactory,
        \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory $productFactory,
        \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory $cmsFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $modelDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        UrlFinderInterface $urlFinder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot $documentRoot = null,
        ItemProviderInterface $itemProvider = null,
        SitemapConfigReaderInterface $configReader = null,
        \Magento\Sitemap\Model\SitemapItemInterfaceFactory $sitemapItemFactory = null
    )
    {
        parent::__construct(
            $context,
            $registry,
            $escaper,
            $sitemapData,
            $filesystem,
            $categoryFactory,
            $productFactory,
            $cmsFactory,
            $modelDate,
            $storeManager,
            $request,
            $dateTime,
            $resource,
            $resourceCollection,
            $data,
            $documentRoot,
            $itemProvider,
            $configReader,
            $sitemapItemFactory
        );
        $this->urlFinder = $urlFinder;
    }
    /**
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function createSitemapFiles()
    {
        $this->_initSitemapItems();
        $this->createSitemapFileWithoutImages();
        $this->createSitemapFileWithImages();
        $this->setSitemapTime($this->_dateModel->gmtDate('Y-m-d H:i:s'));
        $this->save();
        return $this;
    }
    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function createSitemapFileWithImages()
    {
        $filename = $this->getSitemapFilename();
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $filename = pathinfo($filename, PATHINFO_FILENAME);
        $filename = sprintf('%sImages.%s', $filename, $ext);
        $this->createSitemapFile($filename, true);
    }
    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function createSitemapFileWithoutImages()
    {
        $this->createSitemapFile($this->getSitemapFilename(), false);
    }
    /**
     * @param string $filename
     * @param bool $withImages
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function createSitemapFile(string $filename, bool $withImages)
    {
        /** @var $item SitemapItemInterface */
        foreach ($this->_sitemapItems as $item) {
            $url = $this->replaceUrlWithRewrite( $item->getUrl() );
            if( $withImages && empty( $item->getImages() ) ) {
                continue;
            }

            $url = $this->checkUrlForUrlRewriteWithTrailingSlash( $url );
            $xml = $this->_getSitemapRow(
                $url,
                $item->getUpdatedAt(),
                $item->getChangeFrequency(),
                $item->getPriority(),
                $withImages? $item->getImages() : null
            );
            if ($this->_isSplitRequired($xml) && $this->_sitemapIncrement > 0) {
                $this->_finalizeSitemap();
            }
            if (!$this->_fileSize) {
                $this->_createSitemap();
            }
            $this->_writeSitemapRow($xml);
            $this->_lineCount++;
            $this->_fileSize += strlen($xml);
        }
        $this->_finalizeSitemap();
        $path = rtrim(
                $this->getSitemapPath(),
                '/'
            ) . '/' . $this->_getCurrentSitemapFilename(
                $this->_sitemapIncrement
            );
        $destination = rtrim($this->getSitemapPath(), '/') . '/' . $filename;
        $this->_directory->renameFile($path, $destination);
    }
    /**
     * Check for url rewrite with trailing slash,
     * if this url rewrite exists return it trailing slash
     * else return not modified url
     *
     * @param string $url
     * @return string
     */
    private function checkUrlForUrlRewriteWithTrailingSlash(string $url)
    {
        $urlRewrite = $this->urlFinder->findOneByData(
            [
                UrlRewrite::REQUEST_PATH => $url
            ]
        );
        if( empty($urlRewrite) ) {
            return $url;
        }
        if($urlRewrite->getTargetPath() == $url . '/') {
            return $url . '/';
        } else {
            return $url;
        }
    }
    /**
     * Check and replace if url has url rewrite.
     *
     * @param string $url
     * @return string
     */
    private function replaceUrlWithRewrite($url) {
        $redirectTypes = [
            OptionProvider::TEMPORARY,
            OptionProvider::PERMANENT
        ];
        $urlRewrite = $this->urlFinder->findOneByData(
            [
                UrlRewrite::TARGET_PATH => $url
            ]
        );
        if( empty($urlRewrite)) {
            return $url;
        }
        if( in_array( $urlRewrite->getRedirectType(), $redirectTypes ) ) {
            return $url;
        }
        $url = $urlRewrite->getRequestPath();
        return $url;
    }
}
