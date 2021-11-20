<?php

namespace App\Controller\Account;

use App\Entity\User;
use App\EventListener\AuthorizationRequestResolveEventListener;
use App\Form\ConsentType;
use Doctrine\ORM\EntityManagerInterface;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use League\Bundle\OAuth2ServerBundle\Model\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/register", name="register")
     */
    public function register()
    {
        return new Response(null, 501);
    }

    /**
     * @Route("/consent", name="consent")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function consent(Request $request, ClientManagerInterface $clientManager)
    {
        $session = $request->getSession();
        $authorizationQuery = $session->get(AuthorizationRequestResolveEventListener::SESSION_AUTHORIZATION_QUERY);

        $form = $this->createForm(ConsentType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            switch (true) {
                case $form->get('accept')->isClicked():
                    $session->set(AuthorizationRequestResolveEventListener::SESSION_AUTHORIZATION_RESULT, true);
                    break;
                case $form->get('refuse')->isClicked():
                    $session->set(AuthorizationRequestResolveEventListener::SESSION_AUTHORIZATION_RESULT, false);
            }

            return $this->redirectToRoute('oauth2_authorize', $authorizationQuery);
        }

        $scopesDescriptions = [
            'POST_READ' => 'информацию о ваших постах',
            'POST_WRITE' => 'возможность изменять ваши посты',
            'POST_CREATE' => 'возможность созадвать посты',
            'POST_DELETE' => 'возможность удалять ваши посты',
            'USER_READ' => 'информацию о вашем профиле',
            'USER_WRITE' => 'возможность изменять ваш профиль',
        ];

        $scopes = '';
        foreach (explode(' ', $authorizationQuery['scope']) as $scope) {
            $scopes .= $scopesDescriptions[$scope] . ', ';
        }
        $scopes = substr($scopes, 0, strlen($scopes) - 2);

        return $this->render('security/consent.html.twig', [
            'form' => $form->createView(),
            'client' => $clientManager->find($authorizationQuery['client_id']),
            'scopes' => $scopes,
        ]);
    }
}
