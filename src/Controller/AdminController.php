<?php

namespace Watza\AutoCutoutBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Pimcore\Model\Asset;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Watza\AutoCutoutBundle\Service\AwardCutoutService;

class AdminController extends AbstractController
{
    public function remakeAction(Request $request, AwardCutoutService $service): JsonResponse
    {
        $id = $request->get('id');
        $fuzz = (float)$request->get('fuzz', 0.15);

        $asset = Asset::getById($id);
        if (!$asset instanceof Asset\Image) {
            return new JsonResponse(['success' => false, 'message' => "Kein Bild"]);
        }

        // Fuzz als Custom Property speichern
        $asset->setProperty('cutout_fuzz', $fuzz, 'watza_autocutout');
        $asset->save();

        // Bild erneut freistellen
        $service->removeBackground($asset);

        return new JsonResponse(['success' => true]);
    }
}
