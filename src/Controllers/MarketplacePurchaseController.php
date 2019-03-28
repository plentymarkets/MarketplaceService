<?php

namespace MarketplaceService\Controllers;

use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Application;
use Plenty\Plugin\Controller;
use Plenty\Modules\Ticket\Models\Ticket;
use MarketplaceService\Services\TicketService;

/**
 * Class MarketplacePurchaseController
 * @package MarketplaceService\Controllers
 */
class MarketplacePurchaseController extends Controller
{
    /**
     * @param Request       $request
     * @param TicketService $ticketService
     * @return string
     */
    public function handlePurchase(Request $request, Response $response, TicketService $ticketService)
    {
        $ticketData  = $this->prepareTicketData($request->all());
        $messageData = $this->prepareMessageData($request->all());

        if (count($ticketData))
        {
            /** @var Ticket $ticket */
            $ticket = $ticketService->createTicket($ticketData);

            $ticketService->createTicketMessage($messageData, $ticket->id);

            return $response->make("Ticket created", 200);
        }
        return $response->make("Failed to create ticket.", 403);
    }

    /**
     * @param array $data
     * @return array
     */
    private function prepareTicketData(array $data)
    {
        $ticketDataOk = false;

        /** @var ConfigRepository $configRepository */
        $configRepository = pluginApp(ConfigRepository::class);

        $ticketTypeId      = (int)$configRepository->get('MarketplaceService.ticket.type.id');
        $ticketStatusId    = (int)$configRepository->get('MarketplaceService.ticket.status.id');

        $ticketRoleId      = (int)$configRepository->get('MarketplaceService.ticket.role.id');
        $ticketUserIdOne   = (int)$configRepository->get('MarketplaceService.ticket.user.id.one', 0);
        $ticketUserIdTwo   = (int)$configRepository->get('MarketplaceService.ticket.user.id.two', 0);
        $ticketUserIdThree = (int)$configRepository->get('MarketplaceService.ticket.user.id.three', 0);

        $ticketUserIds = [
            $ticketUserIdOne,
            $ticketUserIdTwo,
            $ticketUserIdThree
        ];

        if($ticketTypeId > 0 && $ticketStatusId > 0 && $ticketRoleId > 0 &&
            ($ticketUserIdOne > 0 || $ticketUserIdTwo > 0 || $ticketUserIdThree > 0 )) {
            $ticketDataOk = true;
        }

        if ($ticketDataOk && is_array($data) && count($data))
        {
            $owners = [];

            foreach ($ticketUserIds as $ticketUserId) {
                if($ticketUserId > 0) {
                   array_push($owners, [
                           "userId" => $ticketUserId,
                           "roleId" => $ticketRoleId
                       ]) ;
                }
            }

            return [
                "typeId" => $ticketTypeId,
                "statusId" => $ticketStatusId,
                "title" => 'Service: ' . $data['resource']['order']['orderItems'][0]['orderItemName'],
                "plentyId" => pluginApp(Application::class)->getPlentyId(),
                "source" => "frontend",
                "owners" => $owners
            ];
        }
        return [];
    }
    /**
     * @param array $data
     * @return array
     */
    private function prepareMessageData(array $data)
    {
        $message = '<strong>Service-Artikel:</strong> ' . $data['resource']['order']['orderItems'][0]['orderItemName'] .
            '<br/><br/><strong>Kunde:</strong> ' .
            '<p style="margin-left: 20px;"><strong>Name:</strong> (' . $data['resource']['contact']['gender'] . ') ' . $data['resource']['contact']['firstName'] . ' ' . $data['resource']['contact']['lastName'] .
            '<br/><strong>Email:</strong> ' . $data['resource']['contact']['email'] . '</p>' .
            '<br/><strong>Auftrag-Details:</strong>' .
            '<p style="margin-left: 20px;"><strong>Auftrags-Id:</strong> ' . $data['resource']['order']['id'] .
            '<br/><strong>PayPal-TransactionId:</strong> ' . $data['resource']['order']['payPalTransactionId'] . '</p>';

        foreach ($data['resource']['order']['orderItems'] as $orderItem){
            if($orderItem['itemVariationId'] > 0){
                $message = $message .
                    '<p style="margin-left: 20px;"><strong>Artikelname:</strong> ' . $orderItem['orderItemName'] .
                    '<br/><strong>Preis-Netto:</strong> ' . $orderItem['priceNet'] .
                    '<br/><strong>Preis-Brutto:</strong> ' . $orderItem['priceGross'] . '</p>';
            }
        }

        if (is_array($data) && count($data))
        {
            return [
                "type" => 'message',
                "text" => $message,
            ];
        }
        return [];
    }
}