<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectController extends Controller{

    /**
     * @Route("/redirect", name="redirect_page")
     */
    public function redirectAction(Request $request){
        if($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute('admin_page');
        }
        if($this->get('security.authorization_checker')->isGranted('ROLE_PERSONAL')){
            return $this->redirectToRoute('personal_page');
        }
        if($this->get('security.authorization_checker')->isGranted('ROLE_ALUMNO')){
            return $this->redirectToRoute('alumno_page');
        }

        return new Response("<html><body>Ooops!!!</body></html>");
    }

    
}
