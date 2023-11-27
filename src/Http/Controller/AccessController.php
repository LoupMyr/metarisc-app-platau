<?php

namespace App\Http\Controller;

use Assert\Assertion;
use Metarisc\Metarisc;
use Metarisc\Model\Email;
use App\Service\SessionService;
use App\Domain\Entity\UserCache;
use Psr\SimpleCache\CacheInterface;
use Metarisc\Service\UtilisateursAPI;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Service\UserCacheServiceInterface;

class AccessController
{
    public function __construct(
        private Metarisc $metarisc,
        private SessionService $sessionService,
        private UserCacheServiceInterface $userCacheService
    ) {
    }

    public function __invoke(ServerRequestInterface $request, array $args = []) : void
    {
        /** @var array<string,string> $params */
        $query_params = $request->getQueryParams();

        // Si une erreur est détectée, on traite l'information
        if (\array_key_exists('error', $query_params)) {
            Assertion::string($query_params['error']);
            header('Location: http://localhost:8000/error?error='.$query_params['error']);
            exit;
            // throw new \Exception($query_params['error']);
            // return $response;
        }

        // Quand on arrive ici, ça veut dire que normalement on a un code Metarisc qui permet de récupérer un access token
        if (!\array_key_exists('code', $query_params)) {
            // throw new \Exception("Il devrait y'avoir un code ici pour l'échanger avec un access token, bizarre ...");
            header('Location: http://localhost:8000/');
            exit;
        }

        $code = $query_params['code'];
        Assertion::string($code);

        // On échange, grace au SDK Metarisc, le code avec un access token
        // Si il y a un cache dans le container, l'access token sera stocké dedans
        $this->metarisc->authenticate('oauth2:authorization_code', [
            'code'         => $code,
            'scope'        => 'openid profile email',
            'redirect_uri' => 'http://localhost:8000/access',
        ]);

        // Récupération de l'email de l'utilisateur
        $utilisateursApi = $this->metarisc->utilisateurs;
        \assert($utilisateursApi instanceof UtilisateursAPI);
        $emails        = $utilisateursApi->paginateMoiEmails()->getCurrentPageResults();
        $email_primary = null;
        foreach ($emails as $email) {
            Assertion::isArray($email);
            $email = Email::unserialize($email);
            if (true === $email->getIsPrimary()) {
                $email_primary = $email->getEmail();
                break;
            }
        }
        if (null === $email_primary) {
            throw new \Exception("Problème dans la récupération d'email");
        }

        $access_token = $this->metarisc->getClient()->getCredentials()['access_token'];

        //On stocke dans la session de l'utilisateur son email et son access token
        $this->sessionService->setSessionCookies([
            'access_token' => $access_token,
            'email' => $email_primary
        ]);

        // stocke les cookies de sessions en cookies de navigateur
        $this->sessionService->setAllCookies();

        // On controle dans notre base de données si on connait l'utilisateur
        $userCache = $this->userCacheService->getUserCacheByEmail($email_primary);
        if (null === $userCache) {
            // Si on connait pas l'utilisateur, on l'inscrit dans la base
            $userCache = new UserCache($email_primary, false, $access_token, '');
            $this->userCacheService->addUserCache($userCache);
        } else {
            // Si on le connait, on met à jour son access token
            $userCacheWithNewAccessToken = new UserCache($userCache->getEmail(), $userCache->getOption1(), $access_token, $userCache->getRefreshToken());
            $this->userCacheService->updateUserCache($userCache->getEmail(), $userCacheWithNewAccessToken);
        }



        // On redirect /
        header('Location: http://localhost:8000/home');
        exit;
    }
}