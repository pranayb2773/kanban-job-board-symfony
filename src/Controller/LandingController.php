<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class LandingController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('landing/index.html.twig');
    }
}
