# Understanding Event-Mediator

Before getting into any of the details on using Event-Mediator itself it's
probably useful to go over some of the questions you might have about the need
for it.

### Why events?

Before getting into why you might want to use some kind of events in PHP let's
take a look at them in another mostly single thread scripting language that many
of you probably know something about, Javascript. It could be said that without
a web browser triggering events like onLoad(), onClick(), onSubmit(), etc that
the site JS can interact with there wouldn't be much reason to have it around
and that modern responsive web applications wouldn't be possible. Now the events
in JS do have some limitations like only one script can be attached to any given
event for a tag and some events especially in old browsers are limited to only
certain tag types but with the development of multiple JS libraries like
[JQuery](https://jquery.com/), etc many of their shortcomings have been overcome
allow some truly interesting things to happen that no one would have imagined
ever doing in a browser just a few years ago. So with this in mind wouldn't you
think having a simple to use, fully customisable event system available also
prove useful server side in PHP? This was part of the thinking that lead to the
creation of Event-Mediator.

### Why use a mediator?

Many of you might be wondering why you should care about using a mediator when
you could just as easily use something like an
[observer](https://en.wikipedia.org/wiki/Observer_pattern). While it is true
that observer works well when you have a small number of subscribers and objects
being observed it starts to break down quickly when you have larger numbers of
either or both. Additionally it makes the objects being observed do an addition
task which normally requires several methods that really have nothing to do with
it primary purpose. So the observer pattern tends to cause tighter coupling
between classes than
[SOLID](https://en.wikipedia.org/wiki/SOLID_%28object-oriented_design%29)
principals seem to allow since both the observer and objects have to have
knowledge about each other to interact.

If instead you use a mediator all the subscribers (observers) need to do is be
registered with it. The subscribers themselves don't even need to know how to
register or that they are a subscriber really they only need to perform a
function when they receive the event. The objects only need to know how to
trigger the event but don't need to know anything about the subscribers or what
they do with the event. The coupling between the subscribers and objects is very
loose now and even the event mediator needs very little knowledge of the
subscribers and zero knowledge of the objects triggering the events.

## Listeners and Subscribers

In the above I used subscribers to refer to anything that wanted to receive the
event but another term used is listeners. In Event-Mediator both are possible
and they have some differences in how they and the mediator interact during the
add(attach) and delete(detach) process but the actual event related parts is the
same. To make it clearer what the difference is between them subscribers have to
implement the `SubscriberInterface` which has just one method while plain
listeners don't.

### Service Listeners and Subscribers

The only noticeable difference between a service version and base listeners or
subscribers is the service versions are
[lazy loading](https://en.wikipedia.org/wiki/Lazy_loading) meaning that they
only get initialized if the event is triggered and not at the time they are
added to the mediator. This can make for a more responsive application when some
events have a chance of not being triggered every time the application runs.
Better responsiveness will be most noticeable when the listener or subscriber is
a complex class which has a slow initialization process (large DB or filesystem
data records, etc) or you have a large number of listeners or subscribers for
one or more events which are only sometimes triggered.

## Events

So if you look at the
[EventInterface](https://github.com/Dragonrun1/event-mediator/blob/master/src/EventInterface.php)
you'll notice it only has a couple of methods (`eventHandled()`,
`hasBeenHandled()`). You also have the option of extend from the
[Event](https://github.com/Dragonrun1/event-mediator/blob/master/src/Event.php)
class or using the EventTrait as well with your only classes. Next to give you a
better idea what an complete and working event system might look like I'll use
an example from one of my other open source projects which uses this project.
 
### Logging Example

To start out I'm going to list the steps needed to make a working logging event
system using code examples from my
[Yapeal-ng](https://github.com/Yapeal/yapeal-ng) project which is a re-write of
another long time project with many improvements including switching to events
for logging and the main processing loop among others. I'll show how the logging
event stuff is done as I believe it's more general and probably easier to
understand so you might find you can adept it to your own projects with few
changes.

  1 Get new instance of Mediator. The
  [Yapeal-ng Mediator](https://github.com/Yapeal/yapeal-ng/blob/master/lib/Event/Mediator.php)
  extends from the
  [PimpleContainerMediator](https://github.com/Dragonrun1/event-mediator/blob/master/src/PimpleContainerMediator.php)
  class and is initialized in a common initialization class in the
  [`wireEvent()`](https://github.com/Yapeal/yapeal-ng/blob/eef492830cb53c195d8887abdb63f5912eead2ca/lib/Configuration/Wiring.php#L449-L453)
  method. If you haven't used [Pimple](http://pimple.sensiolabs.org/) before you
  might have a quick look at it's docs.
  
  2 Next get the event log class and register it with the event system.
  Yapeal-ng adds an
  [event wrapper](https://github.com/Yapeal/yapeal-ng/blob/master/lib/Log/Logger.php)
  around an initialized instance of [Monolog](https://github.com/Seldaek/monolog)
  but should work with any
  [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
  compatible class.
  You'll find the initialization and registering of the class for the log events in the
  [`wireLog()`](https://github.com/Yapeal/yapeal-ng/blob/eef492830cb53c195d8887abdb63f5912eead2ca/lib/Configuration/Wiring.php#L456-L503)
  method.
  
  3 Start sending log event as needed. For an example of this we'll look at
  [Yapeal::autoMagic()](https://github.com/Yapeal/yapeal-ng/blob/eef492830cb53c195d8887abdb63f5912eead2ca/lib/Yapeal.php#L69-L131)
  which kicks off the main processing loop. After a couple setup lines the
  method sends Yapeal-ng's first log message on line 83.
  
  4 More great and wonderful description here is on the todo list but the
  update of Event-Mediator to version 2.0.*-dev with many changes and the
  changes in Yapeal-ng as well will probably require a rewrite here so I've
  put a hold on this document until after everything is updated.;)
  
