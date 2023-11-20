<?php

namespace App\Http\Controller;

use Laminas;
use Assert\Assertion;
use Twig\Environment;
use Metarisc\Metarisc;
use App\Service\SessionService;
use Doctrine\ORM\EntityManager;
use App\Domain\Entity\UserCache;
use Laminas\Diactoros\ResponseFactory;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class FormMenuController{

    public function  __construct(
        private Environment $twig,
        private Metarisc $metarisc,
        private EntityManager $em,
    ){
    }

    public function __invoke(ServerRequestInterface $request, array $args) : ResponseInterface
    {
        $userCache = $this->getUserCache($_COOKIE['email'],$_COOKIE['access_token']);

        $body = $request->getParsedBody();
        if (isset($body) && !empty($body)) {
            if (isset($body['btnEnvoyer'])) {
                $bool = true;
                // ON REGARDE SI "connect" DU BODY ($_POST) EST "faux" ou "vrai"
                if ('faux' == $body['connect']) {
                    $bool = false;
                }
                // ON MET A JOUR LE "UserCache" SI LA VALEUR ACTUELLE DE "option1" EST != DE CELLE SAISIE DANS LE FORMULAIRE
                if ($userCache->getOption1() != $bool) {
                    try {
                        $emailInput = $body['email'];
                        Assertion::string($emailInput);
                        $userCache->setEmail($emailInput);
                        $userCache->setOption1($bool);
                        $this->em->persist($userCache);
                        $this->em->flush();
                    } catch (\Exception $e) {
                        throw new \Exception('Unexepted error while changing your presence, try again later', 500);
                    }
                }
            }
        }

        $connected = $userCache->getOption1();
        $profil = $this->getProfil();
        $email = $this->getEmail();
        // CREATION D'UN TABLEAU AVEC TOUTES LES VALEURES UTILES DU USER POUR TWIG
        $user = [
            'first_name' => $profil['first_name'],
            'last_name'  => $profil['last_name'],
            'email'      => $email,
            'connected'  => $connected,
        ];

        $template = $this->twig->load('home.twig');
        $html = $template->render([
            'profil'    => $user,
            'connected' => $connected,
        ]);

        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse();
        $response->getBody()->write($html);
        
        return $response;
        
    }

    /**
     * Récupération de l'email de l'utilisateur actuellement connecté.
     **/
    public function getEmail() : string
    {
        // REQUETE POUR L'EMAIL CURRENT USER
        $email_json = $this->metarisc->request('GET', '/@moi/emails', ['auth' => 'oauth']);
        // VERIFICATION QUE LES RESPONSES SONT OK (==200)
        if (200 == $email_json->getStatusCode()) {
            $email_decode = json_decode($email_json->getBody()->__toString(), true);
            Assertion::isArray($email_decode);
            $email_data = $email_decode['data'];
            Assertion::isArray($email_data);
            $email_0 = $email_data[0];
            Assertion::isArray($email_0);
            $email = $email_0['email'];
        } else {
            throw new \Exception('Une erreur est survenue lors de la récupération de l\'email');
        }
        Assertion::notNull($email);
        Assertion::string($email);

        return $email;
    }

    /**
     * Récupération du profil de l'utilisateur actuellement connecté.
     */
    public function getProfil() : array
    {
        // REQUETE POUR L'EMAIL CURRENT USER
        $profil_json = $this->metarisc->request('GET', '/@moi', ['auth' => 'oauth']);
        // VERIFICATION QUE LES RESPONSES SONT OK (==200)
        if (200 == $profil_json->getStatusCode()) {
            $profil           = json_decode($profil_json->getBody()->__toString(), true);
        } else {
            throw new \Exception('Une erreur est survenue lors de la récupération du profil');
        }

        Assertion::notNull($profil);
        Assertion::isArray($profil);

        return $profil;
    }

    /**
     * - Récupération d'un objet UserCache en fonction de l'email en paramètre.
     * - Si il n'y a pas d'objet UserCache correspondant à l'email donnée, l'objet UserCache sera créé.
     */
    public function getUserCache(string $email, string $access) : UserCache
    {
        // ON RECUPERE LE "UserCache" AVEC L'EntityManager ET LE REPOSITORY GRACE A L'EMAIL DU CURRENT USER
        $userCache = $this->em->getRepository(UserCache::class)->findOneBy([
            'email' => $email,
        ]);

        try {
            // VERIFICATION QUE LE "UserCache" RECUPERER EST OK
            if (!isset($userCache)) {
                $userCache = new UserCache($email, false, $access, '');
                $this->em->persist($userCache);
                $this->em->flush();
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
        }

        Assertion::notNull($userCache);

        return $userCache;
    }
}

