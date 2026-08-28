<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;
use Throwable;

class ResumeTextExtractor
{
    public const ALLOWED_EXTENSIONS = ['pdf', 'docx', 'doc', 'txt'];

    /**
     * @return array{valid:bool,extension:string,error:?string}
     */
    public function validateUploadedPath(?string $path, string $disk = 'public'): array
    {
        if (! $path) {
            return ['valid' => false, 'extension' => '', 'error' => 'No resume file was uploaded.'];
        }

        if (! Storage::disk($disk)->exists($path)) {
            return ['valid' => false, 'extension' => '', 'error' => 'Resume file could not be found.'];
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            return ['valid' => false, 'extension' => $extension, 'error' => 'Resume must be a PDF or Word document.'];
        }

        $absolute = Storage::disk($disk)->path($path);
        $size = @filesize($absolute) ?: 0;
        if ($size < 64) {
            return ['valid' => false, 'extension' => $extension, 'error' => 'Resume file is empty or unreadable.'];
        }

        if ($extension === 'pdf') {
            $header = (string) @file_get_contents($absolute, false, null, 0, 5);
            if (! str_starts_with($header, '%PDF')) {
                return ['valid' => false, 'extension' => $extension, 'error' => 'File is not a valid PDF resume.'];
            }
        }

        return ['valid' => true, 'extension' => $extension, 'error' => null];
    }

    public function fromStoragePath(?string $path, string $disk = 'public'): string
    {
        $check = $this->validateUploadedPath($path, $disk);
        if (! $check['valid']) {
            return '';
        }

        $absolute = Storage::disk($disk)->path($path);

        try {
            return match ($check['extension']) {
                'pdf' => $this->fromPdf($absolute),
                'docx' => $this->fromDocx($absolute),
                'doc', 'txt' => trim((string) @file_get_contents($absolute)),
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
