# architecture

Check constitution and layer boundaries: Application orchestrates, Domain owns business rules,
Bridge maps DTO -> command, Infrastructure implements ports. No new legacy Services/Repositories,
Laravel facades or transport DTO leaking out of commands. Check constructor dependencies,
Criteria/typed Resources/Slice, bounded queries, eager-loading and N+1. Check transaction/lock,
Impression audit, persistence and after-commit consumers. Use the backend manifest debt register:
existing Eloquent aggregates are transitional, do not report unchanged debt or demand a broad rewrite.
For V1 check camelCase, common serializer, typed UserId context and request-test @see links.
