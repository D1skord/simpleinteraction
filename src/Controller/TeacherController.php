<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\Teacher;
use App\Form\AddRoomFormType;
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

      //$rooms = $this->getDoctrine()->getRepository(Room::class)->findAll();
      $rooms = $this->getUser()->getRooms();

    //$rooms = $roomRepository->findByUserId($this->getUser()->getId());




    $form = $this->createForm(AddRoomFormType::class, $room);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      // encode the plain password
      $room->setTeacher($this->getUser());
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
   * @Route("/teacher/rooms/{roomId}/tasks", name="teacher_rooms_tasks")
   */
  public function room(Request $request)
  {

    $room = new Room();

    $roomRepository = $this->getDoctrine()->getRepository(Room::class);

    $rooms = $roomRepository->findByUserId($this->getUser()->getId());


    $form = $this->createForm(AddRoomFormType::class, $room);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      // encode the plain password

      $room->setUser($this->getUser());
      $room = $form->getData();

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($room);
      $entityManager->flush();


    }

    return $this->render('rooms.html.twig', [
      'addForm' => $form->createView(),
      'rooms' => $rooms
    ]);
  }

  /**
   * @Route("/teacher/rooms/{roomId}/delete", name="teacher_rooms_delete")
   */
  public function roomDelete($roomId)
  {
    $roomRepository = $this->getDoctrine()->getRepository(Room::class);

    $room = $roomRepository->findOneBy([
      'id' => $roomId
    ]);

    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->remove($room);
    $entityManager->flush();

    $this->addFlash(
      'success',
      'Комната удалена!'
    );

    return $this->redirectToRoute('teacher_rooms');
  }

}