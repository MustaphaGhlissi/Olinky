<?php

namespace Protector\AppBundle\Controller;

use Protector\AppBundle\Entity\GeneratedLink;
use Protector\AppBundle\Form\GeneratedLinkType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="recaptcha_home")
     */
    public function indexAction()
    {
        return $this->render('ProtectorAppBundle:Default:index.html.twig');
    }

    /**
     * @Route("/ajouter-lien", name="add_link")
     */
    public function addLinkAction(Request $request)
    {
        $generatedLink = new GeneratedLink();
        $formLink = $this->createForm(GeneratedLinkType::class, $generatedLink, ['method'=>'POST','action'=>$this->generateUrl('add_link')]);
        $formLink->handleRequest($request);
        $errors = [];
        if($formLink->isSubmitted() && $formLink->isValid())
        {
            $links = explode(PHP_EOL, $generatedLink->getLinks());

            foreach ($links as $key=>$link)
            {
                if(trim($link) !== "")
                {
                    $link = filter_var($link, FILTER_SANITIZE_URL);
                    if (!filter_var($link, FILTER_VALIDATE_URL)) {
                        $errors[$link]="n'est pas un lien valide";
                    }
                }
                else
                {
                    if (array_key_exists($key,$links)) {
                        unset($links[$key]);
                    }
                }

            }

            if(count($errors) === 0)
            {
                $token = uniqid();
                $generatedLink->setToken($token);
                $generatedLink->setLinks($links);
                $em = $this->getDoctrine()->getManager();
                $em->persist($generatedLink);
                $em->flush();
                $showUrl = $request->getSchemeAndHttpHost().$this->generateUrl('show_link',['generatedToken'=>$token]);
                $msg = "Nouveau lien généré : <a href='$showUrl' target='_blank' id='generatedLink'>".$showUrl."</a>";
                $this->addFlash('msg',$msg);
                return $this->redirectToRoute('add_link');
            }
        }

        if(count($errors)>0)
            $render = $this->render('ProtectorAppBundle:Default:new-link.html.twig',
                ['formLink'=>$formLink->createView(),'errors'=>$errors]);
        else
            $render = $this->render('ProtectorAppBundle:Default:new-link.html.twig',
                ['formLink'=>$formLink->createView()]);

        return $render;
    }


    /**
     * @Route("/lien/{generatedToken}", name="show_link")
     */
    public function generatedLinkAction(Request $request,$generatedToken)
    {
        $em = $this->getDoctrine()->getManager();
        $generatedLink = $em->getRepository('ProtectorAppBundle:GeneratedLink')->findOneBy(['token'=>$generatedToken]);

        if(!$generatedLink)
            throw $this->createNotFoundException('Lien introuvable');

        $response = new Response();

        if($request->isMethod('POST') && $request->request->has('g-recaptcha-response'))
        {
            $recaptcha = new ReCaptcha('6LckRzQUAAAAAPYt4CrSrFSZD6CToVIhOOgXshAJ');
            $resp = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());
            $response = $this->redirectToRoute('show_link',['generatedToken'=>$generatedLink->getToken()]);
            if($resp->isSuccess())
            {
                $cookie = new Cookie('recaptcha','recaptcha',time()+(3600*4));
                $response->headers->setCookie($cookie);
            }
            else
            {
                $this->addFlash('danger','Vous devez valider le recaptcha pour consulter les liens');
            }

            return $response;
        }

        $response->setContent($this->renderView('ProtectorAppBundle:Default:show-link.html.twig',['generatedLink'=>$generatedLink]));
        return $response;
    }
}
