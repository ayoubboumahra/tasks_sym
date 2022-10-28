<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
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
    public function index (Request $request,PaginatorInterface $paginator, EntityManagerInterface $em): Response
    {
        //$user_id = $this->getUser()->getId();

        $user = $this->getUser();

        $q = $request->query->get('q');

        $permission = $this->isGranted('ROLE_ADMIN');

        $query = $em->getRepository(Task::class)
            ->findByUserRole( $user, $q, $permission );
        /*
        if ( $this->isGranted('ROLE_ADMIN') ) {
    
            $query = $em->getRepository(Task::class)
                ->findByUserRole($user_id,$q, $this->isGranted('ROLE_ADMIN'));
        
        } elseif( $this->isGranted("ROLE_SUPER_ADMIN") ) {



        } else {

            $query = $em->getRepository(Task::class)
                ->createQueryBuilder('t')
                ->andWhere('t.assigned_to = :id')
                ->setParameter('id', $user_id)
                ->orderBy("t.id", "DESC")
                ->getQuery();

        }
        */

        $tasks = $paginator->paginate(
            $query,
            $request->query->getInt('page',1),
            5
        );

        //dd($tasks);

        return $this->render("./pages/task/index.html.twig", compact("tasks"));

    }

    /**
     * @Route("/tasks/create", name="task_create", methods={"GET","POST"})
     */
    public function create (Request $request)
    {
        $task = new Task();

        if ( !$this->isGranted('TASK_CREATE', $task) ) {

            $this->addFlash("error", "Sorry but you don't have permission to access this page.");

            return $this->redirectToRoute("app_task_index");

        }

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
    public function show (string $slug, EntityManagerInterface $em)
    {

        $task = $em->getRepository(Task::class)->findOneBy([
            "slug" => $slug
        ]);

        if ( !$this->isGranted('TASK_VIEW', $task) ) {

            $this->addFlash("error", "Sorry but you don't have permission to access this page.");

            return $this->redirectToRoute("app_task_index");

        }

        return $this->render("./pages/task/show.html.twig", compact("task"));

    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit", methods={"GET","PUT"})
     * @return Response
     */
    public function edit (string $id, Request $request, EntityManagerInterface $em): Response
    {
        $task = $em->getRepository(Task::class)->findOneBy([
            "id" => $id
        ]);

        if ( !$this->isGranted('TASK_EDIT', $task) ) {

            $this->addFlash("error", "Sorry but you don't have permission to access this page.");

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
    public function destroy (string $id, Request $request, EntityManagerInterface $em): Response
    {   
        $token = $request->request->get('_token');

        $task = $em->getRepository(Task::class)->findOneBy([
            "id" => $id
        ]);

        if ( !$this->isGranted('TASK_DELETE', $task) ) {

            $this->addFlash("error", "Sorry but you don't have permission to run this action.");

            return $this->redirectToRoute("app_task_index");

        }
       
        if ( $this->isCsrfTokenValid("task_delete_".$task->getId(),$token) ) {

            $em->remove($task);

            $em->flush();

            $this->addFlash("success", "The task is deleted succuessfully !!");
        
        }

        return $this->redirectToRoute("app_task_index");
    }
}