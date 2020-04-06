<?php
/**
 * Created by PhpStorm.
 * User: Azamat
 * Date: 28.02.2020
 * Time: 14:57
 */

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @Entity
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class Teacher extends User
{

  /**
   * @ORM\OneToMany(targetEntity="Room", mappedBy="teacher")
   */
  private $rooms;

  public function __construct()
  {
      $this->rooms = new ArrayCollection();
  }

  /**
   * @return Collection|Room[]
   */
  public function getRooms(): Collection
  {
      return $this->rooms;
  }

  public function getRoom($roomId)
    {
        foreach ($this->rooms as $room) {
            if ($room->getId() == $roomId) {
                return $room;
            }
        }

        return null;
    }

  public function addRoom(Room $room): self
  {
      if (!$this->rooms->contains($room)) {
          $this->rooms[] = $room;
          $room->setTeacher($this);
      }

      return $this;
  }

  public function removeRoom(Room $room): self
  {
      if ($this->rooms->contains($room)) {
          $this->rooms->removeElement($room);
          // set the owning side to null (unless already changed)
          if ($room->getTeacher() === $this) {
              $room->setTeacher(null);
          }
      }

      return $this;
  }





}