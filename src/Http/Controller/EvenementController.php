<?php

namespace App\Http\Controller;

use Assert\Assertion;
use Twig\Environment;
use Metarisc\Metarisc;
use Metarisc\Model\Evenement;
use Metarisc\Service\EvenementsAPI;
use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class EvenementController
{
    public function __construct(
        private Metarisc $metarisc,
        private Environment $twig
    ) {
    }

    public function __invoke(ServerRequestInterface $request, array $args) : ResponseInterface
    {
        $template = $this->twig->load('evenements.twig');

        $evenementsService = $this->metarisc->evenements;
        Assertion::isInstanceOf($evenementsService, EvenementsAPI::class);

        $evenementsPager      = $evenementsService->paginateEvenements();
        $evenementsArray      = $evenementsPager->getCurrentPageResults();
        $evenements           = [];
        // CONVERSION DE TOUS LES ELT(array) RECUPERÉ EN OBJET Evenement
        foreach ($evenementsArray as $elt) {
            Assertion::isArray($elt);
            $event        = Evenement::unserialize($elt);
            $evenements[] = $event;
        }
        // SI L'UTILISATEUR VIENT D'ENVOYER UN FORMULAIRE
        if ('POST' == $request->getMethod()) {
            $body = $request->getParsedBody();
            if (isset($body)) {
                // SI LE FORMULAIRE EST CELUI DE RECHERCHE PAR TITRE
                if (isset($body['btnSearchTitre'])) {
                    $titleInput = $body['searchTitle'];
                    Assertion::string($titleInput);
                    Assertion::notEmpty($titleInput);
                    $result = [];
                    // TRANSFORMATION DE LA SAISIE EN UNE CHAINE DE CARACTERE COLLÉ ET EN MINUSCULE
                    $trimInput = mb_strtolower(str_replace(' ', '', $titleInput));
                    // PARCOURS DE TOUS LES ELEMENTS DU TABLEAU
                    foreach ($evenements as $elt) {
                        // TRANSFORMATION DU TITRE DE L'ELT EN UNE CHAINE DE CARACTERE COLLÉ ET EN MINUSCULE
                        $title = $elt->getTitle();
                        Assertion::string($title);
                        $trimTitle = mb_strtolower(str_replace(' ', '', $title));
                        // SI LE TITLE CONTIENS L'INPUT
                        if (str_contains($trimTitle, $trimInput)) {
                            // ON AJOUT L'ELT AU TABLEAU DE RESULTAT
                            $result[] = $elt;
                        }
                    }
                    $evenements = $result;
                }
            }
        }

        $html     = $template->render([
            'evenements' => $evenements,
        ]);

        $responseFactory = new ResponseFactory();
        $response        = $responseFactory->createResponse();
        $response->getBody()->write($html);

        return $response;
    }
}
