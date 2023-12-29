<?php

namespace App\Service;

use App\Domain\Service\PieceServiceInterface;
use Psr\Http\Message\ResponseInterface;

class PieceService implements PieceServiceInterface
{
    public function uploadDocument(string $filename, string $file_contents, int $type_document) : Array
    {

    }

    public function downloadDocument() : ResponseInterface
    {
        
    }

    public static function guessExtension(ResponseInterface $http_response) : ?string
    {

    }
}