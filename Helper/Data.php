<?php

namespace Zone\RequiredLogin\Helper;

use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Request\Http;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    const XML_PATH_RequireLogin = 'requiredlogin/';

    public function __construct(
        private readonly Http $request,
        private readonly LoggerInterface $logger,
        private readonly CustomerSession $customerSession,
        private readonly AdminSession $adminSession,
        Context $context,
    ) {
        parent::__construct($context);
    }

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_RequireLogin . 'general/' . $code, $storeId);
    }

    public function getPageException($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_RequireLogin . 'pageexception/' . $code, $storeId);
    }

    public function getNotificationException($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_RequireLogin . 'notification/' . $code, $storeId);
    }

    public function getWhitelist(): array
    {
        $selectedWhitelist = $this->getPageException('select_whitelist');
        $defaultWhitelist = [
            'adminhtml_auth_login',
            'customer_account_login',
            'customer_account_logoutSuccess',
            'customer_account_index',
            'customer_account_forgotpassword',
            'customer_account_forgotpasswordpost',
            'customer_account_createPassword',
            'customer_account_resetpasswordpost',
            'customer_account_createpost',
            'customer_account_loginPost',
            'customer_section_load',
            'stripe_payments_admin_configure_webhooks',
            'stripe_payments_webhooks_index',
        ];

        if ($selectedWhitelist) {
            $selectedWhitelist = explode(",", $selectedWhitelist);

            foreach ($selectedWhitelist as $key => $whitelist) {
                if ($whitelist == 'no-route') {
                    $selectedWhitelist[$key] = 'cms_noroute_index';
                }
            }
            return array_merge($selectedWhitelist, $defaultWhitelist);
        }

        return $defaultWhitelist;
    }

    public function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    public function isAdminLoggedInOrIsAdminAction(): bool
    {
        return $this->adminSession->isLoggedIn() || str_starts_with($this->request->getOriginalPathInfo(), '/admin');
    }

    public function isAuthAction(): bool
    {
        $currentAction = $this->request->getFullActionName();

        if (in_array($currentAction, $this->getWhitelist())) {
            return true;
        }

        $this->logger->notice('Zone_RequiredLogin Blocked :' . $currentAction);

        return false;
    }
}
