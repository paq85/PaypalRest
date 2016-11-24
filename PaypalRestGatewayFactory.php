<?php
namespace Payum\Paypal\Rest;

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Paypal\Rest\Action\CaptureAction;
use Payum\Paypal\Rest\Action\StatusAction;
use Payum\Paypal\Rest\Action\SyncAction;
use Payum\Core\Exception\InvalidArgumentException;

class PaypalRestGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        if (false == class_exists(ApiContext::class)) {
            throw new \LogicException('You must install "paypal/rest-api-sdk-php" library.');
        }

        $config->defaults([
            'payum.factory_name' => 'paypal_rest',
            'payum.factory_title' => 'PayPal Rest',

            'payum.action.capture' => new CaptureAction(),
            'payum.action.sync' => new SyncAction(),
            'payum.action.status' => new StatusAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'client_id' => '',
                'client_secret' => '',
                'mode' => 'sandbox' // or 'live'
            ];
            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = ['client_id', 'client_secret', 'mode'];
            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $credential = new OAuthTokenCredential($config['client_id'], $config['client_secret']);
                \PayPal\Common\PayPalModel::setCredential($credential);
                $configManager = \PayPal\Core\PayPalConfigManager::getInstance();
                $iniConfig = [
                    'http.ConnectionTimeOut' => 30,
                    'http.Retry' => 1,
                    'mode' => $config['sandbox'],
                    'log.LogEnabled' => '' // empty string means disabled
                ];
                $configManager->addConfigs($iniConfig);

                return new ApiContext($credential);
            };
        }
    }
}
