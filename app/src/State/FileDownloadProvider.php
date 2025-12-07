<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\FileConversion;
use App\Service\FileConversionService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class FileDownloadProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider,
        private FileConversionService $fileConversionService,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        /** @var FileConversion $entity */
        $entity = $this->itemProvider->provide($operation, $uriVariables, $context);

        return $this->fileConversionService->map($entity);
    }
}
