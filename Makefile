.PHONY: ci test cs phpunit phpcs stan

ci: test cs
test: phpunit jest
cs: phpcs stan

phpunit:
ifdef filter
	php ../../tests/phpunit/phpunit.php -c phpunit.xml.dist --filter $(filter)
else
	php ../../tests/phpunit/phpunit.php -c phpunit.xml.dist
endif

perf:
	php ../../tests/phpunit/phpunit.php -c phpunit.xml.dist --group Performance

phpcs:
	vendor/bin/phpcs -p -s --standard=$(shell pwd)/phpcs.xml

stan:
	vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=2G

stan-baseline:
	vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=2G --generate-baseline

npm-install:
	docker run -it --rm -v "$(CURDIR)":/home/node/app -w /home/node/app -u node node:20 npm install

lint-docker:
	docker run -it --rm -v "$(CURDIR)":/home/node/app -w /home/node/app -u node node:20 npm run lint

jest:
	docker run -it --rm -v "$(CURDIR)":/home/node/app -w /home/node/app -u node node:20 npm run test
