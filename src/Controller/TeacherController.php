<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Room;
use App\Entity\Student;
use App\Entity\Task;
use App\Entity\Teacher;
use App\Form\AddAnswerFormType;
use App\Form\AddMarkFormType;
use App\Form\AddRoomFormType;
use App\Form\AddStudentToRoomFormType;
use App\Form\AddTaskFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Transliterator;


class TeacherController extends AbstractController
{
    /**
     * @Route("/teacher/rooms", name="teacher_rooms")
     */
    public function rooms(Request $request)
    {

        $room = new Room();

        $rooms = $this->getUser()->getRooms();

        $addRoomForm = $this->createForm(AddRoomFormType::class, $room);
        $addRoomForm->handleRequest($request);
        if ($addRoomForm->isSubmitted() && $addRoomForm->isValid()) {
            // encode the plain password
            //$room->setTeacher($this->getUser());
            $this->getUser()->addRoom($room);
            $room = $addRoomForm->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($room);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Комната добавлена!'
            );
            return $this->redirectToRoute('teacher_rooms');
        }

        return $this->render('teacher/rooms.html.twig', [
            'addRoomForm' => $addRoomForm->createView(),
            'rooms' => $rooms
        ]);
    }

    /**
     * @Route("/teacher/rooms/{roomId}", name="teacher_room")
     */
    public function room(Request $request, $roomId)
    {
        $room = $this->getDoctrine()->getRepository(Room::class)->findOneBy(['id' => $roomId]);
        $tasks = $room->getTasks();

        $task = new Task();

        $addTaskForm = $this->createForm(AddTaskFormType::class, $task);
        $addTaskForm->handleRequest($request);
        if ($addTaskForm->isSubmitted() && $addTaskForm->isValid()) {
            $task->setRoom($room);
            $task = $addTaskForm->getData();

            $file = $addTaskForm->get('file')->getData();

            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = urlencode ($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                // Move the file to the directory where brochures are stored
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
                $task->setFile($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();
        }

        return $this->render('teacher/room.html.twig', [
            'addTaskForm' => $addTaskForm->createView(),
            'tasks' => $tasks,
            'room' => $room
        ]);
    }

    /**
     * @Route("/teacher/rooms/{roomId}/delete", name="teacher_room_delete")
     */
    public function roomDelete($roomId)
    {
        $room = $this->getDoctrine()->getRepository(Room::class)->findOneBy([
            'id' => $roomId
        ]);


        $this->getUser()->removeRoom($room);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($room);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Комната удалена!'
        );

        return $this->redirectToRoute('teacher_rooms');
    }


    /**
     * @Route("/teacher/rooms/{roomId}/task/{taskId}", name="teacher_task")
     */
    public function task(Request $request, $roomId, $taskId)
    {
        $task = $this->getDoctrine()->getRepository(Task::class)->findOneBy(['id' => $taskId]);
        $room = $this->getDoctrine()->getRepository(Room::class)->findOneBy(['id' => $roomId]);


        $form = $this->createForm(AddStudentToRoomFormType::class);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $student = $this->getDoctrine()->getRepository(Student::class)->findOneBy(['email' => $form->get('email')->getData()]);

            if (!empty($student)) {
                $room = $this->getDoctrine()->getRepository(Room::class)->findOneBy(['id' => $roomId]);

                $room->addStudent($student);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($room);
                $entityManager->flush();

                $this->addFlash(
                    'success',
                    'Студент добавлен!'
                );
            } else {
                $this->addFlash(
                    'danger',
                    'Студент не найден!'
                );
            }

            return $this->redirectToRoute('teacher_rooms_tasks_task', [
                'roomId' => $roomId,
                'taskId' => $taskId,
            ]);
        }




        return $this->render('teacher/task.html.twig', [
            'room' => $room,
            'task' => $task,
            'addStudentForm' => $form->createView(),
            'students' => $room->getStudents(),
        ]);
    }


    /**
     * @Route("/teacher/add-mark", name="teacher_add_mark")
     */
    public function AddMark(Request $request)
    {
        $taskId = $request->request->get('taskId');
        $studentId =  $request->request->get('studentId');
        $mark =  $request->request->get('mark');

        dump($studentId);

        $task = $this->getDoctrine()->getRepository(Task::class)->findOneBy(['id' => $taskId]);


        $student = $this->getDoctrine()->getRepository(Student::class)->findOneBy(['id' => $studentId]);
        $answer = $student->getAnswer($taskId);
        $answer->setMark($mark);



        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($answer);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Оценка сохранена!'
        );


        return $this->redirectToRoute('teacher_task', [
            'roomId' => $task->getRoom()->getId(),
            'taskId' => $task->getId(),
        ]);
    }
}