<?php

namespace App\Entity;


use DateTime;
use App\Entity\User;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AnnonceRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=AnnonceRepository::class)
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(
 *  fields={"title"},
 *  message="Une autre annonce possède déjà ce titre, merci de le modifier"
 * )
 */
class Annonce
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Assert\Length(min=10, max=255, minMessage="Le titre doit faire plus de 10 caractères !", maxMessage="Le titre ne peut pas faire plus de 255 caractères")
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(min=20, minMessage="Votre introduction doit faire plus de 20 caractères !")
     */
    private $introduction;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(min=100, minMessage="Votre description doit faire plus de 100 caractères !")
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Url()
     */
    private $coverImage;

    /**
     * @ORM\Column(type="integer")
     */
    private $rooms;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $street;

    /**
     * @ORM\Column(type="string")
     */
    private $zip;

    /**
     * @ORM\Column(type="decimal", scale=6, precision=8)
     */
    private $lat;

    /**
     * @ORM\Column(type="decimal", scale=6, precision=8)
     */
    private $lon;

    /**
     * @ORM\OneToMany(targetEntity=Image::class, mappedBy="annonce", orphanRemoval=true)
     * @Assert\Valid()
     */
    private $images;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="annonces")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity=Booking::class, mappedBy="annonce")
     */
    private $bookings;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="annonce", orphanRemoval=true)
     */
    private $comments;

    

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->bookings = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
    * Permet d'initialiser le slug
    * @ORM\PrePersist
    * @ORM\PreUpdate
    * @return void
    */
    public function initializeSlug()
    {
        if (empty($this->slug)) {
            $slugify = new Slugify();
            $this->slug = $slugify->slugify($this->title);
        }
    }

    /**
     * Permet de récupérer le commentaire d'un auteur par rapport à une annonce
     * 
     * 
     *
     * @param User $author
     * @return Comment|null
     */
    public function getCommentFromAuthor(User $author)
    {
        foreach ($this->comments as $comment) {
            if ($comment->getAuthor() === $author) {
                return $comment;
            }
        }
        return null;
    }

    /**
     * Permet d'obtenir la moyenne globale des notes pour cette annonce
     *
     * @return float
     */
    public function getAvgRatings()
    {
        //Calculer la somme des notes
        // la fonction array_reduce () envoie les valeurs d'un tableau à une fonction définie par l'utilisateur et renvoie une chaîne.
        $sum = \array_reduce($this->comments->toArray(), function($total, $comment){
            return $total + $comment->getRating();
        }, 0);

        // Faire la division pour avoir la moyenne
        if(count($this->comments) > 0) return $sum / count($this->comments);

        return 0;
    }

    /**
     * Permet d'obtenir un tableau des jours qui ne sont pas disponibles pour cette annonce
     *
     * @return array Un tableau d'objets Datetime représentant les jours d'occupation
     */
    public function getNotAvailableDays()
    {
        $notAvailableDays = [];

        foreach ($this->bookings as $booking) {
            // Calculer les jours qui se trouvent entre la date d'arrivée et de départ
            $resultat = range($booking->getStartDate()->getTimestamp(),
                                $booking->getEndDate()->getTimestamp(),
                                24 * 60 * 60
            );

            $days = array_map(function($dayTimestamp) {
                return new \DateTime(date('Y-m-d', $dayTimestamp));
            }, $resultat);

            $notAvailableDays = array_merge($notAvailableDays, $days);
        }
        return $notAvailableDays;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(string $introduction): self
    {
        $this->introduction = $introduction;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(string $coverImage): self
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function getRooms(): ?int
    {
        return $this->rooms;
    }

    public function setRooms(int $rooms): self
    {
        $this->rooms = $rooms;

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setAnnonce($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getAnnonce() === $this) {
                $image->setAnnonce(null);
            }
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|Booking[]
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings[] = $booking;
            $booking->setAnnonce($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->contains($booking)) {
            $this->bookings->removeElement($booking);
            // set the owning side to null (unless already changed)
            if ($booking->getAnnonce() === $this) {
                $booking->setAnnonce(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAnnonce($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAnnonce() === $this) {
                $comment->setAnnonce(null);
            }
        }

        return $this;
    }

    
}
