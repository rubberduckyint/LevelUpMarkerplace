<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Model\Manager;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\BookingStripe;
use Cocorico\CoreBundle\Event\BookingBankWireEvent;
use Cocorico\CoreBundle\Event\BookingBankWireEvents;
use Cocorico\CoreBundle\Mailer\TwigSwiftMailer;
use Cocorico\CoreBundle\Repository\BookingBankWireRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use function PHPSTORM_META\type;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BookingStripeManager extends BaseManager
{

    protected $em;

    public function __construct(
        EntityManager $em
    ) {
        $this->em = $em;

    }

    /**
     * Create a new stripe charge
     *
     * @param Booking $booking
     * @return BookingStripe
     */
    public function create(Booking $booking, $charge)
    {
        $offerer = $booking->getListing()->getUser();
        $amountToPayToOfferer = $booking->getAmountToPayToOfferer();

        $stripeCharge = new BookingStripe();
        $stripeCharge->setBooking($booking);
        $stripeCharge->setAmount($amountToPayToOfferer);
        $stripeCharge->setUser($offerer);
        $stripeCharge->setChargeId($charge);


        $this->persistAndFlush($stripeCharge);

        return $stripeCharge;
    }

}