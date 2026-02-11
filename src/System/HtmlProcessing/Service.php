<?php

namespace Edutiek\AssessmentService\System\HtmlProcessing;

use DOMDocument;
use Edutiek\AssessmentService\System\Data\HeadlineScheme;
use Mustache_Engine;

/**
 * Tool for processing HTML code coming from the rich text editor
 */
class Service implements FullService
{
    private static array $allowedStyles = [
        'background-color',
        'color',
        'text-align',
        'padding-left'
    ];

    public static int $paraCounter = 0;
    public static int $wordCounter = 0;
    public static int $h1Counter = 0;
    public static int $h2Counter = 0;
    public static int $h3Counter = 0;
    public static int $h4Counter = 0;
    public static int $h5Counter = 0;
    public static int $h6Counter = 0;

    public static $headlineScheme = HeadlineScheme::NUMERIC;

    public function fillTemplate(string $template, array $data): string
    {
        $mustache = new Mustache_Engine(array('entity_flags' => ENT_QUOTES));
        $template = file_get_contents($template);
        return $mustache->render($template, $data);
    }

    public function secureContent(string $html): string
    {
        $html = $this->processXslt($html, __DIR__ . '/xsl/secure.xsl', 0);
        return $html;
    }

    public function getContentForMarking(string $html, bool $add_paragraph_numbers, HeadlineScheme $headline_scheme): string
    {
        $html = $this->processXslt($html, __DIR__ . '/xsl/secure.xsl', 0);
        $html = $this->processXslt($html, __DIR__ . '/xsl/numbers.xsl', 0,
            $add_paragraph_numbers,
            $headline_scheme
        );
        return $html;
    }

    public function getContentForPdf(string $html, bool $add_paragraph_numbers, HeadlineScheme $headline_scheme): string
    {
        $html = $this->processXslt($html, __DIR__ . '/xsl/secure.xsl', 0);
        $html = $this->getContentForMarking($html, $add_paragraph_numbers, $headline_scheme);
        $html = $this->getContentStyles($add_paragraph_numbers, $headline_scheme) . $this->removeCustomMarkup($html);

        echo $html;
        exit;

        return $html;
    }

    public function getContentStyles(bool $add_paragraph_numbers, HeadlineScheme $headline_scheme): string
    {
        $styles = file_get_contents(__DIR__ . '/styles/content.css');
        if ($headline_scheme === HeadlineScheme::THREE) {
            // This is the only headline scheme that needs a style, because headlines have different sizes
            // The numbers of the other headline schemes are creted in the XSLT processor
            $styles .= "\n" . file_get_contents(__DIR__ . '/styles/headlines-three.css');
        }
        if ($add_paragraph_numbers) {
            // this adds a margin to the body and moves the paragraph number outside beneath the following block
            $styles .= "\n" . file_get_contents(__DIR__ . '/styles/numbers.css');
        }
        return "<style>\n$styles\n</style>\n";
    }

    public function replaceCustomMarkup(string $html): string
    {
        $html = preg_replace('/<w-p w="([0-9]+)" p="([0-9]+)">/', '<span data-w="$1" data-p="$2">', $html);
        $html = str_replace('</w-p>', '</span>', $html);
        return $html;
    }

    public function removeCustomMarkup(string $html): string
    {
        $html = preg_replace('/<w-p w="([0-9]+)" p="([0-9]+)">/', '', $html);
        $html = str_replace('</w-p>', '', $html);
        return $html;
    }

    public function processXslt(
        string $html,
        string $xslt_file,
        int $service_version,
        bool $add_paragraph_numbers = false,
        HeadlineScheme $headline_scheme = HeadlineScheme::NUMERIC
    ): string {
        try {
            // functions called from XSLT are static and need a static state
            self::$headlineScheme = $headline_scheme;

            self::initParaCounter();
            self::initWordCounter();
            self::initHeadlineCounters();

            // remove ascii control characters except tab, cr and lf
            $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $html);

            // don't process an empty text
            $html = trim($html);
            if (empty($html)) {
                return '';
            }

            // get the XSLT document
            // set the URI to allow document() within the XSL file
            $xslt_doc = new \DOMDocument('1.0', 'UTF-8');
            $xslt_doc->loadXML(file_get_contents($xslt_file));
            $xslt_doc->documentURI = $xslt_file;

            // get the XSLT processor
            $xslt = new \XSLTProcessor();
            $xslt->registerPhpFunctions();
            $xslt->importStyleSheet($xslt_doc);
            $xslt->setParameter('', 'service_version', $service_version);
            $xslt->setParameter('', 'add_paragraph_numbers', (int) $add_paragraph_numbers);

            // fault-tolerant HTML parsing
            // add XML encoding to properly support asian characters
            $dom_doc = new \DOMDocument('1.0', 'UTF-8');
            $dom_doc->loadHTML('<?xml encoding="UTF-8"?' . '>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);

            $result = $xslt->transformToDoc($dom_doc);
            $processed = $result->saveHTML();

            return str_replace('<?xml encoding="UTF-8"?>', '', $processed);
        } catch (\Throwable $e) {
            return 'HTML PROCESSING ERROR:<p>' . $e->getMessage() . '</p>' . $html;
        }
    }

