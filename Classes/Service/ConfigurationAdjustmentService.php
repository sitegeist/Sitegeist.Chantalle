<?php
namespace Sitegeist\Chantalle\Service;

use Symfony\Component\Yaml\Yaml;
use Neos\Utility\Arrays;

class ConfigurationAdjustmentService
{
    /**
     * Replace configuration pathes in a directory
     *
     * @param array $replacements key=>value pairs
     * @param $baseDirectory
     */
    public static function replaceSettingPathes(array $replacements, $baseDirectory)
    {
        $dir      = new \RecursiveDirectoryIterator($baseDirectory . DIRECTORY_SEPARATOR . 'Configuration', \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($dir);

        $replacePathes = [];
        foreach ($replacements as $source => $target) {
            $replacePathes[] = [
                'source' => explode('.', $source),
                'target' => explode('.', $target)
            ];
        }

        /** @var \SplFileInfo $item */
        foreach ($iterator as $item) {
            if (!$item->isFile()) {
                continue;
            }
            if (fnmatch('Settings*', $item->getFilename()) && $item->getExtension() == 'yaml') {
                $configuration = Yaml::parseFile($item->getRealPath());
                $configurationWasAltered = false;

                foreach ($replacePathes as $replacement) {
                    if ($affectedConfiguration = Arrays::getValueByPath($configuration, $replacement['source'])) {
                        $configuration = Arrays::setValueByPath($configuration, $replacement['target'], $affectedConfiguration);
                        $configuration = Arrays::unsetValueByPath($configuration, $replacement['source']);
                        $configurationWasAltered = true;
                    }
                }

                if ($configurationWasAltered) {
                    file_put_contents($item->getRealPath(), Yaml::dump($configuration, 10));
                }
            }

            $content = file_get_contents($item->getRealPath());
            $content = str_replace(array_keys($replacements), array_values($replacements), $content);
            file_put_contents($item->getRealPath(), $content);
        }
    }
}
