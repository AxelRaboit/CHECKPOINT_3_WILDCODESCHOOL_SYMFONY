<?php

namespace App\Controller;

use App\Entity\Tile;
use App\Service\MapManager;
use App\Repository\BoatRepository;
use App\Repository\TileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MapController extends AbstractController
{
    /**
     * @Route("/map", name="map")
     */
    public function displayMap(BoatRepository $boatRepository, mapManager $mapManager): Response
    {
        $em = $this->getDoctrine()->getManager();
        $tiles = $em->getRepository(Tile::class)->findAll();

        foreach ($tiles as $tile) {
            $map[$tile->getCoordX()][$tile->getCoordY()] = $tile;
        }

        $boat = $boatRepository->findOneBy([]);

        if ($mapManager->checkTreasure($boat)) {
            $this->addFlash(
                'success',
                'You found the treasure !'
            );
        };

        return $this->render('map/index.html.twig', [
            'map'  => $map ?? [],
            'boat' => $boat,
        ]);
    }

    /**
     * @Route("/start" , name="start")
     * @return Response
     */
    public function start(
        BoatRepository $boatRepository, 
        MapManager $mapManager, 
        TileRepository $tileRepository, 
        EntityManagerInterface $em
        ): Response
    {
        $boat = $boatRepository->findOneBy([]);
        $boat->setCoordX(0);
        $boat->setCoordY(0);

        $previousTreasure = $tileRepository->findOneBy([
            'hasTreasure' => true
        ]);

        if ($previousTreasure != null) {
            $previousTreasure->setHasTreasure(false);
        }

        $randomIsland = $mapManager->getRandomIsland();
        $randomIsland->setHasTreasure(true);

        $em->persist($boat);
        $em->flush();

        return $this->redirectToRoute('map');
    }
}
