<?php

namespace Payum\Paypal\Rest\Action;

use PayPal\Api\Payment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var ArrayObject $model */
        $model = $request->getModel();

        if (isset($model['state']) && 'approved' == $model['state']) {
            $request->markCaptured();

            return;
        }

        if (isset($model['state']) && 'created' == $model['state']) {
            $request->markNew();

            return;
        }

        if (false == isset($model['state'])) {
            $request->markNew();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
