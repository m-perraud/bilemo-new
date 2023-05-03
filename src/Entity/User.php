<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['Username'], message: 'Cet username est déjà utilisé.')]
#[UniqueEntity(fields: ['Email'], message: 'Cette adresse email est déjà utilisée.')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique : true)]
    #[Groups(['client:list', 'client:details'])]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Le username doit faire au moins {{ limit }} caractères.',
        maxMessage: 'Le username doit faire maximum {{ limit }} caractères.',
    )]
    private ?string $Username = null;

    #[ORM\ManyToOne(inversedBy: 'users', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['client:list', 'client:details'])]
    #[Assert\NotBlank]
    private ?Client $Client = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Le nom doit faire au moins {{ limit }} caractères.',
        maxMessage: 'Le nom doit faire maximum {{ limit }} caractères.',
    )]
    #[Groups(['client:details'])]
    private ?string $Firstname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Le prénom doit faire au moins {{ limit }} caractères.',
        maxMessage: 'Le prénom doit faire maximum {{ limit }} caractères.',
    )]
    #[Groups(['client:details'])]
    private ?string $Lastname = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email(
        message: 'L\'email n\'est pas valide.',
    )]
    #[Groups(['client:details'])]
    private ?string $Email = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->Username;
    }

    public function setUsername(string $Username): self
    {
        $this->Username = $Username;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->Client;
    }

    public function setClient(?Client $Client): self
    {
        $this->Client = $Client;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->Firstname;
    }

    public function setFirstname(string $Firstname): self
    {
        $this->Firstname = $Firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->Lastname;
    }

    public function setLastname(string $Lastname): self
    {
        $this->Lastname = $Lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(string $Email): self
    {
        $this->Email = $Email;

        return $this;
    }
}
