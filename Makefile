COLOR_ENABLED ?= true
TEST_OUTPUT_STYLE ?= dot

## DIRECTORY AND FILE
BUILD_DIRECTORY ?= build
REPORTS_DIRECTORY ?= ${BUILD_DIRECTORY}/reports
COVERAGE_DIRECTORY ?= ${BUILD_DIRECTORY}/coverage
BEHAT_COVERAGE_DIRECTORY ?= ${BUILD_DIRECTORY}/coverage-behat
COVERAGE_CLOVER_FILE_PATH ?= ${COVERAGE_DIRECTORY}/clover.xml

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
		PHPUNIT_COVERAGE_OPTION ?= --coverage-html ${COVERAGE_DIRECTORY}
		BEHAT_COVERAGE_OPTION ?= --profile coverage-html
	else ifeq ("${COVERAGE_OUTPUT_STYLE}","clover")
        	PHPUNIT_COVERAGE_OPTION ?= --coverage-clover ${COVERAGE_CLOVER_FILE_PATH}
        	BEHAT_COVERAGE_OPTION ?= --profile coverage-clover
        else
		PHPUNIT_COVERAGE_OPTION ?= --coverage-text
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
else ifdef PHPUNIT_COVERAGE_OPTION
test-functional: create-build-directories
endif
test-functional:
	./vendor/bin/phpunit ${PHPUNIT_COLOR_OPTION} ${PHPUNIT_OUTPUT_STYLE_OPTION} ${PHPUNIT_COVERAGE_OPTION} --testsuite functional
	./vendor/bin/behat ${BEHAT_COLOR_OPTION} ${BEHAT_OUTPUT_STYLE_OPTION} ${BEHAT_COVERAGE_OPTION} --no-snippets

codestyle: create-build-directories
	./vendor/bin/phpcs ${PHPCS_DISABLE_WARNING_OPTION} --standard=phpcs.xml.dist ${PHPCS_COLOR_OPTION} ${PHPCS_REPORT_FILE_OPTION} --report=${PHPCS_REPORT_STYLE}

scrutinizer-phpunit:
	./vendor/bin/phpunit ${PHPUNIT_COLOR_OPTION} ${PHPUNIT_OUTPUT_STYLE_OPTION} ${PHPUNIT_COVERAGE_OPTION}

scrutinizer-behat:
	./vendor/bin/behat ${BEHAT_COLOR_OPTION} ${BEHAT_OUTPUT_STYLE_OPTION} ${BEHAT_COVERAGE_OPTION} --no-snippets


# Internal commands
create-build-directories:
	mkdir -p ${COVERAGE_DIRECTORY} ${BEHAT_COVERAGE_DIRECTORY} ${REPORTS_DIRECTORY} ${REPORTS_DIRECTORY}

.PHONY: build install configure test test-unit test-functional codestyle create-build-directories scrutinizer-behat scrutinizer-phpunit
.DEFAULT: build
