# Laravel Boost Rules

## Mapping File Glob to Rule Files

This file maps directories and file patterns to the rule files in the `.ai/rules` folder.

### Core Boost Rules (apply to entire project)

- `**/*.php` -> `boost/general.md`
- `**/*.blade.php` -> `boost/blade.md`
- `**/*.jsx` -> `boost/react.md`
- `**/*.css` -> `boost/css.md`

### Framework Specific Rules

- `app/Http/**` -> `boost/controllers.md`
- `app/Models/**` -> `boost/models.md`
- `resources/js/**` -> `boost/react-frontend.md`
- `resources/views/**` -> `boost/views.md`

### Testing Rules

- `tests/**` -> `boost/testing.md`

### Database Rules

- `database/migrations/**` -> `boost/migrations.md`
- `database/factories/**` -> `boost/factories.md`

## Overview of Available Rules

The following rule files contain detailed guidance for different aspects of the project:

- `boost/general.md` - General guidelines for working with the Laravel Boost project
- `boost/controllers.md` - Controller development guidelines
- `boost/models.md` - Eloquent model development
- `boost/react-frontend.md` - Frontend React application rules
- `boost/views.md` - View layer rules
- `boost/testing.md` - Testing rules and patterns
- `boost/migrations.md` - Migration development conventions
- `boost/factories.md` - Factory development rules