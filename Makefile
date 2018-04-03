COLOR_ENABLED ?= true
TEST_OUTPUT_STYLE ?= dot
COVERAGE_OUTPUT_STYLE ?= html

## DIRECTORY AND FILE
BUILD_DIRECTORY ?= build
REPORTS_DIRECTORY ?= ${BUILD_DIRECTORY}/reports
COVERAGE_DIRECTORY ?= ${BUILD_DIRECTORY}/coverage
COVERAGE_CLOVER_FILE_PATH ?= ${COVERAGE_DIRECTORY}/clover.xml

## Commands options
### Composer
#COMPOSER_OPTIONS=
### Phpcs
PHPCS_REPORT_STYLE ?= full
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

ifeq ("${COVERAGE_OUTPUT_STYLE}","clover")
	PHPUNIT_COVERAGE_OPTION ?= --coverage-clover ${COVERAGE_CLOVER_FILE_PATH}
else
	ifeq ("${COVERAGE_OUTPUT_STYLE}","html")
    	PHPUNIT_COVERAGE_OPTION ?= --coverage-html ${COVERAGE_DIRECTORY}
    else
    	PHPUNIT_COVERAGE_OPTION ?= --coverage-text
    endif
endif

ifneq ("${PHPCS_REPORT_FILE}","")
	PHPCS_REPORT_FILE_OPTION ?= --report-file=${PHPCS_REPORT_FILE}
endif


## Project build (install and configure)
build: install configure

## Project installation
install:
	composer install ${COMPOSER_COLOR_OPTION} ${COMPOSER_OPTIONS} --prefer-dist --no-suggest --no-interaction

## project Configuration
configure:

# Project tests
test:
	make test-functional
	make test-technical
	make codestyle

test-technical:
	./vendor/bin/phpunit ${PHPUNIT_COLOR_OPTION} ${PHPUNIT_OUTPUT_STYLE_OPTION} --testsuite technical

test-functional:
	./vendor/bin/phpunit ${PHPUNIT_COLOR_OPTION} ${PHPUNIT_OUTPUT_STYLE_OPTION} --testsuite functional
	./vendor/bin/behat ${BEHAT_COLOR_OPTION} ${BEHAT_OUTPUT_STYLE_OPTION} --no-snippets

codestyle: create-reports-directory
	./vendor/bin/phpcs --standard=phpcs.xml.dist ${PHPCS_COLOR_OPTION} ${PHPCS_REPORT_FILE_OPTION} --report=${PHPCS_REPORT_STYLE}

coverage: create-coverage-directory
	./vendor/bin/phpunit ${PHPUNIT_COLOR_OPTION} ${PHPUNIT_OUTPUT_STYLE_OPTION} ${PHPUNIT_COVERAGE_OPTION}



# Internal commands
create-coverage-directory:
	mkdir -p ${COVERAGE_DIRECTORY}

create-reports-directory:
	mkdir -p ${REPORTS_DIRECTORY}


.PHONY: build install configure test test-technical test-functional codestyle coverage create-coverage-directory create-reports-directory
.DEFAULT: build
