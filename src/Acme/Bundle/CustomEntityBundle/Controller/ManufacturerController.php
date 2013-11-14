<?php                                                                                                                        
namespace Acme\Bundle\CustomEntityBundle\Controller;

use Acme\Bundle\CustomEntityBundle\Model\Manufacturer;
use Acme\Bundle\CustomEntityBundle\Form\Type\ManufacturerType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Route("/manufacturer")
 */
class ManufacturerController extends Controller
{
    /**
     * @Route(
     *     "/.{_format}",
     *     requirements={"_format" = "html|json"},
     *     defaults={"_format" = "html"}
     * )
    */
    public function indexAction(Request $request)
    {
        $repository = $this->get('acme_custom_entity.repository.manufacturer');
        $manufacturers = $repository->findAll();

        $view = 'AcmeCustomEntityBundle:Manufacturer:index.html.twig';

        return $this->render($view, array('manufacturers' => $manufacturers));
    }

   /**
     * @Route("/create")
     * @Template("AcmeCustomEntityBundle:Manufacturer:edit.html.twig")
     */
    public function createAction()
    {
        return $this->editAction(new Manufacturer());
    }

    /**
     * @Route(
     *     "/edit/{id}",
     *     requirements={"id"="\d+"},
     *     defaults={"id"=0}
     * )
     * @Template("AcmeCustomEntityBundle:Manufacturer:edit.html.twig")
     */
    public function editAction(Manufacturer $manufacturer)
    {
        $formType = new ManufacturerType();
        $form = $this->createForm($formType, $manufacturer);

        if ($this->getRequest()->isMethod('POST')) {
            $form->bind($this->getRequest());

            if ($form->isValid()) {
                $entityManager = $this->get('acme_custom_entity.manager.manufacturer');
                $entityManager->persist($manufacturer);
                $entityManager->flush();

                $this->get('session')->getFlashBag()->add('success', 'Manufacturer successfully saved');

                return $this->redirect($this->generateUrl('acme_customentity_manufacturer_index'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Method({"delete"})
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     */
    public function removeAction(Manufacturer $manufacturer)
    {
        $entityManager = $this->get('acme_custom_entity.manager.manufacturer');

        $entityManager->remove($manufacturer);
        $entityManager->flush();

        $this->get('session')->getFlashBag()->add('success', 'Manufacturer successfully removed');

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirect($this->generateUrl('acme_customentity_manufacturer_index'));
        }
    }


}
