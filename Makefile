default: cs phpstan tests

cs:
	vendor/bin/phpcs --standard=PSR12 ./src ./tests

phpstan:
	vendor/bin/phpstan

tests:
	vendor/bin/tester tests -C
