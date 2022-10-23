<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("app", name="app_")
 */
class TaskController extends AbstractController
{

    /**
     * @Route("/tasks", name="task_index", methods={"GET"})
     * @return Response
     */
    public function index (): Response
    {
        $em = $this->getDoctrine();

        $tasks = $em->getRepository(Task::class)->findBy([
            "created_by" => $this->getUser()
        ]);

        return $this->render("./pages/task/index.html.twig", compact("tasks"));

    }

    /**
     * @Route("/tasks/create", name="task_create", methods={"GET","POST"})
     */
    public function create (Request $request)
    {
        $task = new Task();

        $form = $this->createForm(TaskType::class, $task, [
            "method" => "POST",
            "action" => $this->generateUrl("app_task_create")
        ]);

        $form->handleRequest($request);

        if ( $form->isSubmitted() and $form->isValid() ) {

            $task->setCreatedBy($this->getUser());

            $d = $this->getDoctrine();

            $em = $d->getManager();

            $em->persist($task);

            $em->flush();

            $this->addFlash("success","The task is created successfully !!");

            return $this->redirectToRoute("app_task_create");

        }

        return $this->render("./pages/task/create.html.twig", [
            "task_form" => $form->createView()
        ]);

    }

    /**
     * @Route("/tasks/{slug}", name="task_show", methods={"GET"})
     * @return Response
     */
    public function show (string $slug)
    {
        $doctrine = $this->getDoctrine();

        $task = $doctrine->getRepository(Task::class)->findOneBy([
            "slug" => $slug
        ]);

        if  ( is_null( $task ) ) {

            $this->addFlash("error","Sorry, but the task does not exists.");

            return $this->redirectToRoute("app_task_index");

        }

        return $this->render("./pages/task/show.html.twig", compact("task"));

    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit", methods={"GET","PUT"})
     * @return Response
     */
    public function edit (string $id, Request $request): Response
    {
        $doctrine = $this->getDoctrine();

        $task = $doctrine->getRepository(Task::class)->findOneBy([
            "id" => $id
        ]);

        if  ( is_null( $task ) ) {

            $this->addFlash("error","Sorry, but the task does not exists.");

            return $this->redirectToRoute("app_task_index");

        }

        $form = $this->createForm(TaskType::class, $task, [
            "method" => "PUT",
            "action" => $this->generateUrl("app_task_edit", [
                "id" => $task->getId()
            ])
        ]);

        $form->handleRequest($request);

        if ( $form->isSubmitted() and $form->isValid() ) {

            $task->setCreatedBy($this->getUser());

            $d = $this->getDoctrine();

            $em = $d->getManager();

            $em->persist($task);

            $em->flush();

            $this->addFlash("success","The task is updated successfully !!");

            return $this->redirectToRoute("app_task_edit", [
                "id" => $task->getId()
            ]);

        }

        return $this->render("./pages/task/edit.html.twig", [
            "task_form" => $form->createView()
        ]);


    }

    /**
     * @Route("/tasks/{id}", name="task_destroy", methods={"DELETE"})
     * return Response
     */
    public function destroy (string $id, Request $request): Response
    {
        
        $doctrine = $this->getDoctrine();

        $token = $request->request->get('_token');

        $task = $doctrine->getRepository(Task::class)->findOneBy([
            "id" => $id
        ]);

        if  ( is_null( $task ) ) {

            $this->addFlash("error","Sorry, but the task does not exists.");

            return $this->redirectToRoute("app_task_index");

        }
       
        if ( $this->isCsrfTokenValid("task_delete_".$task->getId(),$token) ) {

            $em = $doctrine->getManager();

            $em->remove($task);

            $em->flush();

            $this->addFlash("success", "The task is deleted succuessfully !!");
        
        }

        return $this->redirectToRoute("app_task_index");
    }
}