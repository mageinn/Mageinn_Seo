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

namespace Mageinn\Seo\Plugin\PageConfig;


class RemoveMeta
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * RemoveMeta constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }
    /**
     * @param $subject
     * @param string $return
     * @return string|string
     */
    public function afterGetDescription(\Magento\Framework\View\Page\Config $subject, $return)
    {
        if ( (int) $this->request->getParam('p') > 1) {
            return '';
        }
        return $return;
    }
    /**
     * @param $subject
     * @param string $return
     * @return string|string
     */
    public function afterGetKeywords(\Magento\Framework\View\Page\Config $subject, $return)
    {
        if ( (int) $this->request->getParam('p') > 1) {
            return '';
        }
        return $return;
    }
}