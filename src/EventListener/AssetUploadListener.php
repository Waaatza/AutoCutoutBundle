<?php

namespace Watza\AutoCutoutBundle\EventListener;

use Pimcore\Event\Model\AssetEvent;
use Pimcore\Model\Asset;
use Watza\AutoCutoutBundle\Service\AwardCutoutService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class AssetUploadListener
{
    public function __construct(
        private AwardCutoutService $cutoutService
    ) {}

    public function onPostAdd(AssetEvent $event): void
    {
        $asset = $event->getAsset();

        if (!$asset instanceof Asset\Image) {
            return;
        }

        if (!str_starts_with($asset->getFullPath(), '/Awards/')) {
            return;
        }

        $this->cutoutService->removeBackground($asset);
    }

}
