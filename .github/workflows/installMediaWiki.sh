#! /bin/bash

MW_BRANCH=$1
EXTENSION_NAME=$2

wget https://github.com/wikimedia/mediawiki/archive/$MW_BRANCH.tar.gz -nv

tar -zxf $MW_BRANCH.tar.gz
mv mediawiki-$MW_BRANCH mediawiki

cd mediawiki

composer install
php maintenance/install.php --dbtype sqlite --dbuser root --dbname mw --dbpath $(pwd) --pass AdminPassword WikiName AdminUser

cat <<'EOT' >> LocalSettings.php
error_reporting(E_ALL| E_STRICT);
ini_set("display_errors", "1");
$wgShowExceptionDetails = true;
$wgShowDBErrorBacktrace = true;
$wgDevelopmentWarnings = true;
EOT

cat <<EOT >> LocalSettings.php
wfLoadExtension( 'Elastica' );
wfLoadExtension( 'CirrusSearch' );
\$wgCirrusSearchServers = [ 'localhost' ];

wfLoadExtension( 'WikibaseRepository', __DIR__ . '/extensions/Wikibase/extension-repo.json' );
require_once __DIR__ . '/extensions/Wikibase/repo/ExampleSettings.php';

wfLoadExtension( 'WikibaseCirrusSearch' );
\$wgWBCSUseCirrus = true;

wfLoadExtension( "$EXTENSION_NAME" );
EOT

cat <<EOT >> composer.local.json
{
	"extra": {
		"merge-plugin": {
			"merge-dev": true,
			"include": [
				"extensions/Elastica/composer.json",
				"extensions/CirrusSearch/composer.json",
				"extensions/Wikibase/composer.json",
				"extensions/$EXTENSION_NAME/composer.json"
			]
		}
	}
}
EOT

cd extensions
git clone https://gerrit.wikimedia.org/r/mediawiki/extensions/Elastica --depth=1  --branch=$MW_BRANCH --recurse-submodules -j8
git clone https://gerrit.wikimedia.org/r/mediawiki/extensions/CirrusSearch --depth=1  --branch=$MW_BRANCH --recurse-submodules -j8
git clone https://gerrit.wikimedia.org/r/mediawiki/extensions/Wikibase --depth=1 --branch=$MW_BRANCH --recurse-submodules -j8
git clone https://gerrit.wikimedia.org/r/mediawiki/extensions/WikibaseCirrusSearch --depth=1  --branch=$MW_BRANCH --recurse-submodules -j8
