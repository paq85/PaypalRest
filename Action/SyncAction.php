<?php

namespace Payum\Paypal\Rest\Action;

use PayPal\Api\Payment as PaypalPayment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\Payment;
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

        $amount = null;
        $currency = null;
        $payPalPayment = PaypalPayment::get($model['id']);
        if ('approved' === $payPalPayment->getState()) {
            $transactions = $payPalPayment->getTransactions();
            foreach ($transactions as $transaction) {
                $amount = $transaction->getAmount()->getTotal();
                $currency = $transaction->getAmount()->getCurrency();
            }

            /* @var Payment $paymentModel */
            $paymentModel = $request->getFirstModel();
            $amountInCents = (int) str_replace('.', '', $amount);
            if ($paymentModel->getTotalAmount() !== $amountInCents) {
                $paymentModel->setTotalAmount($amountInCents);
            }
            if ($paymentModel->getCurrencyCode() !== $currency) {
                $paymentModel->setCurrencyCode($currency);
            }
        }

        $data = [
            'id'                => $payPalPayment->getId(),
            'create_time'       => $payPalPayment->getCreateTime(),
            'update_time'       => $payPalPayment->getUpdateTime(),
            'amount'            => $amount,
            'currency'          => $currency,
            'state'             => $payPalPayment->getState() // "approved" means it was OK
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
