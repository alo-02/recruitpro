<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;
use Throwable;

class ResumeTextExtractor
{
    public function fromStoragePath(?string $path, string $disk = 'public'): string
    {
        if (! $path || ! Storage::disk($disk)->exists($path)) {
            return '';
        }

        $absolute = Storage::disk($disk)->path($path);
        $extension = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));

        try {
            return match ($extension) {
                'pdf' => $this->fromPdf($absolute),
                'docx' => $this->fromDocx($absolute),
                'doc', 'txt' => (string) @file_get_contents($absolute),
                default => '',
            };
        } catch (Throwable) {
            return '';
        }
    }

    protected function fromPdf(string $absolute): string
    {
        $parser = new PdfParser;
        $pdf = $parser->parseFile($absolute);

        return trim($pdf->getText() ?? '');
    }

    protected function fromDocx(string $absolute): string
    {
        if (! class_exists(\ZipArchive::class)) {
            return '';
        }

        $zip = new \ZipArchive;
        if ($zip->open($absolute) !== true) {
            return '';
        }

        $xml = $zip->getFromName('word/document.xml') ?: '';
        $zip->close();

        if ($xml === '') {
            return '';
        }

        $text = strip_tags(str_replace(['</w:p>', '</w:tr>'], "\n", $xml));

        return trim(html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8'));
    }
}
