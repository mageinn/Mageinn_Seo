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
 * Class UpdateGalleryItemsData
 * @package Mageinn\Seo\Plugin
 */
class UpdateGalleryItemsData
{
    /**
     * @var \Mageinn\Seo\Helper\Data
     */
    protected $helper;

    /**
     * UpdateGalleryItemsData constructor.
     * @param \Mageinn\Seo\Helper\Data $helper
     */
    public function __construct(
        \Mageinn\Seo\Helper\Data $helper
    )
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Catalog\Block\Product\View\Gallery $subject
     * @param $result
     * @return string
     */
    public function afterGetGalleryImagesJson(\Magento\Catalog\Block\Product\View\Gallery $subject, $result)
    {
        if($this->helper->getActiveFlag()){
            $result = json_decode($result, TRUE);
            $videoCounter = 0;
            $imgCounter = 0;
            foreach ($result as $key => &$item){
                switch ($item['type']) {
                    case 'image':
                        $captionPrefix = 'Photo ' . ++$imgCounter . ':';
                        break;
                    case 'video':
                        $captionPrefix = 'Video ' . ++$videoCounter . ':';
                        break;
                    default:
                        $captionPrefix = '';
                        break;
                }
                $result[$key]['caption'] = $captionPrefix . $item['caption'];
            }
            return json_encode($result);
        }

        return $result;
    }
}