<?php

namespace Revolter\IdealistBundle\Twig;

use Revolter\IdealistBundle\Utils\Markdown;

class AppExtension extends \Twig_Extension
{
    private $parser;

    public function __construct(Markdown $parser)
    {
        $this->parser = $parser;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter(
                'md2html',
                [$this, 'markdownToHtml'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'headlink',
                [$this, 'headingToAnchor'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'anchor',
                [$this, 'textToAnchor']
            ),
            new \Twig_SimpleFilter(
                'description',
                [$this, 'textToDescr']
            ),
        );
    }

    public function markdownToHtml($content)
    {
        return $this->parser->toHtml($content);
    }

    public function getAnchor($text)
    {
        $result = $text; // mb_convert_case($text, MB_CASE_LOWER);
        $result = preg_replace('/\s/', '-', $result);
        return $result;
    }

    public function textToAnchor($text)
    {
        return '#'.$this->getAnchor($text);
    }

    public function headingToAnchor($content)
    {
        //echo 'headingToAnchor'; exit;
        return preg_replace_callback(
            '/(<h1>)(.*?)(<\/h1>)/',
            function ($m) {
                $id = $this->getAnchor($m[2]);
                $name = mb_convert_case($id, MB_CASE_LOWER);
                return $m[1].'<a id="'.$id.'" name="'.$name.'" class="external-link" href="#'.$id.'"></a>'.$m[2].$m[3];
            },
            $content
        );
    }

    public function textToDescr($content)
    {
        
    }

    public function getName()
    {
        return 'app_extension';
    }
}
