# Sitegeist.Chantalle
## Adopt packages into local distributiuon packahes with a new name

The package allows to copy an already installed package into the local 
DistributionPackages folder and adjust the namespace at the same time with
the following command. 

```
./flow package:adopt Neos.Demo Vendor.Site
```

Afterwards you can require the new package and remove the original one.

## Installation

Sitegeist.Chantalle is available via packagist run `composer require sitegeist/chantalle`.
We use semantic-versioning so every breaking change will increase the major-version number.

### Authors & Sponsors

* Martin Ficzel - ficzel@sitegeist.de

*The development and the public-releases of this package is generously sponsored
by our employer http://www.sitegeist.de.*


## Contribution

We will gladly accept contributions. Please send us pull requests.
