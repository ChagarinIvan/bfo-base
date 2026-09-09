# Research

- **Decision**: Reuse `DisablePersonService`; it already delegates to the aggregate disable method and event.
- **Decision**: Extract protocol-line behavior into an Application command, because the current controller reaches legacy services and Eloquent directly.
- **Decision**: Keep the existing invitation semantics and transport it through JSON/SPA rather than redesign credentials.
- **Decision**: Remove `/404` and `/500`; router catch-all supplies the SPA not-found view.
