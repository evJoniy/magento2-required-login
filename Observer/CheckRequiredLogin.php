<?php

namespace Zone\RequiredLogin\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\Message\ManagerInterface;
use Zone\RequiredLogin\Helper\Data;

class CheckRequiredLogin implements ObserverInterface
{
    public function __construct(
        private readonly Data $data,
        private readonly UrlInterface $url,
        private readonly ManagerInterface $messageManager,
        private readonly ResponseFactory $responseFactory
    ) {
    }

    public function execute(Observer $observer)
    {
        $isAuthAction = $this->data->isAuthAction();

        if ($this->data->isAdminLoggedInOrIsAdminAction() || $isAuthAction) {
            return $this;
        }

        $isCustomerLogin = $this->data->isCustomerLoggedIn();

        if (!$isCustomerLogin && $this->data->getGeneralConfig('enabled')) {
            if (!$isAuthAction) {
                $this->messageManager->addWarningMessage($this->data->getNotificationException('warning_message'));
                $redirectionUrl = $this->url->getUrl($this->data->getPageException('target_url_redirect'));
                $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
                exit;
            }

            return $this;
        }
    }
}
