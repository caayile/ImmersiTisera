<?php

namespace Tests\Unit;

use App\Services\CertificateSignatureStorage;
use PHPUnit\Framework\TestCase;

class CertificateSignatureDisplayTest extends TestCase
{
    public function test_display_signature_is_cropped_and_thicker_than_the_source(): void
    {
        $source = imagecreatetruecolor(200, 90);
        imagealphablending($source, false);
        imagesavealpha($source, true);
        $clear = imagecolorallocatealpha($source, 0, 0, 0, 127);
        imagefilledrectangle($source, 0, 0, 199, 89, $clear);
        $ink = imagecolorallocatealpha($source, 18, 38, 29, 0);
        imageline($source, 40, 45, 160, 45, $ink);
        ob_start();
        imagepng($source);
        $binary = ob_get_clean();
        imagedestroy($source);

        $display = (new CertificateSignatureStorage)->forDisplay('data:image/png;base64,'.base64_encode($binary));

        $this->assertIsString($display);
        $rendered = base64_decode(substr($display, strlen('data:image/png;base64,')), true);
        $this->assertNotFalse($rendered);
        $image = imagecreatefromstring($rendered);
        $this->assertNotFalse($image);
        $this->assertLessThan(90, imagesy($image));
        $this->assertGreaterThan($this->opaquePixels($binary), $this->opaquePixels($rendered));
        imagedestroy($image);
    }

    private function opaquePixels(string $binary): int
    {
        $image = imagecreatefromstring($binary);
        $count = 0;

        for ($y = 0; $y < imagesy($image); $y++) {
            for ($x = 0; $x < imagesx($image); $x++) {
                $rgba = imagecolorsforindex($image, imagecolorat($image, $x, $y));

                if ($rgba['alpha'] <= 40) {
                    $count++;
                }
            }
        }

        imagedestroy($image);

        return $count;
    }
}
