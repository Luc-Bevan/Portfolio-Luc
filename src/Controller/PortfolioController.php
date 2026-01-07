<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PortfolioController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('portfolio/index.html.twig', [
            'page_title' => 'My Portfolio',
        ]);
    }
}
