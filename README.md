# GestQuartel

Aplicacao GestQuartel para gestao de um quartel de bombeiros. Inclui autenticacao, dashboard, gestao de pessoas, fardamento, veiculos e atribuicao/devolucao de fardamento com atualizacao automatica de stock.

## Acesso inicial

Depois de executar as migrations e seeders, podes entrar com:

- Email: `admin@gestquartel.test`
- Password: `password`

## Funcionalidades implementadas

- Login obrigatorio: utilizadores nao autenticados sao enviados para `/login`.
- Dashboard com metricas de pessoas, fardamento, veiculos e alertas de stock baixo.
- CRUD de pessoas com estado, posto/funcao, contactos e historico de fardamento.
- CRUD de fardamento com stock total, stock disponivel e stock minimo.
- CRUD de veiculos com estado operacional, manutencao ou inativo.
- Atribuicao de fardamento a pessoas ativas.
- Devolucao de fardamento com reposicao automatica do stock.
- Protecao contra atribuicoes superiores ao stock disponivel.

## Comandos uteis

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Para validar:

```bash
php artisan test
```
