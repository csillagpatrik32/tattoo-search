<?php
/**
 * Created by PhpStorm.
 * User: Catman
 * Date: 2018. 10. 21.
 * Time: 21:17
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {

        $html = $this->renderView('base.html.twig');

        return new Response($html);
    }
}