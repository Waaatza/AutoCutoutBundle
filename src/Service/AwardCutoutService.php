<?php

namespace Watza\AutoCutoutBundle\Service;

use Pimcore\Model\Asset;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\Asset\Folder;

class AwardCutoutService
{
    private float $defaultFuzz = 0.15;

    public function __construct(float $defaultFuzz = 0.15)
    {
        $this->defaultFuzz = $defaultFuzz;
    }

    public function removeBackground(Image $asset): void
    {
        if (str_contains($asset->getFullPath(), '/_freigestellt/')) {
            return;
        }

        // Fuzz aus Custom Property oder Standardwert
        // Property abrufen
        $prop = $asset->getProperty('cutout_fuzz', 'watza_autocutout');

        if ($prop instanceof \Pimcore\Model\Property) {
            $fuzz = $prop->getData();
        } else {
            // direkter Wert oder null
            $fuzz = $prop;
        }

        $fuzz = $fuzz ?? $this->defaultFuzz;
        $fuzz = (float)$fuzz;

        $fuzz = $fuzz * \Imagick::getQuantum();
        $imagick = new \Imagick();
        $imagick->readImageBlob($asset->getData());
        $imagick->setImageColorspace(\Imagick::COLORSPACE_RGB);
        $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);

        $pixel = $imagick->getImagePixelColor(0, 0);
        $bgColor = $pixel->getColor();

        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();

        foreach ([
                     ['x' => 0, 'y' => 0],
                     ['x' => $width-1, 'y' => 0],
                     ['x' => 0, 'y' => $height-1],
                     ['x' => $width-1, 'y' => $height-1]
                 ] as $point) {
            $imagick->floodFillPaintImage(
                new \ImagickPixel("transparent"),
                $fuzz,
                new \ImagickPixel("rgb({$bgColor['r']},{$bgColor['g']},{$bgColor['b']})"),
                $point['x'],
                $point['y'],
                false
            );
        }

        $imagick->trimImage(0);
        $imagick->setImageFormat('png');

        $targetFolderPath = '/Awards/_freigestellt';
        $targetFolder = Asset::getByPath($targetFolderPath);
        if (!$targetFolder instanceof Folder) {
            $targetFolder = new Folder();
            $targetFolder->setParent(Asset::getByPath('/Awards'));
            $targetFolder->setFilename('_freigestellt');
            $targetFolder->save();
        }

        $new = new Image();
        $new->setParent($targetFolder);
        $new->setFilename(pathinfo($asset->getFilename(), PATHINFO_FILENAME) . '_freigestellt.png');
        $new->setData($imagick->getImageBlob());
        $new->save();
    }

    public function generatePreview(Image $asset, float $fuzz): string
    {
        $imagick = new \Imagick();
        $imagick->readImageBlob($asset->getData());
        $imagick->setImageColorspace(\Imagick::COLORSPACE_RGB);
        $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);

        $fuzz = $fuzz * \Imagick::getQuantum();

        $pixel = $imagick->getImagePixelColor(0, 0);
        $bgColor = $pixel->getColor();

        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();

        foreach ([
                     ['x' => 0, 'y' => 0],
                     ['x' => $width-1, 'y' => 0],
                     ['x' => 0, 'y' => $height-1],
                     ['x' => $width-1, 'y' => $height-1]
                 ] as $point) {
            $imagick->floodFillPaintImage(
                new \ImagickPixel("transparent"),
                $fuzz,
                new \ImagickPixel("rgb({$bgColor['r']},{$bgColor['g']},{$bgColor['b']})"),
                $point['x'],
                $point['y'],
                false
            );
        }

        $imagick->trimImage(0);
        $imagick->setImageFormat('png');

        return base64_encode($imagick->getImageBlob());
    }
}
