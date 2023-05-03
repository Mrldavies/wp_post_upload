<?php

namespace mrldavies\DocUpload;

use PhpOffice\PhpWord;
use Mimrahe\StripTags\Stripper;

class ProcessFile
{

    protected $allowedTag;
    protected $htmlWriter;
    protected $file;
    protected $filePath;
    protected $html;
    protected $status;
    protected $postType;
    protected $title;
    protected $postId;

    public function __construct($file, $postType, $status)
    {
        $this->file = $file;
        $this->status = $status;
        $this->postType = $postType;
        $this->filePath =  '';
        $this->postId = '';
        $this->htmlWriter = (object) [];
        $this->html = '';
        $this->title = 'test';
        $this->allowedTag = ['a', 'p', 'b', 'strong', 'ul', 'li', 'ol', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
    }

    /**
     * Creates the wp post
     *
     * @return Self
     */
    public function post(): self
    {
        $this->postId = wp_insert_post([
            'post_title' => $this->title,
            'post_content' => $this->html,
            'post_status' => $this->status,
            'post_type' => $this->postType,
        ]);

        unset($this->filePath);
        return $this;
    }

    /**
     * Converts word doc to HTML
     *
     * @return Self
     */
    public function convertToHtml(): self
    {
        try {
            $document = PhpWord\IOFactory::load($this->file);
            $this->htmlWriter = new PhpWord\Writer\HTML($document);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to load the file: ' . $e->getMessage());
        }
        return $this;
    }

    /**
     * Saves a HTML file to the uploads directory
     *
     * @return self
     */
    public function saveFile(): self
    {
        $upload_dir = wp_upload_dir();
        $documents_dir = $upload_dir['basedir'] . '/documents';

        if (!file_exists($documents_dir)) {
            mkdir($documents_dir);
        }

        $filename = uniqid('docupload_', true) . '.html';
        $this->filePath = $documents_dir . '/' . $filename;
        $this->htmlWriter->save($this->filePath);
        return $this;
    }

    private function stripEmptyParagraphs($html)
    {
        return preg_replace('/<p[^>]*>(&nbsp;|\s)*<\/p>/', '', $html);
    }

    private function stripInlineStyles($html)
    {
        return preg_replace('/(<[^>]+) style=".*?"/i', '$1', $html);
    }

    private function stripTitleTags($html)
    {
        return preg_replace('/<title>(.*?)<\/title>/', '', $html);
    }

    private function stripStyleTag($html)
    {
        return preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
    }

    /**
     * Strips unwanted tags from the html
     *
     * @return Self
     */
    public function cleanHtml(): self
    {
        $html = file_get_contents($this->filePath);

        $html = $this->stripEmptyParagraphs($html);
        $html = $this->stripInlineStyles($html);
        $html = $this->stripTitleTags($html);
        $html = $this->stripStyleTag($html);

        $stripper = new Stripper($html);
        $stripper->except($this->allowedTag);
        $this->html = $stripper->strip();

        return $this;
    }

    /**
     * Echos html to page
     *
     * @return void
     */
    public function output()
    {
        echo $this->html;
    }
}
