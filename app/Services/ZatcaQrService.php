<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class ZatcaQrService
{
    public function generateBase64Image(string $tlvBase64): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 5,
            'imageBase64' => false,
        ]);

        $qr = new QRCode($options);
        $imageData = $qr->render($tlvBase64);

        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    public function generateFile(string $tlvBase64, string $path): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 5,
            'imageBase64' => false,
        ]);

        $qr = new QRCode($options);
        $imageData = $qr->render($tlvBase64);

        file_put_contents($path, $imageData);

        return $path;
    }
}
