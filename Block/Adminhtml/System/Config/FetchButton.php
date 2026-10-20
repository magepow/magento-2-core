<?php
/**
 * Copyright (c) Magepow. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Magepow\Core\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Escaper;

/**
 * Configuration field: "Fetch Now" button.
 *
 * Triggers an immediate feed refresh via the magepow/notification/fetch route.
 */
class FetchButton extends Field
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Escaper $escaper,
        array $data = []
    ) {
        $this->escaper = $escaper;
        parent::__construct($context, $data);
    }

    /**
     * Render the fetch button element HTML.
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $url = $this->getUrl('magepow/notification/fetch');
        return '<button type="button" class="action-default scalable" onclick="setLocation(\''
            . $this->escaper->escapeUrl($url) . '\')"><span>'
            . $this->escaper->escapeHtml(__('Fetch Magepow Feed Now')) . '</span></button>'
            . '<p class="note"><span>'
            . $this->escaper->escapeHtml(
                __('Pull remote feed immediately into System Notifications (bell icon).')
            )
            . '</span></p>';
    }
}
