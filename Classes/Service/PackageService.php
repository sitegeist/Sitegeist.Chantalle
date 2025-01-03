<?php
namespace Sitegeist\Chantalle\Service;

use Neos\Flow\Package\FlowPackageInterface;
use Neos\Flow\Composer\ComposerUtility;
use Sitegeist\Chantalle\Domain\PackageKey;
use Neos\Flow\Package\FlowPackageKey;

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

        ConfigurationAdjustmentService::replaceSettingPaths($configPathReplacements, $path);

        $manifest = ComposerUtility::getComposerManifest($path . DIRECTORY_SEPARATOR);
        list($vendor, $name) = explode('/', $manifest['name']);

        // only replace vendor if it's a different one and thus keep the exact spelling as before, if it's the same
        if ( strcasecmp($source->getVendor(), $target->getVendor()) != 0 ) {
            $vendor = $target->getVendor();
        }

        $manifest['name'] = PackageKey::buildComposerName($vendor, $target->getName());

        ComposerUtility::writeComposerManifest($path . DIRECTORY_SEPARATOR, FlowPackageKey::fromString($target->getPackageKey()), $manifest);
    }
}
