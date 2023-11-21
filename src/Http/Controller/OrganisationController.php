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
            // On verifie si nous avons un query params "idInsert"
            if (!isset($request->getQueryParams()['idInsert'])) {
                // Si on a pas de query param, on charge la page organisationAsk
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
                // Si on a le query param, on charge la page organisationRep
                $template             = $this->twig->load('organisationsRep.twig');
                $organisationsService = $this->metarisc->organisations;
                \assert($organisationsService instanceof OrganisationAPI);
                /** @var array<string,string> $params */
                $params   = $request->getQueryParams();
                $idInsert = $params['idInsert'];
                // On fait une requête à metarisc, afin de recuperer les informations de l'organisation
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
            // Si erreur, on charge la page error, en affichant l'erreur rencontré
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
