<?php

namespace GroupBundle\Controller;

use GroupBundle\Entity\Comment;
use GroupBundle\Entity\Group;
use GroupBundle\Entity\Topic;
use GroupBundle\Events;
use GroupBundle\Form\TopicType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Wenming Tang <wenming@cshome.com>
 */
class TopicController extends Controller
{
    /**
     * @Route("/group/{id}/topic/new", name="group_topic_new", requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function newAction(Request $request, Group $group)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $topic = new Topic();
        $topic->setGroup($group);
        $topic->setUser($this->getUser());

        $form = $this->createForm(TopicType::class, $topic);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($topic);
            $entityManager->flush();

            $event = new GenericEvent($topic);

            $this->get('event_dispatcher')->dispatch(Events::TOPIC_CREATED, $event);

            return $this->redirectToRoute('group_topic_show', ['id' => $topic->getId()]);
        }

        return $this->render('GroupBundle:Topic:new.html.twig', [
            'group' => $group,
            'form'  => $form->createView()
        ]);
    }

    /**
     * @Route("/group/topic/{id}/edit", name="group_topic_edit", requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     * @Security("is_granted('edit', topic)")
     */
    public function editAction(Request $request, Topic $topic)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $form = $this->createForm(TopicType::class, $topic);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $topic->setUpdatedAt(new \DateTime());

            $entityManager->persist($topic);
            $entityManager->flush();

            return $this->redirectToRoute('group_topic_show', ['id' => $topic->getId()]);
        }

        return $this->render('GroupBundle:Topic:edit.html.twig', [
            'topic' => $topic,
            'form'  => $form->createView()
        ]);
    }

    /**
     * @Route("/group/topic/{id}/delete", name="group_topic_delete", requirements={"id": "\d+"})
     * @Method("GET")
     * @Security("is_granted('delete', topic)")
     */
    public function deleteAction(Topic $topic)
    {
        $topic->setDeletedAt(new \DateTime());

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($topic);
        $entityManager->flush();

        $event = new GenericEvent($topic);

        $this->get('event_dispatcher')->dispatch(Events::TOPIC_DELETED, $event);

        return $this->redirectToRoute('group_topic', ['id' => $topic->getGroup()->getId()]);
    }

    /**
     * @Route("/group/topic/{id}", name="group_topic_show", requirements={"id": "\d+"})
     * @Method("GET")
     * @Security("is_granted('view', topic)")
     */
    public function showAction(Topic $topic)
    {
        $comments = $this->getDoctrine()->getRepository(Comment::class)->findLatestByTopic($topic);

        return $this->render('GroupBundle:Topic:show.html.twig', [
            'topic'    => $topic,
            'comments' => $comments
        ]);
    }
}