<?php

namespace App\Http\Controller;

use App\Domain\Repository\UserCacheRepositoryInterface;
use App\Domain\Service\UserCacheServiceInterface;
use App\Service\UserCacheService;
use Laminas;
use Assert\Assertion;
use Metarisc\Model\Email;
use Metarisc\Service\UtilisateursAPI;
use Psalm\Report;
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
        private Metarisc                  $metarisc,
        private CacheInterface            $cache,
        private SessionService            $sessionService,
        private UserCacheServiceInterface $userCacheService
    )
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args = []): void
    {
        $query_params = $request->getQueryParams();

        // Si une erreur est détectée, on traite l'information
        if (array_key_exists('error', $query_params)) {
            throw new \Exception("AIE");
            // return $response;
        }

        // Quand on arrive ici, ça veut dire que normalement on a un code Metarisc qui permet de récupérer un access token
        if (!array_key_exists('code', $query_params)) {
            throw new \Exception("Il devrait y'avoir un code ici pour l'échanger avec un access token, bizarre ...");
        }

        $code = $query_params['code'];

        // On échange, grace au SDK Metarisc, le code avec un access token
        // Si il y a un cache dans le container, l'access token sera stocké dedans
        $this->metarisc->getClient()->authenticate('oauth2:authorization_code', [
            'code' => $code,
            'scope' => 'openid profile email',
        ]);

        // On essaye de récupérer l'access token depuis le cache
        try {
            $tokens = $this->cache->get('metarisc-oauth2-token');
            //Le probleme est ici
            Assertion::isArray($tokens);
            Assertion::string($tokens['access_token']);
            $access_token = $tokens['access_token'];
            if($access_token === null) {
                throw new \Exception("L'access token est null.");
            }
        } catch (\Exception $e) {
        }

        // Récupération de l'email de l'utilisateur
        $utilisateursApi = $this->metarisc->utilisateurs;
        assert($utilisateursApi instanceof UtilisateursAPI);
        $emails = $utilisateursApi->paginateMoiEmails()->getCurrentPageResults();
        $email_primary = null;
        foreach ($emails as $email) {
            $email = Email::unserialize($email);
            assert($email instanceof Email);
            if ($email->getIsPrimary() === true) {
                $email_primary = $email->getEmail();
                break;
            }
        }
        if ($email_primary === null) {
            throw new \Exception("Problème dans la récupération d'email");
        }

        // On controle dans notre base de données si on connait l'utilisateur
        $userCache = $this->userCacheService->getUserCacheByEmail($email_primary);
        if($userCache===null){
            // Si on connait pas l'utilisateur, on l'inscrit dans la base
            $userCache = new UserCache($email_primary, false, $access_token, '');
            $this->userCacheService->addUserCache($userCache);
        }else{
            // Si on le connait, on met à jour son access token
            $userCacheWithNewAccessToken = new UserCache($userCache->getEmail(), $userCache->getOption1(), $access_token, $userCache->getRefreshToken());
            $this->userCacheService->updateUserCache($userCache->getEmail(), $userCacheWithNewAccessToken);
        }

        // On stocke dans la session de l'utilisateur son email et son access token
        $this->sessionService->setAllCookies($access_token, $email_primary);

        // On redirect /
        header("Location: http://localhost:8000/home");
        exit;
    }
}
