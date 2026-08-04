[Nette Framework](https://nette.org)
===================================

Nette is a PHP framework built on one bet: that the secure way should also be the easiest way. Other frameworks give you a security checklist. Nette gives you defaults that are already right.

Routing, dependency injection, templates, forms, database, authentication, debugging: it is all here, and every piece also works on its own.


Get Started
-----------

**[Create Your First Application →](https://doc.nette.org/quickstart)**

The Quick Start tutorial builds a real blog with you: posts coming from a database, a comment form, user authentication. Not a hello world that falls apart the moment you need a second page. It is the shortest path from nothing to understanding how the whole thing fits together.


Security You Don't Have to Think About
--------------------------------------

Latte is the only PHP template engine with [context-aware escaping](https://latte.nette.org/en/safety-first). It parses the template, sees where your variable actually lands, and escapes it accordingly:

```latte
<p>{$comment}</p>
<a href="{$profileUrl}">{$name}</a>
<script>const user = {$name};</script>
```

HTML text, a URL, JavaScript: three sets of rules, applied correctly, none of them requested. Other engines offer one generic filter and trust you to reach for it every single time. That is why an XSS hole in a Latte template takes deliberate effort.

Forms work the same way. This one is CSRF-protected, validated in the browser and validated again on the server, from a single definition:

```php
$form = new Nette\Forms\Form;
$form->addText('name', 'Name:')
	->setRequired();
$form->addEmail('email', 'E-mail:');
$form->addPassword('password', 'Password:')
	->addRule($form::MinLength, 'Use at least %d characters', 8);
$form->addSubmit('send', 'Sign up');
```

The HTTP layer rejects forged cross-origin requests using Sec-Fetch headers. You don't opt into safety here. You would have to opt out.


An Error Message That Solves the Problem
----------------------------------------

Tracy replaces the white screen of death with a page that shows the exception, the source code around it, the value of every local variable, the SQL queries that ran, and a link that opens the offending line directly in your editor.

It is a standalone library with no ties to the rest of Nette, which is how it ends up in projects that use no Nette at all.


In Development Since 2004
-------------------------

Two decades of production use mean the libraries are stable, because the mistakes were made and fixed years ago. It also means upgrading is a documented procedure rather than an archaeology project: every major version ships a migration guide, and every library is versioned separately, so you upgrade what you want, when you want.


Documentation in Ten Languages
------------------------------

A complete manual, not an API dump: [English](https://doc.nette.org), Czech, German, Spanish, French, Italian, Japanese, Polish, Russian and Turkish. Next to it a quick start, a best practices section and migration guides for every major version.

When the manual isn't enough, there is the [forum](https://forum.nette.org).


Libraries and Framework
-----------------------

Nette is a family of standalone libraries. Take the whole framework, or take a single library into your WordPress plugin, your Symfony application or your legacy codebase. They don't drag each other in and they don't ask you to adopt anything else.

- [Application](https://github.com/nette/application) – The kernel of web application
- [Assets](https://github.com/nette/assets) – Elegant asset management
- [Bootstrap](https://github.com/nette/bootstrap) – Bootstrap of your application
- [Caching](https://github.com/nette/caching) – Cache layer with set of storages
- [Command Line](https://github.com/nette/command-line) – Options and arguments parser
- [Component Model](https://github.com/nette/component-model) – Foundation for component systems
- [DI](https://github.com/nette/di) – Dependency Injection Container
- [Database](https://github.com/nette/database) – Database layer
- [Forms](https://github.com/nette/forms) – Greatly facilitates secure web forms
- [Http](https://github.com/nette/http) – Layer for the HTTP request & response
- [Latte](https://github.com/nette/latte) – The safest template engine
- [Mail](https://github.com/nette/mail) – Sending E-mails
- [Neon](https://github.com/nette/neon) – Loads and dumps NEON format
- [PHP Generator](https://github.com/nette/php-generator) – PHP code generator
- [Robot Loader](https://github.com/nette/robot-loader) – The most comfortable autoloading
- [Routing](https://github.com/nette/routing) – Routing
- [Safe Stream](https://github.com/nette/safe-stream) – Safe atomic operations with files
- [Schema](https://github.com/nette/schema) – User data validation
- [Security](https://github.com/nette/security) – Provides access control system
- [Tester](https://github.com/nette/tester) – Enjoyable unit testing in PHP
- [Tracy](https://github.com/nette/tracy) – Debugging tool you will love ♥
- [Utils](https://github.com/nette/utils) – Utilities and Core Classes

This repository is a metapackage that installs all of them at once. Most projects are better served by starting from the tutorial above, or by requiring only the libraries they actually use.


Support Nette
-------------

Nette is free and always will be. If it earns you money or saves you time, [please make a donation](https://nette.org/donate). It pays for the development and for the documentation.
