# API BERIMBAU

Esta pasta concentra a camada incremental de API do projeto.

## Estrutura

- `bootstrap.php`: inicia sessao, carrega banco, funcoes legadas e helpers.
- `helpers.php`: respostas JSON/HTML, renderizacao de partials e autenticacao basica.
- `services/`: consultas e regras reaproveitaveis por endpoint.
- `partials/`: HTML retornado para HTMX.
- `alunos.php`: lista alunos em JSON ou HTML parcial.
- `dashboard.php`: estatisticas em JSON ou HTML parcial.
- `vivencia.php`: materiais de vivencia em JSON ou HTML parcial.
- `competicoes.php`: eventos em JSON ou HTML parcial.
- `aulas.php`: cria, atualiza, lista e exclui registros de diario.
- `validacao.php`: aprova ou rejeita solicitacoes de aluno.
- `inscricoes.php`: registra inscricoes em competicoes.
- `senha.php`: reset de senha de aluno.

## Uso HTMX

Os endpoints retornam HTML quando recebem `?partial=...`, `?format=html` ou o header `HX-Request: true`.

Exemplo:

```html
<div hx-get="api/alunos.php?partial=list" hx-trigger="load" hx-swap="innerHTML"></div>
```

Sem `partial`, os endpoints retornam JSON.

## Regra de organizacao

As telas em `views/` e os arquivos de interface na raiz nao devem conter SQL direto. Consultas e gravacoes ficam em `api/services/` ou em funcoes legadas de dominio enquanto a migracao completa continua.
