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

use Magento\UrlRewrite\Model\OptionProvider;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Framework\Exception\LocalizedException;
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
     * Current sitemapImages increment
     *
     * @var int
     */
    protected $_sitemapImagesIncrement = 0;

    /**
     * @var \Mageinn\Seo\Helper\Data
     */
    protected $helper;


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
        \Mageinn\Seo\Helper\Data $helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot $documentRoot = null
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
            $documentRoot
        );
        $this->urlFinder = $urlFinder;
        $this->helper = $helper;
    }


    /**
     * Initialize sitemap
     *
     * @return void
     */
    protected function _initSitemapItems()
    {

        $this->collectSitemapItems();

        $this->_tags = [
            self::TYPE_INDEX => [
                self::OPEN_TAG_KEY => '<?xml version="1.0" encoding="UTF-8"?>' .
                    PHP_EOL .
                    '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' .
                    PHP_EOL,
                self::CLOSE_TAG_KEY => '</sitemapindex>',
            ],
            self::TYPE_URL => [
                self::OPEN_TAG_KEY => '<?xml version="1.0" encoding="UTF-8"?>' .
                    PHP_EOL .
                    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' .
                    ' xmlns:content="http://www.google.com/schemas/sitemap-content/1.0"' .
                    ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' .
                    PHP_EOL,
                self::CLOSE_TAG_KEY => '</urlset>',
            ],
        ];
    }

    /**
     * Generate XML
     *
     * @return $this
     */
    public function generateXml()
    {
        $this->_initSitemapItems();

        if($this->helper->getActiveFlag())
        {
            return $this->createSitemapFiles();
        }

        /** @var $sitemapItem \Magento\Framework\DataObject */
        foreach ($this->_sitemapItems as $sitemapItem) {
            $changefreq = $sitemapItem->getChangefreq();
            $priority = $sitemapItem->getPriority();
            foreach ($sitemapItem->getCollection() as $item) {
                $xml = $this->_getSitemapRow(
                    $item->getUrl(),
                    $item->getUpdatedAt(),
                    $changefreq,
                    $priority,
                    $item->getImages()
                );
                if ($this->_isSplitRequired($xml) && $this->_sitemapIncrement > 0) {
                    $this->_finalizeSitemap();
                }
                if (!$this->_fileSize) {
                    $this->_createSitemap();
                }
                $this->_writeSitemapRow($xml);
                // Increase counters
                $this->_lineCount++;
                $this->_fileSize += strlen($xml);
            }
        }
        $this->_finalizeSitemap();

        if ($this->_sitemapIncrement == 1) {
            // In case when only one increment file was created use it as default sitemap
            $path = rtrim(
                    $this->getSitemapPath(),
                    '/'
                ) . '/' . $this->_getCurrentSitemapFilename(
                    $this->_sitemapIncrement
                );
            $destination = rtrim($this->getSitemapPath(), '/') . '/' . $this->getSitemapFilename();

            $this->_directory->renameFile($path, $destination);
        } else {
            // Otherwise create index file with list of generated sitemaps
            $this->_createSitemapIndex();
        }

        $this->setSitemapTime($this->_dateModel->gmtDate('Y-m-d H:i:s'));
        $this->save();

        return $this;
    }
    /**
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function createSitemapFiles()
    {
        $this->createSitemapFileWithoutImages();
        $this->createSitemapFileWithImages();
        $this->setSitemapTime($this->_dateModel->gmtDate('Y-m-d H:i:s'));
        $this->save();
        return $this;
    }
    /**
     * Create Sitemap.xml file without images
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function createSitemapFileWithImages()
    {
        $this->createSitemapImagesFile($this->getSitemapImagesFilename(), true);
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
     * Create Sitemap.xml file without images
     *
     * @param string $filename
     * @param bool $withImages
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    private function createSitemapFile(string $filename, bool $withImages)
    {
        /** @var $sitemapItem \Magento\Framework\DataObject */
        foreach ($this->_sitemapItems as $sitemapItem) {
            $changefreq = $sitemapItem->getChangefreq();
            $priority = $sitemapItem->getPriority();
            foreach ($sitemapItem->getCollection() as $item) {
                $url = $this->replaceUrlWithRewrite( $item->getUrl() );
                if( $withImages && empty( $item->getImages() ) ) {
                    continue;
                }
                $url = $this->checkUrlForUrlRewriteWithTrailingSlash( $url );

                $xml = $this->_getSitemapRow(
                    $url,
                    $item->getUpdatedAt(),
                    $changefreq,
                    $priority,
                    $withImages ? $item->getImages() : null
                );
                if ($this->_isSplitRequired($xml) && $this->_sitemapIncrement > 0) {
                    $this->_finalizeSitemap();
                }
                if (!$this->_fileSize) {
                    $this->_createSitemap();
                }
                $this->_writeSitemapRow($xml);
                // Increase counters
                $this->_lineCount++;
                $this->_fileSize += strlen($xml);
            }
        }

        $this->_finalizeSitemap();

        if ($this->_sitemapIncrement == 1) {
            // In case when only one increment file was created use it as default sitemap
            $path = rtrim(
                    $this->getSitemapPath(),
                    '/'
                ) . '/' . $this->_getCurrentSitemapFilename(
                    $this->_sitemapIncrement
                );
            $destination = rtrim($this->getSitemapPath(), '/') . '/' . $filename;

            $this->_directory->renameFile($path, $destination);
        } else {
            // Otherwise create index file with list of generated sitemaps
            $this->_createSitemapIndex();
        }
    }

    /**
     * Create SitemapImages.xml file with images
     *
     * @param string $filename
     * @param bool $withImages
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    private function createSitemapImagesFile(string $filename, bool $withImages)
    {

        /** @var $sitemapItem \Magento\Framework\DataObject */
        foreach ($this->_sitemapItems as $sitemapItem) {
            $changefreq = $sitemapItem->getChangefreq();
            $priority = $sitemapItem->getPriority();
            foreach ($sitemapItem->getCollection() as $item) {
                $url = $this->replaceUrlWithRewrite( $item->getUrl() );
                if( $withImages && empty( $item->getImages() ) )
                {
                    continue;
                }
                $url = $this->checkUrlForUrlRewriteWithTrailingSlash( $url );

                $xml = $this->_getSitemapRow(
                    $url,
                    $item->getUpdatedAt(),
                    $changefreq,
                    $priority,
                    $withImages ? $item->getImages() : null
                );

                if ($this->_isSplitRequired($xml) && $this->_sitemapImagesIncrement > 0)
                {
                    $this->_finalizeSitemap();
                }

                if (!$this->_fileSize)
                {
                    $this->createSitemapImages();
                }

                $this->_writeSitemapRow($xml);
                // Increase counters
                $this->_lineCount++;
                $this->_fileSize += strlen($xml);
            }
        }

        $this->_finalizeSitemap();

        if ($this->_sitemapImagesIncrement == 1) {
            // In case when only one increment file was created use it as default sitemap
            $path = rtrim(
                    $this->getSitemapPath(),
                    '/'
                ) . '/' . $this->getCurrentSitemapImagesFilename(
                    $this->_sitemapImagesIncrement
                );
            $destination = rtrim($this->getSitemapPath(), '/') . '/' . $filename;

            $this->_directory->renameFile($path, $destination);
        } else {
            // Otherwise create index file with list of generated sitemaps
            $this->createSitemapImagesIndex();
        }
    }

    /**
     * Generate sitemap index XML file
     *
     * @param string $filename
     * @return void
     */
    private function createSitemapImagesIndex()
    {
        $this->createSitemapImages($this->getSitemapImagesFilename(), self::TYPE_INDEX);
        for ($i = 1; $i <= $this->_sitemapIncrement; $i++) {
            $xml = $this->_getSitemapIndexRow($this->getCurrentSitemapImagesFilename($i), $this->_getCurrentDateTime());
            $this->_writeSitemapRow($xml);
        }
        $this->_finalizeSitemap(self::TYPE_INDEX);
    }

    /**
     * Get current sitemapImages filename
     *
     * @param int $index
     * @return string
     */
    private function getCurrentSitemapImagesFilename($index)
    {
        return str_replace('.xml', '', $this->getSitemapImagesFilename()) . '-' . $this->getStoreId() . '-' . $index . '.xml';
    }


    /**
     * Create new sitemapImages file
     *
     * @param null|string $fileName
     * @param string $type
     * @return void
     * @throws LocalizedException
     */
    private function createSitemapImages($fileName = null, $type = self::TYPE_URL)
    {
        if (!$fileName) {
            $this->_sitemapImagesIncrement++;
            $fileName = $this->getCurrentSitemapImagesFilename($this->_sitemapImagesIncrement);
        }

        $path = rtrim($this->getSitemapPath(), '/') . '/' . $fileName;
        $this->_stream = $this->_directory->openFile($path);

        $fileHeader = sprintf($this->_tags[$type][self::OPEN_TAG_KEY], $type);
        $this->_stream->write($fileHeader);
        $this->_fileSize = strlen($fileHeader . sprintf($this->_tags[$type][self::CLOSE_TAG_KEY], $type));
    }


    /**
     * Return sitemap filename with images
     *
     * @return mixed|string
     */
    private function getSitemapImagesFilename(){
        $filename = $this->getSitemapFilename();
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $filename = pathinfo($filename, PATHINFO_FILENAME);
        $filename = sprintf('%sImages.%s', $filename, $ext);
        return $filename;
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