    public static function initParaCounter(): void
    {
        self::$paraCounter = 0;
    }

    public static function currentParaCounter(): string
    {
        return self::$paraCounter;
    }

    public static function nextParaCounter(): string
    {
        self::$paraCounter++;
        return self::$paraCounter;
    }

    public static function initWordCounter(): void
    {
        self::$wordCounter = 0;
    }

    public static function currentWordCounter(): string
    {
        return self::$wordCounter;
    }

    public static function nextWordCounter(): string
    {
        self::$wordCounter++;
        return self::$wordCounter;
    }

    public static function initHeadlineCounters(): void
    {
        self::$h1Counter = 0;
        self::$h2Counter = 0;
        self::$h3Counter = 0;
        self::$h4Counter = 0;
        self::$h5Counter = 0;
        self::$h6Counter = 0;
    }

    public static function nextHeadlinePrefix($tag): string
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

        switch (self::$headlineScheme) {

            case HeadlineScheme::NUMERIC:
                switch ($tag) {
                    case 'h1':
                        return self::$h1Counter . ' ';
                    case 'h2':
                        return self::$h1Counter . '.' . self::$h2Counter . ' ';
                    case 'h3':
                        return self::$h1Counter . '.' . self::$h2Counter . '.' . self::$h3Counter . ' ';
                    case 'h4':
                        return self::$h1Counter . '.' . self::$h2Counter . '.' . self::$h3Counter . '.' . self::$h4Counter . ' ';
                    case 'h5':
                        return self::$h1Counter . '.' . self::$h2Counter . '.' . self::$h3Counter . '.' . self::$h4Counter . '.' . self::$h5Counter . ' ';
                    case 'h6':
                        return self::$h1Counter . '.' . self::$h2Counter . '.' . self::$h3Counter . '.' . self::$h4Counter . '.' . self::$h5Counter . '.' . self::$h6Counter . ' ';
                }
                return '';

            case HeadlineScheme::EDUTIEK:
                switch ($tag) {
                    case 'h1':
                        return self::toLatin(self::$h1Counter, true) . '. ';
                    case 'h2':
                        return self::toRoman(self::$h2Counter) . '. ';
                    case 'h3':
                        return self::$h3Counter . '. ';
                    case 'h4':
                        return self::toLatin(self::$h4Counter) . '. ';
                    case 'h5':
                        return self::toLatin(self::$h5Counter) . self::toLatin(self::$h5Counter) . '. ';
                    case 'h6':
                        return '(' . self::$h6Counter . ') ';
                }
                return '';
        }

        return '';
    }

    /**
     * Get a latin character representation of a number
     */
    public static function toLatin(int $num, $upper = false): string
    {
        if ($num == 0) {
            return '0';
        }
        $num = $num - 1;
        $text = '';

        do {
            $char = substr('abcdefghijklmnopqrstuvwxyz', $num % 26, 1);
            $text = ($upper ? ucfirst($char) : $char) . $text;
            $num = intdiv($num, 26);
        } while ($num > 0);

        return $text;
    }

    /**
     * Get a roman letter representation of a number
     */
    public static function toRoman(int $num): string
    {
        if ($num == 0) {
            return '0';
        }
        $text = '';

        $steps = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1
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
    public static function splitWords($text): \DOMElement
    {
        $words = preg_split("/([\s]+)/", $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        $doc = new DOMDocument();
        $root = $doc->createElement("root");

        $current = '';
        foreach ($words as $word) {
            if ($word == ' ' && (trim($current) == $current || trim($current) == '')) {
                // append a space to the last word if it is pure text or pure space
                // (don't add if a text space is already added to the last word)
                $current .= $word;
            } else {
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

    public static function filterStyle(string $style): string
    {
        $declarations = array_filter(array_map('trim', explode(';', $style)));

        $kept = [];
        foreach ($declarations as $declaration) {
            // Property vom Wert trennen
            $parts = explode(':', $declaration, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $property = strtolower(trim($parts[0]));

            if (in_array($property, self::$allowedStyles, true)) {
                $kept[] = trim($parts[0]) . ': ' . trim($parts[1]);
            }
        }

        return implode('; ', $kept);
    }
}
