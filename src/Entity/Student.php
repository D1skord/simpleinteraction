<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @Entity
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class Student extends User
{

    /**
     * @ManyToMany(targetEntity="Room", inversedBy="students")
     */
    private $rooms;

    /**
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="student", cascade={"persist"})
     */
    private $answers;

    public function __construct()
    {
        $this->rooms = new ArrayCollection();
        $this->answers = new ArrayCollection();
    }

    /**
     * @return Collection|Room[]
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): self
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms[] = $room;
        }

        return $this;
    }

    public function removeRoom(Room $room): self
    {
        if ($this->rooms->contains($room)) {
            $this->rooms->removeElement($room);
        }

        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function getAnswer($taskId)
    {
       $answers =  $this->getAnswers();

       foreach ($answers as $answer) {
           if ($answer->getTask()->getId() == $taskId && $answer->getStudent()->getId() == $this->getId()) {
               return $answer;
           }
       }

        return null;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setStudent($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
            // set the owning side to null (unless already changed)
            if ($answer->getStudent() === $this) {
                $answer->setStudent(null);
            }
        }

        return $this;
    }


}