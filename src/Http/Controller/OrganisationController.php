<?php

namespace App\Http\Controller;

use Twig\Environment;
use Metarisc\Metarisc;
use Metarisc\Service\OrganisationAPI;
use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OrganisationController
{
    public function __construct(
        private Metarisc $metarisc,
        private Environment $twig,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, array $args = []) : ResponseInterface
    {
        try {
            if (!isset($request->getQueryParams()['idInsert'])) {
                $template = $this->twig->load('organisationsAsk.twig');
                $idInsert = '';
                $html     = $template->render([
                    'idInsert' => $idInsert,
                ]);
                $responseFactory = new ResponseFactory();
                $response        = $responseFactory->createResponse();
                $response->getBody()->write($html);

                return $response;
            } else {
                $template             = $this->twig->load('organisationsRep.twig');
                $organisationsService = $this->metarisc->organisations;
                \assert($organisationsService instanceof OrganisationAPI);
                $idInsert = $request->getQueryParams()['idInsert'];
                \assert(\is_string($idInsert));
                $oneOrganisation = $organisationsService->getOrganisation($idInsert);
                $html            = $template->render([
                    'organisation' => $oneOrganisation,
                ]);
                $responseFactory = new ResponseFactory();
                $response        = $responseFactory->createResponse();
                $response->getBody()->write($html);

                return $response;
            }
        } catch (\Exception $e) {
            $error    = $e->getCode();
            $template = $this->twig->load('error.twig');
            $html     = $template->render([
                'error' => $error,
            ]);
            $responseFactory = new ResponseFactory();
            $response        = $responseFactory->createResponse();
            $response->getBody()->write($html);

            return $response;
        }
    }
}
