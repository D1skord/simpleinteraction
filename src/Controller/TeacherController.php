<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\Student;
use App\Entity\Task;
use App\Entity\Teacher;
use App\Form\AddRoomFormType;
use App\Form\AddStudentToRoomFormType;
use App\Form\AddTaskFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class TeacherController extends AbstractController
{
    /**
     * @Route("/teacher/rooms", name="teacher_rooms")
     */
    public function rooms(Request $request)
    {

        $room = new Room();

        $rooms = $this->getUser()->getRooms();

        $form = $this->createForm(AddRoomFormType::class, $room);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            //$room->setTeacher($this->getUser());
            $this->getUser()->addRoom($room);
            $room = $form->getData();

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
            'addForm' => $form->createView(),
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

        $form = $this->createForm(AddTaskFormType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task->setRoom($room);
            $task = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();
        }

        return $this->render('teacher/room.html.twig', [
            'addForm' => $form->createView(),
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
            'task' => $task,
            'addStudentForm' => $form->createView(),
            'students' => $room->getStudents()
        ]);
    }

    public function inviteToRoom($roomId, $studentEmail)
    {


        return $this->redirectToRoute('teacher_rooms');
    }
}