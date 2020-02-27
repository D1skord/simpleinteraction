<?php


namespace App\Controller;



use App\Entity\Room;
use App\Form\AddRoomFormType;
use App\Form\RegistrationFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class TeacherController extends AbstractController
{
    /**
     * @Route("/teacher", name="teacher")
     */
    public function rooms(Request $request) {
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

        return $this->render('teacher/rooms.html.twig', [
            'addForm' => $form->createView(),
            'rooms' => $rooms
        ]);
    }


}