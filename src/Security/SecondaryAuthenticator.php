<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class SecondaryAuthenticator extends AbstractAuthenticator
{
    public const LOGIN_ROUTE = 'app_login';
    public const REDIRECT_FAILURE_ROUTE = 'home_index';
    public const REDIRECT_SUCCESS_ROUTE = 'qrcode_index';

    public function supports(Request $request): ?bool
    {
        return 'home_index' === $request->attributes->get(key: '_route') && $request->isMethod(method: Request::METHOD_POST);
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->getPayload()->getString(key: 'email');
        $password = $request->getPayload()->getString(key: 'password');
        $csrfToken = $request->getPayload()->getString(key: '_csrf_token');

        // Vérifier le token CSRF
        if (!$this->isValidCsrfToken(tokenId: 'authenticate', token: $csrfToken)) {
            throw new AuthenticationException(message: 'Invalid CSRF token');
        }

        // Créer un objet Passport
        return new Passport(
            userBadge: new UserBadge(userIdentifier: $email),
            credentials: new PasswordCredentials(password: $password),
            badges: [
                // Ajouter un badge CSRF Token
                new CsrfTokenBadge(csrfTokenId: 'authenticate', csrfToken: $csrfToken),
                // Ajouter un badge Remember Me
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Rediriger l'utilisateur vers la page de succès
        return new Response(content: self::REDIRECT_SUCCESS_ROUTE);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response(content: self::REDIRECT_FAILURE_ROUTE);
    }

    public function isValidCsrfToken(string $tokenId, ?string $token): bool
    {
        return true;
    }
}
