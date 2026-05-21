# Testes automatizados dos processos

Execute na raiz do projeto:

```bash
php tests/processos_test.php
```

O teste valida:

- sintaxe de todos os arquivos PHP;
- contratos principais dos arquivos `processar_*`;
- compatibilidade básica entre o SQL dump e as consultas usadas pelos processos;
- estrutura de API/HTMX;
- variáveis Docker esperadas no `.env`: app `9077` e MySQL `3377`.
