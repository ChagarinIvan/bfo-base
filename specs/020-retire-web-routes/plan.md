# Implementation Plan: Retire Remaining Web Routes

## Summary

Replace only the listed person/authentication/registration Blade entry points with SPA pages and JSON Bridge actions. Event and cup web flows are explicitly preserved.

## Technical Context

- PHP 8.5, Laravel 13, Vue 3 SPA, Vitest and PHPUnit.
- Existing API authentication token and Application services are reused.
- Protocol-line extraction is refactored into an Application command before adding its API action.

## Constitution Check

- Bridge actions create commands and call Application services.
- Domain/Application unit tests do not create Eloquent models; request tests own persistence verification.
- No Laravel facade or new legacy service is introduced.

## Design

1. Add authenticated JSON endpoints for disabling, extracting and assigning a person from/to a protocol line.
2. Add SPA clients/actions and replace legacy navigation targets.
3. Add SPA registration and activation pages plus API actions that preserve the current email flow.
4. Replace the protocol-assignment links on retained event pages with the SPA, then remove only listed person/error/authentication/registration web routes/controllers/views/tests, their unreferenced action bases/error-mail path/supporting templates, and the now-unused `guest` middleware alias; leave event/cup routes untouched.
5. Add SPA catch-all not-found page.

## Project Structure

```text
app/Application/Service/{Person,Auth}/
app/Bridge/Laravel/Http/Controllers/Api/V1/{Person,Auth}/
resources/spa/{api,pages,router}/
tests/Feature/Api/V1/
```
