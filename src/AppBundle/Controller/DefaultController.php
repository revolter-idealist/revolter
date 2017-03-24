<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function getIdea($idea)
    {
        $result = $this->getParameter('path.idea');
        if ($idea) {
            $result = $this->getParameter('path.texts') . '/' . $idea;
        }
        return $result. '/';
    }
    
    public function getFile($file)
    {
        $result = $this->getParameter('index_file');
        $default = $this->getParameter('index_name');
        if ($file and $file != $default) { // $this->getParameter('path.deal') . '/' . 
            $result = $file;
        }
        $ext = pathinfo($result, PATHINFO_EXTENSION);
        if (!$ext) {
			$result .= '.md';
		}
        return $result;
    }
    
    public function getText($idea = null, $file = null)
    {
        $result = '';   
        $path = $this->getIdea($idea).$this->getFile($file);   
        if (file_exists($path)) { 
            $content = file_get_contents($path);
            if ($content) {
                $result = $content;
            }
        } // echo $path ; exit;
        return $result;
    }
    /**
     * @Route("/", name="index")
     */
    public function indexAction(Request $request)
    {
        return $this->redirectToRoute('file', [
            'idea' => $this->getParameter('index_idea'),
            'file' => $this->getParameter('index_name'),
        ], 301);
        return $this->render('AppBundle::index.html.twig', [
            'content' => $this->getText(),
        ]);
    }
    
    /**
     * @Route("/{idea}/", name="idea")
     */
    public function ideaAction($idea)
    {
        return $this->redirectToRoute('file', [
            'idea' => $idea,
            'file' => $this->getParameter('index_name')
        ], 301);
    }
    
    public function checkTranslate($caption)
    {
        $result = false;   
        $lng = $this->get('translator')->getCatalogue()->all('messages');
        if (in_array($caption, $lng)) {
            $lng = array_flip($lng);
            $result = $lng[$caption];  
        }
        return $result;
    }
    
    public function getLinks($content)
    {
        $result = [];  
        if (preg_match_all('/^\#{1} (.*)$/m', $content, $matches)) { 
            $result = $matches[1];
        }
        return $result;
    }
    
    public function getMaintain()
    {
        $result = ''; 
        $idea = $this->getParameter('index_idea');
        $path = $this->getIdea($idea).'MAINTAIN.md';
        if (file_exists($path)) { 
            $result = file_get_contents($path);
        }
        return $result;
    }
    
    public function getTitle($content)
    {
        $str = substr($content, 0, strpos($content, "\n"));
        $str = preg_replace("~[\#*()\[\]_\~`|=]*~", '', $str);
        return substr($str, 0, 80);
    }
    
    public function getPages($content)
    {
        $result = []; //echo $content ; exit;
        preg_match_all('/(^|\n)(# .*?)(?=$|\n# )/s', $content, $parts);
        // print_r($parts); exit;
        if (count($parts[2])) {
            //$position = $parts[0][1][1];
            //$page_break = '<div class="more"></div>'."\n"; 
            $result = $parts[2]; //echo $position ; exit;
        } else {
            $result[0] = $content;
        } //print_r($result); exit;
        return $result;
    }
    
    /**
     * @Route("/{idea}/print", name="print")
     */
    public function printAction($idea)
    {
        $content = $this->getText($idea, $this->getParameter('index_name')); 
        if (!$content) {
            throw $this->createNotFoundException('Страница не найдена');
        }
        return $this->render('AppBundle::print.html.twig', [
            'title'    => $this->getTitle($content),
            'links'    => $this->getLinks($content),
            'pages'    => $this->getPages($content),
            'maintain' => $this->getMaintain(),
        ]);
    }
    
    /**
     * @Route("/{idea}/{file}", name="file", requirements={
     *      "file"=".+"
     * })) 
     * 
     */
    public function fileAction($idea, $file)
    {
        $content = $this->getText($idea, $file); 
        if (!$content) {
            if ($caption = $this->checkTranslate($idea)) {
                return $this->redirectToRoute('file', [
                    'idea' => $caption,
                    'file' => $file
                ], 301); 
            }
            throw $this->createNotFoundException('Страница не найдена');
        } 
        $template = $file == 'index' ? 'index' : 'texts';
        return $this->render('AppBundle::'.$template.'.html.twig', [
            'title'    => $this->getTitle($content),
            'links'    => $this->getLinks($content),
            'pages'    => $this->getPages($content),
            'maintain' => $this->getMaintain(),
        ]);
    }
}
