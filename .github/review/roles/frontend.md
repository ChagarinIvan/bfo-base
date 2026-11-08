# frontend

Review Vue/TypeScript runtime bugs: reactivity, computed/watch state, request races, stale responses,
unmount cleanup, pagination/filter resets, route params/navigation, API camelCase/nullability,
auth changes and guest metadata visibility. Trace loading/error/empty states, validation, duplicate
submit handling, event propagation and component props/emits. Inspect relevant existing API contracts
and stores before flagging. Static analysis is not a browser or executed test. Report concrete user
sequence and wrong visible state/request; leave visual consistency to uiux.
