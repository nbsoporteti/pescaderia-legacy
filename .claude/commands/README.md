# Slash commands de Claude Code

Comandos a nivel de proyecto. Claude Code los descubre automáticamente desde
`.claude/commands/`, así que `/code-review` queda disponible en cualquier
sesión de este repositorio.

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

## Cómo actualizar

```bash
git clone --depth 1 https://github.com/anthropics/claude-code /tmp/claude-code
cp /tmp/claude-code/plugins/code-review/commands/code-review.md .claude/commands/code-review.md
```
