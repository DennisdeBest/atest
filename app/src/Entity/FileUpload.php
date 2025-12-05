<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\State\FileUploadProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['file:read']]),
        new Post(
            inputFormats: ['multipart' => ['multipart/form-data']],
            normalizationContext: ['groups' => ['file:read']],
            denormalizationContext: ['groups' => ['file:write']],
            processor: FileUploadProcessor::class,
        ),
    ]
)]
class FileUpload
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['file:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['file:read'])]
    private ?Uuid $uid;

    #[ORM\Column(length: 255)]
    #[Groups(['file:read'])]
    private string $path;

    #[Groups(['file:write'])]
    #[Assert\File(
        maxSize: '20M',
        mimeTypes: [
            'text/csv',
            'application/json',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.oasis.opendocument.spreadsheet',
        ],
        mimeTypesMessage: 'Only CSV, JSON, XLSX and ODS files are allowed.',
    )]
    public ?File $file = null;

    public function __construct()
    {
        $this->uid = Uuid::v4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): ?Uuid
    {
        return $this->uid;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }
}
