<?php

namespace Watza\AutoCutoutBundle\Service;

use Pimcore\Model\Asset;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\Asset\Folder;

class AwardCutoutService
{
    public function removeBackground(Image $asset): void
    {
        if (str_contains($asset->getFullPath(), '/_freigestellt/')) {
            return;
        }

        $imagick = new \Imagick();
        $imagick->readImageBlob($asset->getData());
        $imagick->setImageColorspace(\Imagick::COLORSPACE_RGB);
        $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);

        // Hintergrundfarbe vom Rand (oben links)
        $pixel = $imagick->getImagePixelColor(0, 0);
        $bgColor = $pixel->getColor(); // ['r'=>..., 'g'=>..., 'b'=>...]

        // ------------------------------
        // 1️⃣ FloodFill von allen Ecken
        // ------------------------------
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
                0, // fuzz = 0, optional später anpassen
                new \ImagickPixel("rgb({$bgColor['r']},{$bgColor['g']},{$bgColor['b']})"),
                $point['x'],
                $point['y'],
                false
            );
        }

        // ------------------------------
        // 2️⃣ Trimmen nur der Ränder
        // ------------------------------
        $imagick->trimImage(0);
        $imagick->setImageFormat('png');

        // ------------------------------
        // 3️⃣ Zielordner
        // ------------------------------
        $targetFolderPath = '/Awards/_freigestellt';
        $targetFolder = Asset::getByPath($targetFolderPath);
        if (!$targetFolder instanceof Folder) {
            $targetFolder = new Folder();
            $targetFolder->setParent(Asset::getByPath('/Awards'));
            $targetFolder->setFilename('_freigestellt');
            $targetFolder->save();
        }

        // ------------------------------
        // 4️⃣ Neues Asset speichern
        // ------------------------------
        $new = new Image();
        $new->setParent($targetFolder);
        $new->setFilename(pathinfo($asset->getFilename(), PATHINFO_FILENAME) . '_freigestellt.png');
        $new->setData($imagick->getImageBlob());
        $new->save();
    }
}
