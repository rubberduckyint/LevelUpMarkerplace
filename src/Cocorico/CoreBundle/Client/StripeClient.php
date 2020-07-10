<?php

namespace Cocorico\CoreBundle\Client;

use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Charge;
use Stripe\Error\Base;
use Stripe\Stripe;

class StripeClient
{
    private $config;
    private $em;
    private $logger;

    public function __construct($secretKey, array $config, EntityManagerInterface $em, LoggerInterface $logger)
    {
        \Stripe\Stripe::setApiKey($secretKey);
        $this->config = $config;
        $this->em = $em;
        $this->logger = $logger;
    }

    public function createCustomerCharge(User $user, $token, $amount)
    {

        try {
            $customer = $user->getStripeCustomerId();

            if($customer == NULL){
                $customer = \Stripe\Customer::create([
                    'source' => $token,
                    'email' => $user->getEmail(),
                ]);
                $user->setStripeCustomerId($customer->id);
                $customer = $customer->id;
                $this->em->flush();
            }

            $charge = \Stripe\Charge::create([
                'amount' => $amount,
                'currency' => $this->config['currency'],
                'customer' => $customer
            ]);

            return $charge->id;

        } catch (\Stripe\Error\Base $e) {
            $this->logger->error(sprintf('%s exception encountered when creating a premium payment: "%s"', get_class($e), $e->getMessage()), ['exception' => $e]);

            throw $e;
        }

    }

    public function createRefund($charge){

        $refund = \Stripe\Refund::create([
            'charge' => $charge
        ]);
        return $refund;
    }
}