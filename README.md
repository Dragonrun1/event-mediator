# Event-Mediator

Travis-ci: [![Build Status](https://travis-ci.org/Dragonrun1/event-mediator.svg?branch=master)](https://travis-ci.org/Dragonrun1/event-mediator)<br/>
Scruntinizer-ci: [![Build Status](https://scrutinizer-ci.com/g/Dragonrun1/event-mediator/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Dragonrun1/event-mediator/build-status/master)<br/>
Coveralls: [![Coverage Status](https://coveralls.io/repos/github/Dragonrun1/event-mediator/badge.svg?branch=master)](https://coveralls.io/github/Dragonrun1/event-mediator?branch=master)

A general event mediator (dispatcher) with minimal dependencies so it is easy to drop in and use.

## Installing

The recommended way to install Event-Mediator is using [Composer](https://getcomposer.org/) from
[Packagist](https://packagist.org/) with:

`composer require dragonrun1/event-mediator`

You can also get it as a [zip file](https://github.com/Dragonrun1/event-mediator/archive/master.zip) from
[GitHub](https://github.com/Dragonrun1/event-mediator).

## Licensing

Licensing information can be found in the [LICENSE](LICENSE) file.

## Introduction

Most people might know event mediator as an event dispatcher instead and both
names would have worked. The reason I choose to call it a mediator is it follows
the [mediator](https://en.wikipedia.org/wiki/Mediator_pattern) pattern. For
those of you that are familiar with Symfony 2 and it's
[EventDispatcher](http://symfony.com/doc/current/components/event_dispatcher/index.html)
component then Event Mediator started out as basically a drop in replace for it
without the (IMHO) huge dependence overhead often seen with Symfony components.
Event Mediator has since grown into something better since then I think.

To get a better understanding about Event-Mediator and how you might use it
check out [Understanding Event-Mediator](docs/UnderstandingEventMediator.md) 

## Changes

  * Started new 2.0-dev branch with many BC breaking changes.
  * The 1.0 series is now end of life and all application developers should
  update to newer 2.0 versions ASAP. If your code only used the listener methods
  the move should be easy with few changes needed. If application uses any of
  the subscriber stuff you will need to update the returned event array to
  reflect the new expected format.

  For a more complete understand of the changes refer to the commit messages and
  new code.
