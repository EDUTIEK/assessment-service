<?php

namespace Edutiek\AssessmentService\EssayTask\HtmlProcessing;

use DOMDocument;
use Edutiek\AssessmentService\EssayTask\Comments\Service as CommentsService;
use Edutiek\AssessmentService\EssayTask\Data\HeadlineScheme;
use Mustache_Engine;
use Edutiek\AssessmentService\EssayTask\Data\CorrectionSettings;
use Edutiek\AssessmentService\Task\Data\CorrectorComment;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\WritingSettings;

/**
 * Tool for processing HTML code coming from the rich text editor
 */
class Service implements FullService
{
    const COLOR_NORMAL = '#D8E5F4';
    const COLOR_EXCELLENT = '#E3EFDD';
    const COLOR_CARDINAL = '#FBDED1';

    static int $paraCounter = 0;
    static int $wordCounter = 0;
    static int $h1Counter = 0;
    static int $h2Counter = 0;
    static int $h3Counter = 0;
    static int $h4Counter = 0;
    static int $h5Counter = 0;
    static int $h6Counter = 0;

    static CommentsService $comments_service;

    static ?WritingSettings $writingSettings = null;
    static ?CorrectionSettings $correctionSettings = null;
    static bool $forPdf = false;

    /**
     * All Comments that should be merged
     * @var CorrectorComment[]
     */
    static array $allComments = [];

    /**
     * Comments for the current paragraph
     * @var CorrectorComment[]
     */
    static array $currentComments = [];


    public function __construct(
        CommentsService $comments_service
    ){
        self::$comments_service = $comments_service;
    }

    public function fillTemplate(string $template, array $data): string
    {
        $mustache = new Mustache_Engine(array('entity_flags' => ENT_QUOTES));
        $template = file_get_contents($template);
        return $mustache->render($template, $data);
    }

    public function processWrittenText(?Essay $essay, WritingSettings $settings, bool $forPdf = false) : string
    {
        self::$writingSettings = $settings;
        self::$forPdf = $forPdf;
        
        self::initParaCounter();
        self::initWordCounter();
        self::initHeadlineCounters();
        
        $html = $essay ? ($essay->getWrittenText() ?? '') : '';

        // remove ascii control characters except tab, cr and lf
        $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $html);

        // don't process an empty text
        $html = trim($html);
        if (empty($html)) {
            return '';
        }

        $html = $this->processXslt($html, __DIR__ . '/xsl/cleanup.xsl',
            $essay ? $essay->getServiceVersion() : 0);
        $html = $this->processXslt($html, __DIR__ . '/xsl/numbers.xsl',
            $essay ? $essay->getServiceVersion() : 0,
            $settings->getAddParagraphNumbers(), $forPdf);

