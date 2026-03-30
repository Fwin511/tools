## Skills

A skill is a set of local instructions stored in a `SKILL.md` file.

### Available skills

- feiyun-tools-maintainer: Maintains this `feiyun/tools` repository with module-aware workflow, changelog automation, validation commands, and release safety checks. Use when changing package code/docs under `src/`, `tools/`, `tests/`, `composer.json`, or README files. (file: /Users/sally/Sites/feiyun-tools/.agents/skills/feiyun-tools-maintainer/SKILL.md)

### How to use skills

- Discovery: Read the skill description above and open the listed `SKILL.md`.
- Trigger: If the request matches the skill description, use that skill for the turn.
- Scope: Follow the workflow in `SKILL.md`, then load only needed files from `references/` or `scripts/`.
- Fallback: If the skill path cannot be read, continue with best-effort repository maintenance workflow.

## Standard Workflow (Mandatory For New Sessions)

Use this protocol at the start of each new agent session that touches this repository.

1. Activate `$feiyun-tools-maintainer`.
2. Read only these files first:
   - `/Users/sally/Sites/feiyun-tools/AGENTS.md`
   - `/Users/sally/Sites/feiyun-tools/.agents/skills/feiyun-tools-maintainer/SKILL.md`
   - `/Users/sally/Sites/feiyun-tools/.agents/skills/feiyun-tools-maintainer/references/project-map.md`
   - `/Users/sally/Sites/feiyun-tools/.agents/skills/feiyun-tools-maintainer/references/agent-standard-workflow.md`
3. Confirm the scoped paths before coding; avoid whole-repo scanning unless requested.
4. After each completed task, record a changelog entry via:
   - `php scripts/changelog-manager.php add ...`
5. Before final response, run the appropriate maintenance checks:
   - `./.agents/skills/feiyun-tools-maintainer/scripts/run-maintenance-checks.sh fast`
   - or module-specific/full mode as needed.

## Output Contract (Mandatory)

- Report changed files grouped by module.
- Report compatibility impact.
- Report exact verification commands and results.
- If any check is skipped, state why.
