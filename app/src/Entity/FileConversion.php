<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\Enum\FileConversionStatus;
use App\Repository\FileConversionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FileConversionRepository::class)]
class FileConversion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private FileUpload $upload;

    #[ORM\Column(enumType: FileConversionStatus::class)]
    private ?FileConversionStatus $status = FileConversionStatus::Queued;

    #[ORM\Column(type: 'uuid', unique: true)]
    #[ApiProperty(identifier: true)]
    private ?Uuid $uid;

    public function __construct()
    {
        $this->uid = Uuid::v4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpload(): FileUpload
    {
        return $this->upload;
    }

    public function setUpload(FileUpload $upload): static
    {
        $this->upload = $upload;

        return $this;
    }

    public function getStatus(): ?FileConversionStatus
    {
        return $this->status;
    }

    public function setStatus(FileConversionStatus $status): static
    {
        $this->status = $status;

        return $this;
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
