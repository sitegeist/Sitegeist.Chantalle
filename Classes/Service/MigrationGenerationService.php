<?php
namespace Sitegeist\Chantalle\Service;

use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Flow\Configuration\Source\YamlSource;
use Neos\Utility\Files;
use Sitegeist\Chantalle\Domain\PackageKey;

class MigrationGenerationService
{
    const PACKAGE_DELIMITER = ':';

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @param string $source
     * @param string $target
     */
    public function createNodeMigration(string $source, string $target, string $targetPackagePath): string
    {
        $migrations = [];
        $migrationCounter = 0;
        $migrationIdentifier = '';

        $migrations['up']['comments'] = 'migration von ' . $source . ' auf ' . $target;
        $postDelimiterSlices = $this->getPostDelimiterSlices($source, $target);
        if ($postDelimiterSlices) {
            foreach ($postDelimiterSlices as $postDelimiterSlice) {
                $migrations['up']['migration'][$migrationCounter]['filters'][0]['type'] = 'NodeType';
                $migrations['up']['migration'][$migrationCounter]['filters'][0]['settings']['nodeType']
                    = $source . ':' . $postDelimiterSlice;

                $migrations['up']['migration'][$migrationCounter]['transformations'][0]['type'] = 'ChangeNodeType';
                $migrations['up']['migration'][$migrationCounter]['transformations'][0]['settings']['newType']
                    = $target . ':' . $postDelimiterSlice;
                $migrationCounter++;
            }
        }

        if ($migrationCounter) {
            $migrationSource = new YamlSource();
            $migrationIdentifier = (new \DateTimeImmutable())->format('YmdHis');
            $filePath = $targetPackagePath . '/Migrations/ContentRepository/';
            Files::createDirectoryRecursively($filePath, true);
            $migrationSource->save($filePath . 'Version' . $migrationIdentifier, $migrations);
        }

        return $migrationIdentifier;
    }

    protected function getPostDelimiterSlices(string $source, string $target): array
    {
        $postDelimiterSlices = [];

        $nodeTypes = $this->nodeTypeManager->getNodeTypes();

        foreach ($nodeTypes as $nodeTypeName => $nodeType) {
            $postDelimiterSlice = $this->getPostDelimiterSlice($nodeTypeName, $source, $target);

            if (!in_array($postDelimiterSlice, $postDelimiterSlices)
                && !$nodeType->isAbstract()) {
                $postDelimiterSlices[] = $postDelimiterSlice;
            }
        };
        return $postDelimiterSlices;
    }

    protected function getPostDelimiterSlice(string $nodeTypeName, string $source, string $target): ?string
    {
        list($packageSlice, $postDelimiterSlice) = array_pad(
            explode(
                self::PACKAGE_DELIMITER,
                $nodeTypeName,
                2
            ),
            2,
            null
        );

        return  ($packageSlice === $source || $packageSlice === $target)
            ? $postDelimiterSlice
            :null;
    }
}