        return $this->getStyles() . "\n" . $html;
    }

    public function processCommentsForPdf(?Essay $essay, WritingSettings $writingSettings, CorrectionSettings $correctionSettings, array $comments) : string
    {
        self::$writingSettings = $writingSettings;
        self::$correctionSettings = $correctionSettings;
        self::$allComments = $comments;
        self::$currentComments = [];
        self::$forPdf = true;
        
        $html = $this->processWrittenText($essay, $writingSettings, true);
        
        $html = preg_replace('/<w-p w="([0-9]+)" p="([0-9]+)">/','<span data-w="$1" data-p="$2">', $html);
        $html = str_replace('xlas-table','table', $html);
        $html = str_replace('xlas-tr','tr', $html);
        $html = str_replace('xlas-td','td', $html);
        $html = str_replace('</w-p>','</span>', $html);
        $html = $this->processXslt($html, __DIR__ . '/xsl/pdf_comments.xsl',
            $essay ? $essay->getServiceVersion() : 0,
            $writingSettings->getAddParagraphNumbers());

        return $this->getStyles() . "\n" . $html;
    }


    /**
     * Get styles to be added to the HTML
     * @param WritingSettings $settings
     * @return void
     */
    protected function getStyles() : string
    {
        if (self::$forPdf) {
            $styles = file_get_contents(__DIR__ . '/styles/plain_style.html');
            if (self::$writingSettings->getHeadlineScheme() == 'three') {
                $styles .= "\n" . file_get_contents(__DIR__ . '/styles/headlines-three.html');
            }
            return $styles;
        }
        return '';
    }


    /**
     * Get the XSLt Processor for an XSL file
     * The process_version is a number which can be increased with a new version of the processing
     * This number is provided as a parameter to the XSLT processing
     */
    protected function processXslt(string $html, string $xslt_file, int $service_version, bool $add_paragraph_numbers = false, bool $for_pdf = false) : string
    {
        try {
            // get the xslt document
            // set the URI to allow document() within the XSL file
            $xslt_doc = new \DOMDocument('1.0', 'UTF-8');
            $xslt_doc->loadXML(file_get_contents($xslt_file));
            $xslt_doc->documentURI = $xslt_file;

            // get the xslt processor
            $xslt = new \XSLTProcessor();
            $xslt->registerPhpFunctions();
            $xslt->importStyleSheet($xslt_doc);
            $xslt->setParameter('', 'service_version', $service_version);
            $xslt->setParameter('', 'add_paragraph_numbers', (int) $add_paragraph_numbers);
            $xslt->setParameter('', 'for_pdf', (int) $for_pdf);

            // get the html document
            $dom_doc = new \DOMDocument('1.0', 'UTF-8');
            $dom_doc->loadHTML('<?xml encoding="UTF-8"?'.'>'. $html);

            //$xml = $xslt->transformToXml($dom_doc);
            $result = $xslt->transformToDoc($dom_doc);
            $xml= $result->saveHTML();
            
            $xml = preg_replace('/<\?xml.*\?>/', '', $xml);
            $xml = str_replace( ' xmlns:php="http://php.net/xsl"', '', $xml);

            return $xml;
        }
        catch (\Throwable $e) {
            return 'HTML PROCESSING ERROR:<br>' . $e->getMessage() . '<hr>' . $html;
        }
    }

    /**
     * Get the paragraph counter tag for PDF generation
     * This should help for a correct vertical alignment with the counted block in TCPDF
     */
    static function paraCounterTag($tag) : string
    {
        if (self::$forPdf) {
            switch ($tag) {
                case 'pre':
                case 'ol':
                case 'ul':
                case 'li':
                case 'p':
                    return 'div';
                default:
                    return $tag;
            }
        }
        else {
            return 'p';
        }
    }


    static function initParaCounter(): void
    {
        self::$paraCounter = 0;
    }

    static function currentParaCounter(): string
    {
        return self::$paraCounter;
    }

    static function nextParaCounter(): string
    {
        self::$paraCounter++;
        return self::$paraCounter;
    }

    static function initWordCounter(): void
    {
        self::$wordCounter = 0;
    }

    static function currentWordCounter(): string
    {
        return self::$wordCounter;
    }

    static function nextWordCounter(): string
    {
        self::$wordCounter++;
        return self::$wordCounter;
    }
    
    static function initHeadlineCounters(): void
    {
        self::$h1Counter = 0;
        self::$h2Counter = 0;
        self::$h3Counter = 0;
        self::$h4Counter = 0;
        self::$h5Counter = 0;
        self::$h6Counter = 0;
    }
    
    static function nextHeadlinePrefix($tag): string
    {
        switch ($tag) {
            case 'h1':
                self::$h1Counter += 1;
                self::$h2Counter = 0;
                self::$h3Counter = 0;
                self::$h4Counter = 0;
                self::$h5Counter = 0;
                self::$h6Counter = 0;
                break;
                
            case 'h2':
                self::$h2Counter += 1;
                self::$h3Counter = 0;
                self::$h4Counter = 0;
                self::$h5Counter = 0;
                self::$h6Counter = 0;
                break;

            case 'h3':
                self::$h3Counter += 1;
                self::$h4Counter = 0;
                self::$h5Counter = 0;
                self::$h6Counter = 0;
                break;

            case 'h4':
                self::$h4Counter += 1;
                self::$h5Counter = 0;
                self::$h6Counter = 0;
                break;
                
            case 'h5':
                self::$h5Counter += 1;
                self::$h6Counter = 0;
                break;

            case 'h6':
                self::$h6Counter += 1;
                break;
        }
        
        switch (self::$writingSettings->getHeadlineScheme()) {
            
            case HeadlineScheme::NUMERIC->value:
                switch ($tag) {
                    case 'h1':
                        return self::$h1Counter . ' ';
                    case 'h2':
                        return self::$h1Counter . '.' . self::$h2Counter  . ' ';
                    case 'h3':
                        return self::$h1Counter . '.' . self::$h2Counter . '.' . self::$h3Counter  . ' ';
                    case 'h4':
                        return self::$h1Counter . '.' . self::$h2Counter . '.' . self::$h3Counter  . '.' . self::$h4Counter  . ' ';
                    case 'h5':
                        return self::$h1Counter . '.' . self::$h2Counter . '.' . self::$h3Counter  . '.' . self::$h4Counter . '.' . self::$h5Counter  . ' ';
                    case 'h6':
                        return self::$h1Counter . '.' . self::$h2Counter . '.' . self::$h3Counter  . '.' . self::$h4Counter . '.' . self::$h5Counter  . '.' . self::$h6Counter  . ' ';
                }

            case HeadlineScheme::EDUTIEK->value:
                switch ($tag) {
                    case 'h1':
                        return self::toLatin(self::$h1Counter, true) . '. ';
                    case 'h2':
                        return self::toRoman(self::$h2Counter) . '. ';
                    case 'h3':
                        return self::$h3Counter  . '. ';
                    case 'h4':
                        return self::toLatin(self::$h4Counter) . '. ';
                    case 'h5':
                        return self::toLatin(self::$h5Counter) . self::toLatin(self::$h5Counter) . '. ';
                    case 'h6':
                        return '(' . self::$h6Counter  . ') ';
                }
        }
        
        
        return '';
    }

    /**
     * Get a latin character representation of a number
     */
    static function toLatin(int $num, $upper = false) : string
    {
        if ($num == 0) {
            return '0';
        }
        $num = $num - 1;
        $text= '';
        
        do {
            $char = substr('abcdefghijklmnopqrstuvwxyz', $num % 26, 1);
            $text = ($upper ? ucfirst($char) : $char) . $text;
            $num = intdiv($num, 26);
        }
        while ($num > 0);
        
        return $text;
    }

    /**
     * Get a roman letter representation of a number
     */
    static function  toRoman(int $num) : string
    {
        if ($num == 0) {
            return '0';
        }
        $text = '';
        
        $steps = [
            'M'  => 1000,
            'CM' => 900,
            'D'  => 500,
            'CD' => 400,
            'C'  => 100,
            'XC' => 90,
            'L'  => 50,
            'XL' => 40,
            'X'  => 10,
            'IX' => 9,
            'V'  => 5,
            'IV' => 4,
            'I'  => 1
        ];

        foreach ($steps as $sign => $step) {
            $repeat = intdiv($num, $step);
            $text .= str_repeat($sign, $repeat);
            $num = $num % $step;
        }
        
        return $text;
    }


    /**
     * Split a text into single words
     * Single spaces are added to the last word
     * Multiple spaces are treated as separate words
     * @param $text
     * @return \DOMElement
     * @throws \DOMException
     */
    static function splitWords($text): \DOMElement
    {
       $words = preg_split("/([\s]+)/", $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

       $doc = new DOMDocument;
       $root = $doc->createElement("root");

       $current = '';
       foreach ($words as $word) {
           if ($word == ' ' && (trim($current) == $current || trim($current) == '')) {
               // append a space to the last word if it is pure text or pure space
               // (don't add if a text space is already added to the last word)
               $current .= $word;
           }
           else {
               if ($current != '') {
                   $root->appendChild(new \DOMText($current));
               }
               $current = $word;
           }
       }
        if ($current != '') {
            $root->appendChild(new \DOMText($current));
        }

        return $root;
    }

    /**
     * Initialize the collection of comments for the current paragraph
     */
    static function initCurrentComments(string $paraNumber) 
    {
        self::$currentComments = self::$comments_service->getSortedCommentsOfParent(self::$allComments, (int) $paraNumber);
    }

    /**
     * Get a label if a comment starts at the given word
     */
    static function commentLabel(string $wordNumber) : string
    {
        $labels = [];
        foreach(self::$currentComments as $comment) {
            if ((int) $wordNumber == $comment->getStartPosition() && !empty($comment->getLabel())) {
                $labels[] = $comment->getLabel();
            }
        }
        return(implode(',', $labels));
    }

    /**
     * Get the background color for the word
     */
    static function commentColor(string $wordNumber) : string
    {
        $comments = [];
        foreach(self::$currentComments as $comment) {
            if ((int) $wordNumber >= $comment->getStartPosition() && (int) $wordNumber <= $comment->getEndPosition()) {
                $comments[] = $comment;
            }
        }
        return self::$comments_service->getTextBackgroundColor($comments);
    }

    /**
     * Get the comments for the current paragraph
     * @return \DOMElement
     * @throws \DOMException
     */
    static function getCurrentComments(): \DOMElement 
    {
        $html = self::$comments_service->getCommentsHtml(self::$currentComments, self::$correctionSettings);

        $doc = new DOMDocument;
        $doc->loadXML('<root xml:id="root">' . $html . '</root>');
        return $doc->getElementById('root');
    }
}
