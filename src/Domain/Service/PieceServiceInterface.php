<?php

namespace App\Domain\Service;

use Psr\Http\Message\ResponseInterface;

interface PieceServiceInterface
{
    public function uploadDocument(string $filename, string $file_contents, int $type_document) : Array;

    public function downloadDocument() : ResponseInterface;

    public static function guessExtension(ResponseInterface $http_response) : ?string;
}