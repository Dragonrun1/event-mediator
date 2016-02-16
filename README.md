# Event-Mediator

[![Build Status](https://travis-ci.org/Dragonrun1/event-mediator.svg?branch=master)](https://travis-ci.org/Dragonrun1/event-mediator)
[![Build Status](https://scrutinizer-ci.com/g/Dragonrun1/event-mediator/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Dragonrun1/event-mediator/build-status/master)
[![Coverage Status](https://coveralls.io/repos/Dragonrun1/event-mediator/badge.svg?branch=master)](https://coveralls.io/r/Dragonrun1/event-mediator?branch=master)

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

Most people might know event mediator as an event dispatcher instead and both names work. The reason I choose to call it
a mediator is it follows the [mediator](https://en.wikipedia.org/wiki/Mediator_pattern) pattern. For those of you that
are familiar with Symfony 2 and it's
[EventDispatcher](http://symfony.com/doc/current/components/event_dispatcher/index.html) component then Event Mediator
is basically a drop in replace for it without the huge dependence overhead (IMHO) often seen with Symfony components.

To get a better understanding about Event-Mediator and how you might use it check out
[Understanding Event-Mediator](docs/UnderstandingEventMediator.md) 
