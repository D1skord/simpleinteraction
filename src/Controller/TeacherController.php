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
        $addRoomForm = $this->createForm(AddRoomFormType::class, $room);
        $addRoomForm->handleRequest($request);
        if ($addRoomForm->isSubmitted() && $addRoomForm->isValid()) {
            $this->getUser()->addRoom($room);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($this->getUser());
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Комната добавлена!'
            );
            return $this->redirectToRoute('teacher_rooms');
        }

        return $this->render('teacher/rooms.html.twig', [
            'addRoomForm' => $addRoomForm->createView(),
            'rooms' => $this->getUser()->getRooms()
        ]);
    }

    /**
     * @Route("/teacher/rooms/{roomId}", name="teacher_room")
     */
    public function room(Request $request, $roomId)
    {
        $room = $this->getUser()->getRoom($roomId);

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

        $addStudentForm = $this->createForm(AddStudentToRoomFormType::class);
        $addStudentForm->handleRequest($request);
        if ($addStudentForm->isSubmitted() && $addStudentForm->isValid()) {

            $student = $this->getDoctrine()->getRepository(Student::class)->findOneBy(['email' => $addStudentForm->get('email')->getData()]);

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
        }

        return $this->render('teacher/room.html.twig', [
            'addTaskForm' => $addTaskForm->createView(),
            'addStudentForm' => $addStudentForm->createView(),
            'tasks' => $room->getTasks(),
            'room' => $room
        ]);
    }

    /**
     * @Route("/teacher/rooms/{roomId}/edit", name="teacher_room_edit")
     */
    public function roomEdit(Request $request, $roomId)
    {
        $room = $this->getUser()->getRoom($roomId);

        $editRoomForm = $this->createForm(AddRoomFormType::class, $room);
        $editRoomForm->handleRequest($request);
        if ($editRoomForm->isSubmitted() && $editRoomForm->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($room);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Комната изменена!'
            );
            return $this->redirectToRoute('teacher_room', [
                'roomId' => $roomId
            ]);
        }

        return $this->render('teacher/room_edit.html.twig', [
            'editRoomForm' => $editRoomForm->createView(),
            'room' => $room
        ]);
    }

    /**
     * @Route("/teacher/rooms/{roomId}/delete", name="teacher_room_delete")
     */
    public function roomDelete($roomId)
    {
        $room = $this->getUser()->getRoom($roomId);

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
        $room = $this->getUser()->getRoom($roomId);
        $task = $room->getTask($taskId);

        return $this->render('teacher/task.html.twig', [
            'room' => $room,
            'task' => $task,

            'students' => $room->getStudents(),
        ]);
    }

    /**
     * @Route("/teacher/rooms/{roomId}/task/{taskId}/edit", name="teacher_task_edit")
     */
    public function taskEdit(Request $request, $roomId, $taskId)
    {
        $room = $this->getUser()->getRoom($roomId);
        $task = $room->getTask($taskId);
        $editTaskForm = $this->createForm(AddTaskFormType::class, $task);
        $editTaskForm->handleRequest($request);
        if ($editTaskForm->isSubmitted() && $editTaskForm->isValid()) {

            $file = $editTaskForm->get('file')->getData();

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
            $entityManager->persist($room);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Задание изменено!'
            );
            return $this->redirectToRoute('teacher_task', [
                'roomId' => $roomId,
                'taskId' => $taskId
            ]);
        }

        return $this->render('teacher/task_edit.html.twig', [
            'editTaskForm' => $editTaskForm->createView(),
            'room' => $room,
            'task' => $task
        ]);
    }

    /**
     * @Route("/teacher/rooms/{roomId}/task/{taskId}/delete", name="teacher_task_delete")
     */
    public function taskDelete($roomId, $taskId)
    {
        $room = $this->getUser()->getRoom($roomId);

        $task = $room->getTask($taskId);

        $room->removeTask($task);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($room);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Задание удалено!'
        );

        return $this->redirectToRoute('teacher_room', [
            'roomId' => $room->getId()
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
            'roomId' => $taskId,
            'taskId' => $taskId,
        ]);
    }
}