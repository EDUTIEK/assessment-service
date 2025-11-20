<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfCreator;

class Options
{
    private string $creator = '';
    private string $author = '';
    private string $title = '';
    private string $subject = '';
    private string $keywords = '';
    private int $start_page_number = 1;
    private bool $print_header = false;
    private bool $print_footer = true;
    private int $header_margin = 5; // In mm
    private int $footer_margin = 10; // In mm
    private int $top_margin = 10; // In mm
    private int $bottom_margin = 10; // In mm
    private int $left_margin = 15; // In mm
    private int $right_margin = 15; // In mm
    private bool $portrait = true;

    public function getCreator(): string
    {
        return $this->creator;
    }

    public function withCreator(string $creator): self
    {
        $clone = clone $this;
        $clone->creator = $creator;

        return $clone;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function withAuthor(string $author): self
    {
        $clone = clone $this;
        $clone->author = $author;

        return $clone;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function withTitle(string $title): self
    {
        $clone = clone $this;
        $clone->title = $title;

        return $clone;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function withSubject(string $subject): self
    {
        $clone = clone $this;
        $clone->subject = $subject;

        return $clone;
    }

    public function getKeywords(): string
    {
        return $this->keywords;
    }

    public function withKeywords(string $keywords): self
    {
        $clone = clone $this;
        $clone->keywords = $keywords;

        return $clone;
    }

    public function getStartPageNumber(): int
    {
        return $this->start_page_number;
    }

    public function withStartPageNumber(int $start_page_number): self
    {
        $clone = clone $this;
        $clone->start_page_number = $start_page_number;

        return $clone;
    }

    public function getPrintHeader(): bool
    {
        return $this->print_header;
    }

    public function withPrintHeader(bool $print_header): self
    {
        $clone = clone $this;
        $clone->print_header = $print_header;

        return $clone;
    }

    public function getPrintFooter(): bool
    {
        return $this->print_footer;
    }

    public function withPrintFooter(bool $print_footer): self
    {
        $clone = clone $this;
        $clone->print_footer = $print_footer;

        return $clone;
    }

    public function getHeaderMargin(): int
    {
        return $this->header_margin;
    }

    public function withHeaderMargin(int $header_margin): self
    {
        $clone = clone $this;
        $clone->header_margin = $header_margin;

        return $clone;
    }

    public function getFooterMargin(): int
    {
        return $this->footer_margin;
    }

    public function withFooterMargin(int $footer_margin): self
    {
        $clone = clone $this;
        $clone->footer_margin = $footer_margin;

        return $clone;
    }

    public function getTopMargin(): int
    {
        return $this->top_margin;
    }

    public function withTopMargin(int $top_margin): self
    {
        $clone = clone $this;
        $clone->top_margin = $top_margin;

        return $clone;
    }

    public function getBottomMargin(): int
    {
        return $this->bottom_margin;
    }

    public function withBottomMargin(int $bottom_margin): self
    {
        $clone = clone $this;
        $clone->bottom_margin = $bottom_margin;

        return $clone;
    }

    public function getLeftMargin(): int
    {
        return $this->left_margin;
    }

    public function withLeftMargin(int $left_margin): self
    {
        $clone = clone $this;
        $clone->left_margin = $left_margin;

        return $clone;
    }

    public function getRightMargin(): int
    {
        return $this->right_margin;
    }

    public function withRightMargin(int $right_margin): self
    {
        $clone = clone $this;
        $clone->right_margin = $right_margin;

        return $clone;
    }

    public function getPortrait(): bool
    {
        return $this->portrait;
    }

    public function withPortrait(bool $portrait): self
    {
        $clone = clone $this;
        $clone->portrait = $portrait;

        return $clone;
    }
}
