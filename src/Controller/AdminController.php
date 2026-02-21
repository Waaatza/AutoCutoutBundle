<?php

namespace Watza\AutoCutoutBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Pimcore\Model\Asset;
use Pimcore\Model\Asset\Image;
use Watza\AutoCutoutBundle\Service\AwardCutoutService;

class AdminController extends AbstractController
{
    #[Route('/admin/watza/autocutout/remake', name: 'watza_autocutout_remake', methods: ['POST'])]
    public function remakeAction
    (Request $request, AwardCutoutService $service): JsonResponse
    {
        $id = $request->get('id');
        $fuzz = (float)$request->get('fuzz', 0.15);

        $asset = Asset::getById($id);

        if (!$asset instanceof Image) {
            return $this->json(['success' => false, 'message' => 'Kein Bild']);
        }

        $asset->setProperty('cutout_fuzz', $fuzz, 'watza_autocutout');
        $asset->save();

        $service->removeBackground($asset);

        return $this->json(['success' => true]);
    }

    #[Route('/admin/watza/autocutout/preview', name: 'watza_autocutout_preview', methods: ['POST'])]
    public function previewAction(Request $request, AwardCutoutService $service): JsonResponse
    {
        $asset = Asset::getById((int)$request->get('id'));

        if (!$asset instanceof Image) {
            return $this->json(['success' => false, 'message' => 'Kein Bild']);
        }

        $fuzz = (float)$request->get('fuzz', 0.15);

        $preview = $service->generatePreview($asset, $fuzz);

        return $this->json([
            'success' => true,
            'image' => 'data:image/png;base64,' . $preview
        ]);
    }
}
