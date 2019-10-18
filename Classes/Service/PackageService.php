<?php
namespace Sitegeist\Chantalle\Service;

use Neos\Flow\Package\FlowPackageInterface;
use Sitegeist\Chantalle\Domain\PackageKey;

class PackageService
{

    /**
     * @param string $path
     * @param PackageKey $source
     * @param PackageKey $target
     */
    public static function alterPackageNamespace(string $path, PackageKey $source, PackageKey $target)
    {
        $stringReplacements = [];
        $stringReplacements[$source->getPackageKey()] = $target->getPackageKey();
        $stringReplacements[$source->getPhpNamespace()] = $target->getPhpNamespace();
        $stringReplacements[addslashes($source->getPhpNamespace())] = addslashes($target->getPhpNamespace());
        $stringReplacements[$source->getRootNodeName()] = $target->getRootNodeName();

        StringReplacementService::replaceRecursively($stringReplacements, $path);

        $configPathReplacements = [];
        $configPathReplacements[$source->getPackageKey()] = $target->getPackageKey();

        ConfigurationAdjustmentService::replaceSettingPathes($configPathReplacements, $path);

        JsonFileService::modifyFile(
            $path . DIRECTORY_SEPARATOR . 'composer.json',
            [
                'name' => $target->getComposerName(),
                'extra' => [
                    'neos' => [
                        'package-key' => $target->getPackageKey()
                    ]
                ]
            ]
        );
    }
}
