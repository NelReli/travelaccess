<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Routing\Annotation\Route;

class CookieController extends AbstractController
{
    #[Route('/accept-cookies', name: 'app_accept_cookies')]
    public function acceptCookies(): Response
    {
        $response = $this->redirectToRoute('app_home');

        $cookie = Cookie::create('cookies_accepted', 'true', strtotime('+1 week'), '/', null, false, true, false, 'Lax');
        $response->headers->setCookie($cookie);

        return $response;
    }

    #[Route('/refuse-cookies', name: 'app_refuse_cookies')]
    public function refuseCookies(): Response
    {
        $response = $this->redirectToRoute('app_home');

        $cookie = Cookie::create('cookies_refused', 'true', strtotime('+1 week'), '/', null, false, true, false, 'Lax');
        $response->headers->setCookie($cookie);

        return $response;
    }
}
