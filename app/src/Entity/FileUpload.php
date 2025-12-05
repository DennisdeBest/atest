<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\FileUploadRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FileUploadRepository::class)]
#[ApiResource]
class FileUpload
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $uid = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): ?Uuid
    {
        return $this->uid;
    }

    public function setUid(Uuid $uid): static
    {
        $this->uid = $uid;

        return $this;
    }
}
