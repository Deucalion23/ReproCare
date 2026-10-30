# Git Workflow Rule

After **every completed task** in this project, always run the following git
commands in order, from the project root (`c:\xampp\htdocs\ReproCare`):

```powershell
git add .
git commit -m "<meaningful message describing what was done>"
git push origin main
git pull origin main
```

## Rules

- The commit message must be concise and descriptive (e.g. `feat: add bulk test
  seeder for 5 RHUs`, `fix: correct checkup status logic`, `refactor: extract
  health-record helper`).
- Use conventional commit prefixes: `feat:`, `fix:`, `refactor:`, `chore:`,
  `docs:`, `test:`, `style:`.
- Always `push` **then** `pull` in that order so the remote is updated before
  syncing back.
- If there is nothing to commit (clean working tree), skip the commit step but
  still run `git pull origin main` to stay in sync.
- Never force-push (`--force`) without explicit user instruction.
- Run all four commands as a single workflow — do not stop after just `git add`.
