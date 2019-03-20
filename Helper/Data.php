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

namespace Mageinn\Seo\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use \Magento\Store\Model\ScopeInterface;


class Data extends AbstractHelper
{
    private $config;

    public function __construct(Context $context)
    {
        $this->config = $context->getScopeConfig();
        parent::__construct($context);
    }

    /**
     * Get config value by key
     *
     * @param string $key
     * @return mixed
     */
    public function getConfig($key)
    {
        return $this->config->getValue('mageinn_seo/general/' . $key, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get is module enabled
     *
     * @return bool
     */
    public function getActiveFlag()
    {
        return (bool)$this->getConfig('enable');
    }
}