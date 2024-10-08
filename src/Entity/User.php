<?php
namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Controller\UserInfoController;
use App\Repository\UserRepository;
use App\State\UserPasswordHasher;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(processor: UserPasswordHasher::class, validationContext: ['groups' => ['user:write']], security: "is_granted('PUBLIC_ACCESS')"),
        new Get(),
        new Put(processor: UserPasswordHasher::class),
        new Patch(processor: UserPasswordHasher::class, validationContext: ['groups' => ['user:write']]),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
)]
#[ApiFilter(PropertyFilter::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['username'], message: 'It looks like another dragon took your username. ROAR!')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['user:read', 'user:write'])]
    #[ApiFilter(SearchFilter::class, strategy: 'partial')]
    #[Assert\NotBlank]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[Groups(['user:write'])]
    #[Assert\NotBlank]
    private ?string $plainPassword = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PadelGame::class)]
    private Collection $padelGames;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: TenisGame::class)]
    private Collection $tenisGames;

    public function __construct()
    {
        $this->padelGames = new ArrayCollection();
        $this->tenisGames = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserInfo(): array{
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email
        ];
    }
    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }


    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @return Collection<int, PadelGame>
     */
    public function getGames(): Collection
    {
        return $this->padelGames;
    }

    public function addGame(PadelGame $padelGame): static
    {
        if (!$this->padelGames->contains($padelGame)) {
            $this->padelGames->add($padelGame);
            $padelGame->setUser($this);
        }

        return $this;
    }

    public function removeGame(PadelGame $padelGame): static
    {
        if ($this->padelGames->removeElement($padelGame)) {
            // set the owning side to null (unless already changed)
            if ($padelGame->getUser() === $this) {
                $padelGame->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TenisGame>
     */
    public function getTenisGames(): Collection
    {
        return $this->tenisGames;
    }

    public function addTenisGame(TenisGame $tenisGame): static
    {
        if (!$this->tenisGames->contains($tenisGame)) {
            $this->tenisGames->add($tenisGame);
            $tenisGame->setUsers($this);
        }

        return $this;
    }

    public function removeTenisGame(TenisGame $tenisGame): static
    {
        if ($this->tenisGames->removeElement($tenisGame)) {
            // set the owning side to null (unless already changed)
            if ($tenisGame->getUsers() === $this) {
                $tenisGame->setUsers(null);
            }
        }

        return $this;
    }
}