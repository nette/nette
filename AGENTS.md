# To My Agents!

It is my fervent wish that this file guide every AI coding agent working with code in this repository.

## Project Overview

**nette/nette is a metapackage**, not a library: it ships no functionality of its
own. The actual deliverables are `composer.json` (the curated list of packages
that constitute "the framework") and `readme.md` (the project's public pitch).
The single PHP file, `Nette/Framework.php`, only carries version constants and is
classmap-autoloaded.

- **Package**: `nette/nette`; `master` is the development line, old maintenance
  branches are `v0.6` ... `v2.5`
- **PHP Version**: not declared here; it follows from the required packages

## Essential Commands

There is **no build, no test suite and no static analysis** in this repo.

```bash
composer update              # refresh vendor/ (composer.lock is gitignored)
php composer-frontline.php   # bump constraints to the newest releases
```

`composer-frontline.php` (untracked helper) runs `composer outdated -D` and
re-requires the outdated packages; without arguments it covers `nette/*`,
`tracy/*` and `latte/*`. It refuses to run unless `minimum-stability` is stable,
and it goes through `composer require`, which writes **caret** constraints, so
convert them back to `>=` by hand afterwards.

## Working in this repo

- **Constraints are deliberately `>=`, never `^`.** A metapackage must not hold
  back a newer major of any component, so each entry states only the minimum the
  framework actually needs. Do not "fix" them into caret ranges.
- **`require` lists only the top-level packages.** Component Model, Neon, Routing
  and Schema are not listed; they arrive transitively. `readme.md` on the other
  hand lists *all* libraries of the family, so adding or dropping a package means
  editing both files - they are not kept in sync by anything.
- **The version constants in `Nette/Framework.php` are unreliable.** They were
  bumped by hand at release time (see the `Released version 3.1` /
  `opened 3.2-dev` commit pairs, which also move the `branch-alias`), and that
  ritual has lapsed: tag `v3.3.0` still ships `VERSION = '3.2'`, and the
  `branch-alias` reads `3.2-dev` while the constraints on `master` already target
  Nette 4.0. **The git tag is the authoritative version**, not the constant.
- **Anything not meant for users is `export-ignore`d in `.gitattributes`**
  (`.github/`, `tests/`, `phpstan*.neon`, `ncs.*`, `AGENTS.md`). Add new
  development-only files to that list.
- `readme.md` is marketing copy aimed at newcomers: concrete, opinionated, no
  feature bullet-dumps. Match that voice when editing it.
