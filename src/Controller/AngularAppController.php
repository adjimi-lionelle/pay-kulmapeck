<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AngularAppController extends AbstractController
{

    #[Route('/', name: 'app_angular', requirements: ['angular_route' => '^(?!api).*'])]   
   public function index()
    {
        return $this->render('angular_app/index.html.twig');
    }
}