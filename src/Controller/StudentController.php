<?php


namespace App\Controller;


use App\Entity\Answer;
use App\Entity\Room;
use App\Entity\Student;
use App\Entity\Task;
use App\Form\AddAnswerFormType;
use App\Form\AddRoomFormType;
use App\Form\AddStudentToRoomFormType;
use App\Form\AddTaskFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StudentController extends AbstractController
{
    /**
     * @Route("/student/rooms", name="student_rooms")
     */
    public function rooms(Request $request)
    {

        $room = new Room();

        $rooms = $this->getUser()->getRooms();

        return $this->render('student/rooms.html.twig', [
            'rooms' => $rooms
        ]);
    }

    /**
     * @Route("/student/rooms/{roomId}", name="student_room")
     */
    public function room(Request $request, $roomId)
    {
        $room = $this->getDoctrine()->getRepository(Room::class)->findOneBy(['id' => $roomId]);
        $tasks = $room->getTasks();


        return $this->render('student/room.html.twig', [
            'tasks' => $tasks,
            'room' => $room
        ]);
    }

    /**
     * @Route("/student/rooms/{roomId}/task/{taskId}", name="student_task")
     */
    public function task(Request $request, $roomId, $taskId)
    {
        $task = $this->getDoctrine()->getRepository(Task::class)->findOneBy(['id' => $taskId]);
       // $room = $this->getDoctrine()->getRepository(Room::class)->findOneBy(['id' => $roomId]);


        $answer = $this->getUser()->getAnswer($taskId);

        if (empty($answer)) {
            $answer = new Answer();
        }

        $form = $this->createForm(AddAnswerFormType::class, $answer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $answer = $form->getData();
            $answer->setTask($task);

            $this->getUser()->addAnswer($answer);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($this->getUser());
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Ответ добавлен!'
            );
        }



        return $this->render('student/task.html.twig', [
            'task' => $task,
            'answer' => $answer,
            'addAnswerStudentForm' => $form->createView(),
        ]);
    }

}