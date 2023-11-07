<?php

namespace App\Http\Controller;

use Laminas;
use Assert\Assertion;
use Twig\Environment;
use Metarisc\Metarisc;
use App\Service\SessionService;
use Doctrine\ORM\EntityManager;
use App\Domain\Entity\UserCache;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AccessController
{
    public function __construct(
        private Metarisc $metarisc,
        private Environment $twig,
        private CacheInterface $cache,
        private EntityManager $em,
        private SessionService $sessionService
    ) {
    }

    public function __invoke(ServerRequestInterface $request, array $args = []) : ResponseInterface
    {
        $responseFactory = new Laminas\Diactoros\ResponseFactory();
        $response        = $responseFactory->createResponse();
        $template        = $this->twig->load('home.twig');

        // SI LA REQUETE EST "GET" ALORS ON AUTHENTICATE AVEC LE CODE DANS L'URL ET ON ECRIT L'ACCESS TOKEN DANS LE CACHE
        if ('GET' == $request->getMethod()) {
            $code = $_GET['code'];
            $this->metarisc->getClient()->authenticate('oauth2:authorization_code', [
                'code'         => $code,
                'scope'        => 'openid profile email',
            ], $this->cache);
        }
        $access = '';
        // ON ESSAYE DE RECUPERER L'ACCESS TOKEN DEPUIS LE CACHE
        try {
            $tokens = $this->cache->get('metarisc-oauth2-token');
            Assertion::isArray($tokens);
            Assertion::string($tokens['access_token']);
            $access = $tokens['access_token'];
        } catch (\Exception $e) {
        }
        // ON RECUPERE L'EMAIL / PROFIL / LE "UserCache" CORRESPONDANT AU CURRENT USER
        $email     = $this->getEmail();
        $profil    = $this->getProfil();
        $userCache = $this->getUserCache($email, $access);

        // SI LA REQUETE EST POST (CHANGEMENT DE PRESENCE)
        if ('POST' == $request->getMethod()) {
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
        }
        // SI LA SESSION N'EST PAS EN MARCHE ALORS ON PREPARE LES PARAMETRES DE LA SESSION ET ON LA DEMARRE
        if (!$this->sessionService->isConnected()) {
            $this->sessionService->startSecureSession();
        }
        // ON MET L'EMAIL ET L'ACCESS TOKEN EN TANT QUE COOKIE

        $this->sessionService->setAllCookies($access, $email);

        $connected = $userCache->getOption1();
        // CREATION D'UN TABLEAU AVEC TOUTES LES VALEURES UTILES DU USER POUR TWIG
        $user = [
            'first_name' => $profil['first_name'],
            'last_name'  => $profil['last_name'],
            'email'      => $email,
            'connected'  => $connected,
        ];
        $html = $template->render([
            'profil'    => $user,
            'connected' => $connected,
        ]);
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
