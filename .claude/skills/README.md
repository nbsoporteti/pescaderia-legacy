# Skills de Claude Code

Skills a nivel de proyecto. Claude Code las descubre automáticamente desde
`.claude/skills/` cuando se trabaja en este repositorio, así que están
disponibles en cualquier sesión (incluyendo Claude Code en la web).

## Procedencia

Estas skills provienen del proyecto **Superpowers** de Jesse Vincent:

- Repositorio: https://github.com/obra/superpowers
- Versión: `5.1.0`
- Commit de origen: `8cf3900` (2026-06-15)
- Licencia: MIT — ver [`LICENSE-superpowers`](./LICENSE-superpowers)

Se copiaron tal cual desde `skills/` del repositorio upstream. No se
modificó su contenido.

## Skills incluidas

| Skill | Para qué sirve |
|---|---|
| `using-superpowers` | Punto de entrada: cómo descubrir y usar el resto de skills |
| `brainstorming` | Explorar intención y requisitos antes de implementar |
| `writing-plans` | Escribir planes para tareas multi-paso |
| `executing-plans` | Ejecutar un plan escrito con puntos de control |
| `test-driven-development` | TDD antes de escribir código de implementación |
| `systematic-debugging` | Depuración metódica ante bugs y fallos |
| `requesting-code-review` | Pedir revisión de código |
| `receiving-code-review` | Procesar feedback de revisión con rigor técnico |
| `verification-before-completion` | Verificar con evidencia antes de declarar "terminado" |
| `using-git-worktrees` | Aislar trabajo en git worktrees |
| `dispatching-parallel-agents` | Repartir tareas independientes entre subagentes |
| `subagent-driven-development` | Ejecutar planes con subagentes en la sesión actual |
| `finishing-a-development-branch` | Cerrar una rama: merge / PR / limpieza |
| `writing-skills` | Crear, editar y verificar skills nuevas |

## Cómo actualizar

Para traer una versión más nueva desde upstream:

```bash
git clone --depth 1 https://github.com/obra/superpowers /tmp/superpowers
cp -R /tmp/superpowers/skills/. .claude/skills/
cp /tmp/superpowers/LICENSE .claude/skills/LICENSE-superpowers
# luego actualiza la versión y el commit indicados arriba
```

Alternativa (en tu Claude Code local, no en la web): instalarlas como plugin
con `/plugin marketplace add obra/superpowers` y `/plugin install superpowers`.
