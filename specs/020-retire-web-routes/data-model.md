# Data Model

- **Person**: existing active/inactive lifecycle; disabling emits its existing domain event.
- **Protocol line**: existing line from which a person can be extracted; equal prepared lines are reassigned.
- **Registration invitation**: encrypted email token with no persisted schema change; activation creates or updates the user and sends a generated password.
