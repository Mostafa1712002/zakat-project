<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRGdImagePNG;

class ZatcaQrService
{
    public function generateBase64Image(string $tlvBase64): string
    {
        $options = new QROptions();
        $options->outputInterface = QRGdImagePNG::class;
        $options->scale = 5;

        $qr = new QRCode($options);

        return $qr->render($tlvBase64);
    }
}
