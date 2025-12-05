<?php

namespace App\Entity;

use App\Enum\FileConversionStatus;
use App\Repository\FileConversionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileConversionRepository::class)]
class FileConversion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?FileUpload $upload = null;

    #[ORM\Column(enumType: FileConversionStatus::class)]
    private ?FileConversionStatus $status = FileConversionStatus::Queued;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpload(): ?FileUpload
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
}
