<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function getText($dir = '')
    {
        $result = '';
        $file = $this->getParameter('index_file').'.md';
        $path = $this->getParameter('path.web') . $dir . $file; 
        if (file_exists($path)) {
            $content = file_get_contents($path);
            if ($content) {
                $result = $content;
            }
        }
        return $result;
    }
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('default/rev.html.twig', [
            'content' => $this->getText('/'),
        ]);
    }
    
    /**
     * @Route("/rev", name="revpage")
     */
    public function revAction(Request $request)
    {
        $content = $this->getText('/');
        return $this->render('default/rev.html.twig', [
            'content' => $content,
        ]);
    }
}
