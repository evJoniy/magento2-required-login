<?php

namespace Zone\RequiredLogin\Plugin;

use Magento\Customer\Controller\Account\LoginPost;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AfterLoginPlugin
{
    const CONFIG_REDIRECT_DASHBOARD = 'customer/startup/redirect_dashboard';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function afterExecute($resultRedirect)
    {
        if (!$this->scopeConfig->getValue(self::CONFIG_REDIRECT_DASHBOARD)) {
            $resultRedirect->setPath('/');
        }

        return $resultRedirect;
    }
}
