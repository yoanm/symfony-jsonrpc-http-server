COLOR_ENABLED ?= true
TEST_OUTPUT_STYLE ?= dot

## DIRECTORY AND FILE
BUILD_DIRECTORY ?= build
REPORTS_DIRECTORY ?= ${BUILD_DIRECTORY}/reports # Codestyle
BEHAT_COVERAGE_DIRECTORY ?= ${BUILD_DIRECTORY}/coverage-behat
PHPUNIT_COVERAGE_DIRECTORY ?= ${BUILD_DIRECTORY}/coverage-phpunit
PHPUNIT_UNIT_COVERAGE_FILE_PATH ?= ${PHPUNIT_COVERAGE_DIRECTORY}/unit.clover
PHPUNIT_FUNCTIONAL_COVERAGE_FILE_PATH ?= ${PHPUNIT_COVERAGE_DIRECTORY}/functional.clover

## Commands options
### Composer
#COMPOSER_OPTIONS=
### Phpcs
PHPCS_REPORT_STYLE ?= full
PHPCS_DISABLE_WARNING ?= "false"
#PHPCS_REPORT_FILE=
#PHPCS_REPORT_FILE_OPTION=

# Enable/Disable color ouput
ifeq ("${COLOR_ENABLED}","true")
	PHPUNIT_COLOR_OPTION ?= --colors=always
	BEHAT_COLOR_OPTION ?= --colors
	PHPCS_COLOR_OPTION ?= --colors
	COMPOSER_COLOR_OPTION ?= --ansi
else
	PHPUNIT_COLOR_OPTION ?= --colors=never
	PHPCS_COLOR_OPTION ?= --no-colors
	BEHAT_COLOR_OPTION ?= --no-colors
	COMPOSER_COLOR_OPTION ?= --no-ansi
endif

ifeq ("${TEST_OUTPUT_STYLE}","pretty")
	PHPUNIT_OUTPUT_STYLE_OPTION ?= --testdox
	BEHAT_OUTPUT_STYLE_OPTION ?= --format pretty
else
	PHPUNIT_OUTPUT_STYLE_OPTION ?=
	BEHAT_OUTPUT_STYLE_OPTION ?= --format progress
endif

ifdef COVERAGE_OUTPUT_STYLE
	export XDEBUG_MODE=coverage
	ifeq ("${COVERAGE_OUTPUT_STYLE}","html")
		PHPUNIT_COVERAGE_OPTION ?= --coverage-html ${PHPUNIT_COVERAGE_DIRECTORY}
		PHPUNIT_FUNCTIONAL_COVERAGE_OPTION ?= --coverage-html ${PHPUNIT_COVERAGE_DIRECTORY}
		BEHAT_COVERAGE_OPTION ?= --profile coverage-html
	else ifeq ("${COVERAGE_OUTPUT_STYLE}","clover")
		PHPUNIT_COVERAGE_OPTION ?= --coverage-clover ${PHPUNIT_UNIT_COVERAGE_FILE_PATH}
		PHPUNIT_FUNCTIONAL_COVERAGE_OPTION ?= --coverage-clover ${PHPUNIT_FUNCTIONAL_COVERAGE_FILE_PATH}
		BEHAT_COVERAGE_OPTION ?= --profile coverage-clover
        else
		PHPUNIT_COVERAGE_OPTION ?= --coverage-text
		PHPUNIT_FUNCTIONAL_COVERAGE_OPTION ?= --coverage-text
		BEHAT_COVERAGE_OPTION ?= --profile coverage
	endif
endif

ifneq ("${PHPCS_REPORT_FILE}","")
	PHPCS_REPORT_FILE_OPTION ?= --report-file=${PHPCS_REPORT_FILE}
endif

ifneq ("${PHPCS_DISABLE_WARNING}","true")
	PHPCS_DISABLE_WARNING_OPTION=
else
	PHPCS_DISABLE_WARNING_OPTION=-n
endif


## Project build (install and configure)
build: install configure

## Project installation
install:
	composer install ${COMPOSER_COLOR_OPTION} ${COMPOSER_OPTIONS} --prefer-dist --no-suggest --no-interaction

## project Configuration
configure:

# Project tests
test: test-functional test-unit codestyle

ifdef PHPUNIT_COVERAGE_OPTION
test-unit: create-build-directories
endif
test-unit:
	./vendor/bin/phpunit ${PHPUNIT_COLOR_OPTION} ${PHPUNIT_OUTPUT_STYLE_OPTION} ${PHPUNIT_COVERAGE_OPTION} --testsuite technical

ifdef BEHAT_COVERAGE_OPTION
test-functional: create-build-directories
else ifdef PHPUNIT_FUNCTIONAL_COVERAGE_OPTION
test-functional: create-build-directories
endif
test-functional:
	./vendor/bin/phpunit ${PHPUNIT_COLOR_OPTION} ${PHPUNIT_OUTPUT_STYLE_OPTION} ${PHPUNIT_FUNCTIONAL_COVERAGE_OPTION} --testsuite functional
	./vendor/bin/behat ${BEHAT_COLOR_OPTION} ${BEHAT_OUTPUT_STYLE_OPTION} ${BEHAT_COVERAGE_OPTION} --no-snippets

codestyle: create-build-directories
	./vendor/bin/phpcs ${PHPCS_DISABLE_WARNING_OPTION} --standard=phpcs.xml.dist ${PHPCS_COLOR_OPTION} ${PHPCS_REPORT_FILE_OPTION} --report=${PHPCS_REPORT_STYLE}

scrutinizer-phpunit:
	XDEBUG_MODE=coverage ./vendor/bin/phpunit ${PHPUNIT_COLOR_OPTION} ${PHPUNIT_OUTPUT_STYLE_OPTION} --coverage-clover build/coverage-phpunit/scrutinizer.xml

scrutinizer-behat:
	XDEBUG_MODE=coverage ./vendor/bin/behat ${BEHAT_COLOR_OPTION} ${BEHAT_OUTPUT_STYLE_OPTION} --profile coverage-clover --no-snippets


# Internal commands
create-build-directories:
	mkdir -p ${PHPUNIT_COVERAGE_DIRECTORY} ${BEHAT_COVERAGE_DIRECTORY} ${REPORTS_DIRECTORY}

.PHONY: build install configure test test-unit test-functional codestyle create-build-directories scrutinizer-behat scrutinizer-phpunit
.DEFAULT: build
