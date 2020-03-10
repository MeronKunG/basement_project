<?php

namespace App\Controller;

use App\Entity\TestCsRemarksUser;
use App\Repository\TestCSRemarksUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/login_api", name="app_login_service")
     */
    public function login_service(Request $request, TestCSRemarksUserRepository $remarksUserRepository)
    {
        header("Access-Control-Allow-Origin: *");
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $result = ["status" => false];
        if ($username != null && $password != null) {
            $checkUser = $remarksUserRepository->getUserName($username);
            if (!empty($checkUser)) {
                $result = $this->checkCredentials($checkUser[0], $password);
                if($result == true) {
                    $result = ["name" => $checkUser[0]->getUserName(), "status" => $result];
                } else {
                    $result = ["status" => $result];
                }
            } else {
                $result = ["status" => false];
            }
        }
        return $this->json($result);
    }

    public function checkCredentials(UserInterface $user, $credentials)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function userLogout()
    {
        return "";
    }
}
