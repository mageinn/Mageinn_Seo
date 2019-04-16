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
 * Class Pager
 */
class Pager
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

    public function aroundGetPagerUrl(\Magento\Theme\Block\Html\Pager $subject, callable $proceed, $params)
    {
        if($this->helper->getActiveFlag())
        {
            $urlParams = [];
            $urlParams['_current'] = true;
            $urlParams['_escape'] = true;
            $urlParams['_use_rewrite'] = true;
            $urlParams['_fragment'] = $subject->getFragment();
            if($params['p'] == 1) {
                $params['p'] = null;
            }
            $urlParams['_query'] = $params;
            return $subject->getUrl($subject->getPath(), $urlParams);
        }

        return $proceed($params);
    }
}