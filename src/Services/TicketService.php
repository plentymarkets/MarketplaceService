<?php

namespace MarketplaceService\Services;

use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Ticket\Contracts\TicketRepositoryContract;

/**
 * Class TicketService
 * @package MarketplaceService\Services
 */
class TicketService
{
    /** @var TicketRepositoryContract $ticketRepository */
    private $ticketRepository;

    /** @var AuthHelper $authHelper */
    private $authHelper;

    /**
     * TicketService constructor.
     * @param TicketRepositoryContract $ticketRepository
     * @param AuthHelper               $authHelper
     */
    public function __construct(TicketRepositoryContract $ticketRepository, AuthHelper $authHelper)
    {
        $this->authHelper       = $authHelper;
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * @param array $data
     * @return \Plenty\Modules\Ticket\Models\Ticket
     */
    public function createTicket(array $data)
    {
        $ticket = $this->ticketRepository;

        return $this->authHelper->processUnguarded(function () use ($ticket, $data)
        {
            return $ticket->createTicket($data);
        });
    }

    /**
     * @param array $data
     * @param int $ticketId
     * @return mixed
     */
    public function createTicketMessage(array $data, int $ticketId)
    {
        $ticket = $this->ticketRepository;

        return $this->authHelper->processUnguarded(function () use ($ticket, $ticketId, $data)
        {
            return $ticket->createMessage($data, $ticketId);
        });
    }
}