<?php

namespace Pumukit\NewAdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_ACCESS_MULTIMEDIA_SERIES')")
 */
class ManualController extends Controller implements NewAdminControllerInterface
{
    /**
     * @Route("/manual")
     * @Template("PumukitNewAdminBundle:Manual:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        return [];
    }
}
