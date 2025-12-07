<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\Enum\FileInputFormat;
use App\Enum\FileOutputFormat;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class FileUpload
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $uid;

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
    public File $file;

    #[ORM\Column(enumType: FileInputFormat::class)]
    private ?FileInputFormat $inputFormat = null;

    #[Groups(['file:write'])]
    #[ORM\Column(type: 'string', enumType: FileOutputFormat::class)]
    #[ApiProperty(
        openapiContext: [
            'type' => 'string',
            'enum' => ['json', 'xml'],
            'example' => 'json',
            'description' => 'Desired output format for the converted file.',
        ]
    )]
    private FileOutputFormat $requestedOutputFormat;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

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

    public function getInputFormat(): ?FileInputFormat
    {
        return $this->inputFormat;
    }

    public function setInputFormat(FileInputFormat $inputFormat): static
    {
        $this->inputFormat = $inputFormat;

        return $this;
    }

    public function getRequestedOutputFormat(): ?FileOutputFormat
    {
        return $this->requestedOutputFormat;
    }

    public function setRequestedOutputFormat(FileOutputFormat $requestedOutputFormat): static
    {
        $this->requestedOutputFormat = $requestedOutputFormat;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }
}
