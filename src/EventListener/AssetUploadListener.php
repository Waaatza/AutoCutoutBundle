<?php

namespace Watza\AutoCutoutBundle\EventListener;

use Pimcore\Event\Model\AssetEvent;
use Pimcore\Model\Asset\Image;
use Watza\AutoCutoutBundle\Service\AwardCutoutService;

class AssetUploadListener
{
    public function __construct(private AwardCutoutService $cutoutService) {}

    public function onPostAdd(AssetEvent $event): void
    {
        $asset = $event->getAsset();

        if (!$asset instanceof Image) {
            return;
        }

        // Nur Bilder im /Awards/ Ordner
        if (!str_starts_with($asset->getFullPath(), '/Awards/') ||
            str_contains($asset->getFullPath(), '/_freigestellt/')
        ) {
            return;
        }

        $this->cutoutService->removeBackground($asset);
    }
}
