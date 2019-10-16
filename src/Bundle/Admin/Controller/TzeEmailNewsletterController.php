<?php

namespace App\Bundle\Admin\Controller;

use App\Shared\Entity\TzeEmailNewsletter;
use App\Shared\Form\TzeEmailNewsletterType;
use App\Shared\Services\Utils\ServiceName;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TzeEmailNewsletterController.
 */
class TzeEmailNewsletterController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        // Récupérer manager
        $_email_manager = $this->get(ServiceName::SRV_METIER_EMAIL_NEWSLETTER);
        // Récupérer tout les emails
        $_emails = $_email_manager->getAllEmailNewsletter();

        return $this->render(
            'AdminBundle:TzeEmailNewsletter:index.html.twig', 
            array(
                'email_newsletters' => $_emails,
            )
        );
    }

    /**
     * @param TzeEmailNewsletter $_email
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(TzeEmailNewsletter $_email)
    {
        if (!$_email) {
            throw $this->createNotFoundException('Unable to find TzeEmailNewsletter entity.');
        }

        $_etze_form = $this->createEditForm($_email);

        return $this->render(
            'AdminBundle:TzeEmailNewsletter:edit.html.twig', 
            array(
                'email_newsletter' => $_email,
                'etze_form' => $_etze_form->createView(),
            )
        );
    }

    /**
     * @param Request $_request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function newAction(Request $_request)
    {
        // Récupérer manager
        $_email_manager = $this->get(ServiceName::SRV_METIER_EMAIL_NEWSLETTER);

        $_email = new TzeEmailNewsletter();
        $_form = $this->createCreateForm($_email);
        $_form->handleRequest($_request);

        if ($_form->isSubmitted() && $_form->isValid()) {
            // Enregistrement email
            $_email_manager->saveEmailNewsletter($_email, 'new');

            $_email_manager->setFlash('success', 'Email abonné ajouté');

            return $this->redirect($this->generateUrl('email_newsletter_index'));
        }

        return $this->render(
            'AdminBundle:TzeEmailNewsletter:add.html.twig', 
            array(
                'email_newsletter' => $_email,
                'form' => $_form->createView(),
            )
        );
    }

    /**
     * @param Request            $_request
     * @param TzeEmailNewsletter $_email
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateAction(Request $_request, TzeEmailNewsletter $_email)
    {
        // Récupérer manager
        $_email_manager = $this->get(ServiceName::SRV_METIER_EMAIL_NEWSLETTER);

        if (!$_email) {
            throw $this->createNotFoundException('Unable to find TzeEmailNewsletter entity.');
        }

        $_etze_form = $this->createEditForm($_email);
        $_etze_form->handleRequest($_request);

        if ($_etze_form->isValid()) {
            $_email_manager->saveEmailNewsletter($_email, 'update');

            $_email_manager->setFlash('success', 'Email abonné modifié');

            return $this->redirect($this->generateUrl('email_newsletter_index'));
        }

        return $this->render(
            'AdminBundle:TzeEmailNewsletter:edit.html.twig', 
            array(
                'email_newsletter' => $_email,
                'etze_form' => $_etze_form->createView(),
            )
        );
    }

    /**
     * Création formulaire d'édition email.
     *
     * @param TzeEmailNewsletter $_email The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(TzeEmailNewsletter $_email)
    {
        $_form = $this->createForm(
            TzeEmailNewsletterType::class, 
            $_email, array(
                'action' => $this->generateUrl('email_newsletter_new'),
                'method' => 'POST',
            )
        );

        return $_form;
    }

    /**
     * Création formulaire de création email.
     *
     * @param TzeEmailNewsletter $_email The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(TzeEmailNewsletter $_email)
    {
        $_form = $this->createForm(TzeEmailNewsletterType::class, $_email, array(
            'action' => $this->generateUrl('email_newsletter_update', array('id' => $_email->getId())),
            'method' => 'PUT',
        ));

        return $_form;
    }

    /**
     * @param Request            $_request
     * @param TzeEmailNewsletter $_email
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteAction(Request $_request, TzeEmailNewsletter $_email)
    {
        // Récupérer manager
        $_email_manager = $this->get(ServiceName::SRV_METIER_EMAIL_NEWSLETTER);

        $_form = $this->createDeleteForm($_email);
        $_form->handleRequest($_request);

        if ($_request->isMethod('GET') || ($_form->isSubmitted() && $_form->isValid())) {
            // Suppression email
            $_email_manager->deleteEmailNewsletter($_email);

            $_email_manager->setFlash('success', 'Email abonné supprimé');
        }

        return $this->redirectToRoute('email_newsletter_index');
    }

    /**
     * @param TzeEmailNewsletter $_email
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createDeleteForm(TzeEmailNewsletter $_email)
    {
        return $this->createFormBuilder()
                    ->setAction($this->generateUrl('email_newsletter_delete', array('id' => $_email->getId())))
                    ->setMethod('DELETE')
                    ->getForm();
    }

    /**
     * @param Request $_request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteGroupAction(Request $_request)
    {
        // Récupérer manager
        $_email_manager = $this->get(ServiceName::SRV_METIER_EMAIL_NEWSLETTER);

        if (null !== $_request->request->get('_group_delete')) {
            $_ids = $_request->request->get('delete');
            if (null == $_ids) {
                $_email_manager->setFlash('error', 'Veuillez sélectionner un élément à supprimer');

                return $this->redirect($this->generateUrl('email_newsletter_index'));
            }
            $_email_manager->deleteGroupEmailNewsletter($_ids);
        }

        $_email_manager->setFlash('success', 'Eléments sélectionnés supprimés');

        return $this->redirect($this->generateUrl('email_newsletter_index'));
    }
}
