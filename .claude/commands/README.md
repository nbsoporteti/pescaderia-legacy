# Slash commands de Claude Code

Comandos a nivel de proyecto. Claude Code los descubre automáticamente desde
`.claude/commands/`, así que `/code-review` y `/security-review` quedan
disponibles en cualquier sesión de este repositorio.

## `code-review`

Revisión automática de un pull request con varios subagentes (haiku/sonnet/opus)
y scoring de confianza para filtrar falsos positivos. Reporta solo issues de
alta señal (bugs claros, errores de compilación/tipos, violaciones de CLAUDE.md).
Con el argumento `--comment` publica comentarios inline en el PR.

- Origen: https://github.com/anthropics/claude-code @ `64ceb97`
- Ruta upstream: `plugins/code-review/commands/code-review.md`
- Licencia: © Anthropic PBC — uso sujeto a los [Commercial Terms of Service](https://www.anthropic.com/legal/commercial-terms)
- Copiado sin modificaciones.

### Dependencias / requisitos

El comando asume:

- El **CLI `gh`** instalado y autenticado (`gh pr view/diff/comment`, etc.).
- El MCP `mcp__github_inline_comment__create_inline_comment` para los
  comentarios inline.

Por eso funciona mejor en **Claude Code local** con `gh` instalado. En Claude
Code en la web `gh` no está disponible, así que la parte de `--comment`/inline
podría no ejecutarse tal cual; la lógica de análisis sí es portable.

> Nota: este repo/harness ya trae un `/code-review` integrado propio. Este
> archivo añade la variante oficial de `anthropics/claude-code`; si hay
> ambigüedad al invocar `/code-review`, renombra este archivo (p. ej.
> `code-review-anthropic.md`).

## `security-review`

Revisión de seguridad de los cambios pendientes en la rama. Actúa como un
ingeniero de seguridad senior: busca vulnerabilidades de **alta confianza**
(inyección SQL/comandos, XSS, fallos de auth, secretos hardcodeados, deserialización
insegura, path traversal…) sobre el diff, filtra falsos positivos con subtareas
paralelas y entrega un reporte en markdown con severidad, escenario de explotación
y recomendación.

- Origen: https://github.com/anthropics/claude-code-security-review @ `0c6a49f`
- Ruta upstream: `.claude/commands/security-review.md`
- Licencia: MIT (© 2025 Anthropic) — ver [`LICENSE-security-review`](./LICENSE-security-review)
- Copiado sin modificaciones.

### Dependencias / requisitos

Usa solo herramientas estándar (`git diff`, `Read`, `Glob`, `Grep`, `Task`), así
que **funciona también en Claude Code en la web** (no necesita `gh` ni MCP extra).
Compara contra `origin/HEAD`, por lo que conviene tener el remoto actualizado.

> Nota: el repo `anthropics/claude-code-security-review` es además una **GitHub
> Action** para correr esta revisión automáticamente en cada PR (requiere un
> secret con API key de Anthropic). Aquí solo se incluyó el slash command; si
> quieres la Action en CI, se puede agregar aparte.
>
> Este harness ya trae un `/security-review` integrado propio; este archivo
> añade la variante oficial del repo de Anthropic.

## Cómo actualizar

```bash
# code-review (de anthropics/claude-code)
git clone --depth 1 https://github.com/anthropics/claude-code /tmp/claude-code
cp /tmp/claude-code/plugins/code-review/commands/code-review.md .claude/commands/code-review.md

# security-review (de anthropics/claude-code-security-review)
git clone --depth 1 https://github.com/anthropics/claude-code-security-review /tmp/cc-security
cp /tmp/cc-security/.claude/commands/security-review.md .claude/commands/security-review.md
cp /tmp/cc-security/LICENSE .claude/commands/LICENSE-security-review
```
