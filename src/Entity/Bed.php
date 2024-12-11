<?php

namespace App\Entity;

use App\Repository\BedRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: BedRepository::class)]
class Bed
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["roomJson","bedJson","bookingJson"])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(["bedJson","roomJson","bookingJson"])]
    private ?int $number = null;

    #[ORM\ManyToOne(targetEntity: Room::class, cascade: ['persist'])]
    #[Groups(["bedJson","bookingJson"])]
    private ?Room $room = null;

    /**
     * @var Collection<int, Booking>
     */
    #[ORM\OneToMany(targetEntity: Booking::class, mappedBy: 'bed')]
    private Collection $bookings;

    #[ORM\Column]
    #[Groups(["bedJson"])]
    private ?bool $isBooked = null;

    #[ORM\Column]
    #[Groups(["bedJson"])]
    private ?float $pricePerNight = null;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): static
    {
        $this->room = $room;

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setBed($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getBed() === $this) {
                $booking->setBed(null);
            }
        }

        return $this;
    }

    public function isBooked(): ?bool
    {
        return $this->isBooked;
    }

    public function setBooked(bool $isBooked): static
    {
        $this->isBooked = $isBooked;

        return $this;
    }

    public function getPricePerNight(): ?float
    {
        return $this->pricePerNight;
    }

    public function setPricePerNight(float $pricePerNight): static
    {
        $this->pricePerNight = $pricePerNight;

        return $this;
    }
}
