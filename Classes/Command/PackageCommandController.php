<?php
namespace Sitegeist\Chantalle\Command;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Package\FlowPackageInterface;
use Neos\Flow\Package\PackageManager;
use Neos\Flow\Composer\ComposerUtility;
use Neos\Utility\Files;
use Sitegeist\Chantalle\Domain\PackageKey;
use Sitegeist\Chantalle\Service\PackageService;

class PackageCommandController extends CommandController
{
    /**
     * @var PackageManager
     * @Flow\Inject
     */
    protected $packageManager;

    /**
     * Adopt a package to the distribution packages folder and adjust the package name
     *
     * @param string $source package to adopt
     * @param string $target new package key
     */
    public function adoptCommand (string $source, string $target)
    {
        if (!$this->packageManager->isPackageAvailable($source)) {
            $this->outputLine(sprintf('The source package %s does not exist', $source));
            $this->quit(1);
        }

        $sourcePackage = $this->packageManager->getPackage($source);
        if (!$sourcePackage instanceof FlowPackageInterface) {
            $this->outputLine(sprintf('The source package has to be a flow package', $source));
            $this->quit(1);
        }

        if ($this->packageManager->isPackageAvailable($target)) {
            $this->outputLine(sprintf('The target package %s already exists', $target));
            $this->quit(1);
        }

        // identify local distribution folder
        $packagesPath = null;
        $composerManifestRepositories = ComposerUtility::getComposerManifest(FLOW_PATH_ROOT, 'repositories');
        if (is_array($composerManifestRepositories)) {
            foreach ($composerManifestRepositories as $repository) {
                if (is_array($repository) && $repository['type'] === 'path' && isset($repository['type'], $repository['url'])
                    && strpos($repository['url'], './') === 0 && substr($repository['url'], -2) === '/*'
                ) {
                    $packagesPath = Files::getUnixStylePath(Files::concatenatePaths([FLOW_PATH_ROOT, substr($repository['url'], 0, -2)]));
                    $runComposerRequireForTheCreatedPackage = true;
                    break;
                }
            }
        }

        if (!$packagesPath) {
            $this->outputLine('no local package path found in repositories of your composer manifest. Plese define one first');
            $this->quit(1);
        }

        // copy package
        $sourcePackagePath = realpath($sourcePackage->getPackagePath());
        $targetPackagePath = realpath($packagesPath) . DIRECTORY_SEPARATOR . $target;
        Files::copyDirectoryRecursively($sourcePackagePath, $targetPackagePath, false, true);

        $sourcePackageDescription = new PackageKey($source);
        $targetPackageDescription = new PackageKey($target);
        PackageService::alterPackageNamespace($targetPackagePath, $sourcePackageDescription, $targetPackageDescription);

        // final message
        $this->outputLine(sprintf('Package %s was adopted as %s in path %s', $source, $target, $targetPackagePath));
        $this->outputLine();
        $this->outputLine(sprintf('Please run `composer require %s && composer remove %s` to finalize this.', $targetPackageDescription->getComposerName(), $sourcePackageDescription->getComposerName()));
        $this->outputLine(sprintf('Also consider to remove CreativeResort.Chantalle with `composer remove sitegeist/chantalle`'));
    }
}
