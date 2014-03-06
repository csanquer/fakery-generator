Fakery Generator - a Random Test Fixtures Data Generator
========================================================

[![Latest Stable Version](https://poser.pugx.org/csanquer/fakery-generator/v/stable.png)](https://packagist.org/packages/csanquer/fakery-generator)
[![Latest Unstable Version](https://poser.pugx.org/csanquer/fakery-generator/v/unstable.png)](https://packagist.org/packages/csanquer/fakery-generator)
[![Build Status](https://travis-ci.org/csanquer/fakery-generator.png?branch=master)](https://travis-ci.org/csanquer/fakery-generator)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/csanquer/fakery-generator/badges/quality-score.png?s=2198ddf464fb44997b3990277151df112a95844a)](https://scrutinizer-ci.com/g/csanquer/fakery-generator/)
[![Code Coverage](https://scrutinizer-ci.com/g/csanquer/fakery-generator/badges/coverage.png?s=7d035be3f60ced08ac77245c40354460aa6fe2ab)](https://scrutinizer-ci.com/g/csanquer/fakery-generator/)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/7a3e3061-48f2-4bee-8c2b-4da3b28db159/mini.png)](https://insight.sensiolabs.com/projects/7a3e3061-48f2-4bee-8c2b-4da3b28db159)

[![Dependency Status](https://www.versioneye.com/user/projects/53184899ec13755bfa000652/badge.png)](https://www.versioneye.com/user/projects/53184899ec13755bfa000652)
[![Project Status](http://stillmaintained.com/csanquer/colibri-csv.png)](http://stillmaintained.com/csanquer/colibri-csv)
[![License](https://poser.pugx.org/csanquer/fakery-generator/license.png)](https://packagist.org/packages/csanquer/fakery-generator)
[![Daily Downloads](https://poser.pugx.org/csanquer/fakery-generator/d/daily.png)](https://packagist.org/packages/csanquer/fakery-generator)
[![Monthly Downloads](https://poser.pugx.org/csanquer/fakery-generator/d/monthly.png)](https://packagist.org/packages/csanquer/fakery-generator)
[![Total Downloads](https://poser.pugx.org/csanquer/fakery-generator/downloads.png)](https://packagist.org/packages/csanquer/fakery-generator)

This project is a rewritten fork of [Spyrit's datalea](https://github.com/spyrit/datalea), I developed previously at Spyrit's.

Install
-------

* copy config sample file and edit the copy with your environment values

```bash
cp app/config/parameters.yml.dist app/config/parameters.yml
```

* get composer http://getcomposer.org/ and install dependencies

```bash
    curl -s https://getcomposer.org/installer | php
```

* install dependencies
    
```bash
    php composer.phar install
```

* set your web server document root to web directory

* clean cache

```bash
    php app/console cache:clear
```

Licensing
---------

License GPL 3

* Copyright (C) 2012-2013 Spyrit Systeme
* Copyright (C) 2014      Charles Sanquer <charles.sanquer@gmail.com>

This file is part of Fakery Generator.

Fakery Generator is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Fakery Generator is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Fakery Generator.  If not, see <http://www.gnu.org/licenses/>.

