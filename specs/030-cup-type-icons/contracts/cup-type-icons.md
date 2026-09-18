# SPA Contract: Cup Type Icons

## Shared mapping

`cupTypeDefinition(type: string)` returns:

```ts
{
  type: string
  icon: string
  label: string
  fallback: boolean
}
```

For each current `CupType`, `fallback` is `false`. Unknown values return the
neutral icon and `fallback: true`.

## Components

- `CupTypeIcon` accepts a `type: string` and optional `showLabel` boolean.
- It renders an accessible icon for the resolved definition.
- `CupForm` type options use the same resolved definition and emit the original
  enum string.
- `CupsPage` keeps the existing cup anchor and adds the icon beside its name.

## Compatibility

The JSON API payload is unchanged. No new request or response fields are
introduced.
