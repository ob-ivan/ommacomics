<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ComicsController extends Controller {
    /**
     * @Route("/")
     */
    public function main() {
        return $this->render('comics/main.html.twig');
    }
}
