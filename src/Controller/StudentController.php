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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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

        if (empty($answer = $this->getUser()->getAnswer($taskId))) {
            $answer = new Answer();
        }

        $addAnswerForm = $this->createForm(AddAnswerFormType::class, $answer);
        $addAnswerForm->handleRequest($request);
        if ($addAnswerForm->isSubmitted() && $addAnswerForm->isValid()) {

            $answer = $addAnswerForm->getData();
            $answer->setTask($task);

            $file = $addAnswerForm->get('file')->getData();

            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = urlencode ($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('files_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $answer->setFile($newFilename);
            }

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
            'addAnswerForm' => $addAnswerForm->createView(),
        ]);
    }



}