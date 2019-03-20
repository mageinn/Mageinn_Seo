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

namespace Mageinn\Seo\Model\View\Page;


/**
 * Page title
 *
 * @api
 * @since 100.0.2
 */
class Title extends \Magento\Framework\View\Page\Title
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;
    /**
     * Title constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($scopeConfig);
    }
    /**
     * @param string $title
     * @return string
     */
    protected function addConfigValues($title)
    {
        $preparedTitle = $this->scopeConfig->getValue(
                'design/head/title_prefix',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ) . ' ' . $title;
        $postfix = ' ' . $this->scopeConfig->getValue(
                'design/head/title_suffix',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        if ($this->request->getFullActionName() == 'cms_index_index') {
            $postfix = '';
        }
        return trim($preparedTitle . $postfix);
    }
}