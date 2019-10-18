<?php
declare(strict_types=1);

namespace Sitegeist\Chantalle\Domain;

use Neos\ContentRepository\Utility;

class PackageKey
{
    protected $packageKey;

    /**
     * PackageDescription constructor.
     * @param string $packageKey
     * @param string $composerName
     * @param string $phpNamespaces
     * @param string $rootNodeName
     */
    public function __construct(string $packageKey)
    {
        $this->packageKey = $packageKey;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getPackageKey();
    }

    /**
     * @return string
     */
    public function getPackageKey(): string
    {
        return $this->packageKey;
    }

    /**
     * @return string
     */
    public function getComposerName(): string
    {
        list($vendor, $name) = explode('.', $this->packageKey, 2);
        return strtolower($vendor) . '/' . strtolower(str_replace('.', '-', $name));
    }

    /**
     * @return string
     */
    public function getPhpNamespace(): string
    {
        return str_replace('.', '\\', $this->packageKey);
    }

    /**
     * @return string
     */
    public function getRootNodeName(): string
    {
        return Utility::renderValidNodeName($this->packageKey);
    }
}
