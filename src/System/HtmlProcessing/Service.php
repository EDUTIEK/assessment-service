<?php

namespace Edutiek\AssessmentService\System\HtmlProcessing;

use DOMDocument;
use Edutiek\AssessmentService\System\Data\HeadlineScheme;

/**
 * Tool for processing HTML code coming from the rich text editor
 */
class Service implements FullService
{
    public static int $paraCounter = 0;
    public static int $wordCounter = 0;
    public static int $h1Counter = 0;
    public static int $h2Counter = 0;
    public static int $h3Counter = 0;
    public static int $h4Counter = 0;
    public static int $h5Counter = 0;
    public static int $h6Counter = 0;

    public static $headline_scheme = HeadlineScheme::NUMERIC;
    public static bool $forPdf = false;


    /**
     * Process html text for marking
     * This will add the paragraph numbers and headline prefixes
     * and split up all text to single word embedded in <w-p> elements.
     *      the 'w' attribute is the word number
     *      the 'p' attribute is the paragraph number
     */
    public function processHtmlForMarking(string $html) : string
    {
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

        $html = $this->processXslt($html, __DIR__ . '/xsl/cleanup.xsl', 0);
        $html = $this->processXslt($html, __DIR__ . '/xsl/numbers.xsl', 0);

        return $html;
    }


    /**
     * Get the XSLt Processor for an XSL file
     * The process_version is a number which can be increased with a new version of the processing
     * This number is provided as a parameter to the XSLT processing
     */
    protected function processXslt(
        string $html,
        string $xslt_file,
        int $service_version,
        bool $add_paragraph_numbers = false,
        bool $for_pdf = false,
        HeadlineScheme $headline_scheme = HeadlineScheme::NUMERIC,
    ): string
    {
        try {
            self::$headline_scheme = $headline_scheme;

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
            $dom_doc->loadHTML('<?xml encoding="UTF-8"?' . '>' . $html);

            //$xml = $xslt->transformToXml($dom_doc);
            $result = $xslt->transformToDoc($dom_doc);
            $xml = $result->saveHTML();

            $xml = preg_replace('/<\?xml.*\?>/', '', $xml);
            $xml = str_replace(' xmlns:php="http://php.net/xsl"', '', $xml);

            return $xml;
        } catch (\Throwable $e) {
            return 'HTML PROCESSING ERROR:<br>' . $e->getMessage() . '<hr>' . $html;
        }
    }

    /**
     * Get the paragraph counter tag for PDF generation
     * This should help for a correct vertical alignment with the counted block in TCPDF
     */
    public static function paraCounterTag($tag): string
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
        } else {
            return 'p';
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

        switch (self::$headline_scheme) {

            case HeadlineScheme::NUMERIC->value:
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

                // no break
            case HeadlineScheme::EDUTIEK->value:
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
}
