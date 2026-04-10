# AGENTS

- One Laravel backend serving admin web, public API, and webhooks.
- Admin and API are separate surfaces, not separate systems.
- Keep controllers thin.
- Put validation in Form Requests.
- Put business logic in the Domain layer.
- Use Actions for single use cases.
- Use Services for reusable domain logic.
- Use Queries for non-trivial reads.
- Use DTOs for structured input and output.
- Use consistent API response envelopes.
- Use Spatie Permission with Policies for authorization.
- Keep models lean.
- Store money in minor units.
- Keep stock changes traceable.
- Keep payment handling webhook-safe.
- Preserve naming consistency across layers.
- Add tests for non-trivial logic.
