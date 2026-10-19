<?php

namespace App\Service;

use App\Entity\Billet;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;

final class TicketQrCodeGenerator
{
    public function generateDataUri(Billet $ticket): string
    {
        return (new Builder(new SvgWriter()))
            ->build(data: 'OLYMPIA:TICKET:'.$ticket->getJeton(), size: 280, margin: 12)
            ->getDataUri();
    }
}
