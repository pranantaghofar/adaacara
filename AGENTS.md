# Project Instructions

## Persistent Project Memory

`CODEX_CONTEXT.md` is the persistent technical memory for this project.

Before substantial work:

1. Read `CODEX_CONTEXT.md`.
2. Inspect `git status` and relevant `git diff` when Git metadata is available.
3. Inspect the actual source code related to the task.
4. Treat current source code, migrations/schema SQL, configuration, and database contracts as the source of truth.
5. Never blindly follow stale information from the context file.

After completing substantial work, update `CODEX_CONTEXT.md` ONLY when the work introduces or changes:

- architecture
- important feature behavior
- database/schema contracts
- API contracts
- serialization/data formats
- compatibility constraints
- important dependencies
- deployment requirements
- important workaround
- known unresolved issue
- important technical decision
- meaningful pending work

DO NOT update project memory for:

- typo fixes
- cosmetic CSS adjustments
- minor copy changes
- temporary debugging
- routine refactoring with no architectural impact
- trivial variable changes
- ordinary conversation
- unsuccessful experiments that have no future relevance

Keep `CODEX_CONTEXT.md` concise.

Prefer updating an existing section instead of continuously appending new entries.

Remove or replace stale information when it is no longer true.

Do not turn the context file into a changelog.

Git history is responsible for historical implementation details.

`CODEX_CONTEXT.md` is responsible only for information future Codex sessions need to understand the project correctly.
