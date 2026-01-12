# Agent Instructions

- This is a WordPress plugin repository.
- Code lives in `/includes` with the main plugin bootstrap at the repo root.
- Coding standards:
  - Avoid duplicate hooks.
  - Guard global functions with `function_exists`.
  - Sanitize inputs and escape output.
  - Use Arabic UI strings where applicable.
  - Avoid breaking existing shortcodes.
- Local testing:
  - Run `php -l` on all plugin PHP files before committing.
