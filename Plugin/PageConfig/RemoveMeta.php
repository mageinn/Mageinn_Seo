<?php
/**
 * Created by PhpStorm.
 * User: airat
 * Date: 20.03.19
 * Time: 13:28
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