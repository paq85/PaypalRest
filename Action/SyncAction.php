<?php

namespace Payum\Paypal\Rest\Action;

use PayPal\Api\Payment as PaypalPayment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Sync;

class SyncAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Sync */
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var ArrayObject $model */
        $model = $request->getModel();

        $payPalPayment = PaypalPayment::get($model['id']);
        $data = [
            'id' => $payPalPayment->getId(),
            'create_time'   => $payPalPayment->getCreateTime(),
            'update_time'   => $payPalPayment->getUpdateTime(),
            'state'         => $payPalPayment->getState() // "approved" means it was OK
        ];
        $model->replace($data);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Sync &&
            $request->getModel() instanceof \Payum\Core\Bridge\Spl\ArrayObject
            ;
    }
}
