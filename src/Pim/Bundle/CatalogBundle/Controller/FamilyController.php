<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Exception\DeleteException;
use Pim\Bundle\CatalogBundle\Factory\FamilyFactory;
use Pim\Bundle\CatalogBundle\Form\Handler\FamilyHandler;
use Pim\Bundle\CatalogBundle\Form\Type\AvailableProductAttributesType;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\AvailableProductAttributes;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\GridBundle\Helper\DatagridHelperInterface;

/**
 * Family controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyController extends AbstractDoctrineController
{
    /**
     * @var DatagridHelperInterface
     */
    private $datagridHelper;

    /**
     * @var ChannelManager
     */
    private $channelManager;

    /**
     * @var FamilyFactory
     */
    private $factory;

    /**
     * @var CompletenessManager
     */
    private $completenessManager;

    /**
     * @var FamilyHandler
     */
    protected $familyHandler;

    /**
     * @var Form
     */
    protected $familyForm;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param RegistryInterface        $doctrine
     * @param DatagridHelperInterface  $datagridHelper
     * @param ChannelManager           $channelManager
     * @param FamilyFactory            $factory
     * @param CompletenessManager      $completenessManager
     * @param FamilyHandler            $familyHandler
     * @param Form                     $familyForm
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        RegistryInterface $doctrine,
        DatagridHelperInterface $datagridHelper,
        ChannelManager $channelManager,
        FamilyFactory $factory,
        CompletenessManager $completenessManager,
        FamilyHandler $familyHandler,
        Form $familyForm
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $doctrine
        );

        $this->datagridHelper      = $datagridHelper;
        $this->channelManager      = $channelManager;
        $this->factory             = $factory;
        $this->completenessManager = $completenessManager;
        $this->familyHandler       = $familyHandler;
        $this->familyForm          = $familyForm;
    }

    /**
     * List families
     *
     * @Template
     * @AclAncestor("pim_catalog_family_index")
     * @return Response
     */
    public function indexAction()
    {
        /** @var $queryBuilder QueryBuilder */
        $queryBuilder = $this->getManager()->createQueryBuilder();

        $datagridView = $this->datagridHelper->getDatagrid('family', $queryBuilder)->createView();

        if ('json' === $this->getRequest()->getRequestFormat()) {
            return $this->datagridHelper->getDatagridRenderer()->renderResultsJsonResponse($datagridView);
        }

        return array(
            'datagrid' => $datagridView
        );
    }

    /**
     * Create a family
     *
     * @Template
     * @AclAncestor("pim_catalog_family_create")
     * @return array
     */
    public function createAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_catalog_family_index');
        }

        $family = $this->factory->createFamily();

        if ($this->familyHandler->process($family)) {
            $this->addFlash('success', 'flash.family.created');

            $response = array(
                'status' => 1,
                'url'    => $this->generateUrl('pim_catalog_family_edit', array('id' => $family->getId()))
            );

            return new Response(json_encode($response));
        }

        return array(
            'form' => $this->familyForm->createView()
        );
    }

    /**
     * Edit a family
     *
     * @param Family $family
     *
     * @Template
     * @AclAncestor("pim_catalog_family_edit")
     * @return array
     */
    public function editAction(Family $family)
    {
        if ($this->familyHandler->process($family)) {
            $this->addFlash('success', 'flash.family.updated');
        }

        return array(
            'form'            => $this->familyForm->createView(),
            'attributesForm'  => $this->getAvailableProductAttributesForm(
                $family->getAttributes()->toArray()
            )->createView(),
            'channels' => $this->channelManager->getChannels()
        );
    }

    /**
     * History of a family
     *
     * @param Family $family
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|template
     */
    public function historyAction(Family $family)
    {
        $historyGridView = $this->getHistoryGrid($family)->createView();

        if ('json' === $this->getRequest()->getRequestFormat()) {
            return $this->datagridHelper->getDatagridRenderer()->renderResultsJsonResponse($historyGridView);
        } else {
            return $this->render(
                'PimCatalogBundle:Family:_history.html.twig',
                array(
                    'historyDatagrid'   => $historyGridView
                )
            );
        }
    }

    /**
     * Remove a family
     *
     * @param Family $family
     *
     * @AclAncestor("pim_catalog_family_remove")
     * @return Response|RedirectResponse
     */
    public function removeAction(Family $family)
    {
        $this->getManager()->remove($family);
        $this->getManager()->flush();

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_catalog_family_index');
        }
    }

    /**
     * Add attributes to a family
     *
     * @param Family $family
     *
     * @AclAncestor("pim_catalog_family_add_attribute")
     * @return Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addProductAttributesAction(Family $family)
    {
        $availableAttributes = new AvailableProductAttributes();
        $attributesForm      = $this->getAvailableProductAttributesForm(
            $family->getAttributes()->toArray(),
            $availableAttributes
        );

        $attributesForm->submit($this->getRequest());

        foreach ($availableAttributes->getAttributes() as $attribute) {
            $family->addAttribute($attribute);
        }

        $this->getManager()->flush();

        $this->addFlash('success', 'flash.family.attributes added');

        return $this->redirectToRoute('pim_catalog_family_edit', array('id' => $family->getId()));
    }

    /**
     * Remove product attribute
     *
     * @param integer $familyId
     * @param integer $attributeId
     *
     * @AclAncestor("pim_catalog_family_remove_atribute")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws DeleteException
     */
    public function removeProductAttributeAction($familyId, $attributeId)
    {
        $family    = $this->findOr404('PimCatalogBundle:Family', $familyId);
        $attribute = $this->findOr404('PimCatalogBundle:ProductAttributeInterface', $attributeId);

        if (false === $family->hasAttribute($attribute)) {
            throw new DeleteException($this->getTranslator()->trans('flash.family.attribute not found'));
        } elseif ($attribute->getAttributeType() === 'pim_catalog_identifier') {
            throw new DeleteException($this->getTranslator()->trans('flash.family.identifier not removable'));
        } elseif ($attribute === $family->getAttributeAsLabel()) {
            throw new DeleteException($this->getTranslator()->trans('flash.family.label attribute not removable'));
        } else {
            $family->removeAttribute($attribute);
            $this->getManager()->flush();
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_catalog_family_edit', array('id' => $family->getId()));
        }
    }

    /**
     * Get the AvailbleProductAttributeInterfaces form
     *
     * @param array                      $attributes          The product attributes
     * @param AvailableProductAttributes $availableAttributes The available attributes container
     *
     * @return Symfony\Component\Form\Form
     */
    protected function getAvailableProductAttributesForm(
        array $attributes = array(),
        AvailableProductAttributes $availableAttributes = null
    ) {
        return $this->createForm(
            new AvailableProductAttributesType(),
            $availableAttributes ?: new AvailableProductAttributes(),
            array('attributes' => $attributes)
        );
    }

    /**
     * @param Family $family
     *
     * @return Datagrid
     */
    protected function getHistoryGrid(Family $family)
    {
        $historyGrid = $this->datagridHelper->getDataAuditDatagrid(
            $family,
            'pim_catalog_family_history',
            array('id' => $family->getId())
        );

        return $historyGrid;
    }
}
