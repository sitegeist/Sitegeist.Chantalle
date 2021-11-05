<?php
declare(strict_types=1);

namespace Sitegeist\Chantalle\Domain;

use Neos\ContentRepository\Utility;

class PackageKey
{
    protected $vendor;
    protected $name;

    /**
     * PackageDescription constructor.
     * @param string $packageKey
     * @param string $composerName
     * @param string $phpNamespaces
     * @param string $rootNodeName
     */
    public function __construct(string $packageKey)
    {
        list($this->vendor, $this->name) = explode('.', $packageKey, 2);
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
        return $this->vendor . '.' . $this->name;
    }

    /**
     * @return string
     */
    public function getVendor(): string
    {
        return $this->vendor;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getComposerName(): string
    {
        return static::buildComposerName($this->vendor, $this->name);
    }

    /**
     * @return string
     */
    public static function buildComposerName($vendor, $name): string
    {
        return strtolower($vendor) . '/' . strtolower(str_replace('.', '-', $name));
    }

    /**
     * @return string
     */
    public function getPhpNamespace(): string
    {
        return str_replace('.', '\\', $this->getPackageKey());
    }

    /**
     * @return string
     */
    public function getRootNodeName(): string
    {
        return Utility::renderValidNodeName($this->getPackageKey());
    }
}
