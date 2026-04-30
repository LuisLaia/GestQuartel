# GestQuartel

Aplicação Laravel para gestão de um quartel de bombeiros. Nesta fase inclui autenticação, dashboard, gestão de pessoas, inventário de equipamentos e atribuição/devolução de equipamentos com atualização automática de stock.

## Acesso inicial

Depois de executar as migrations e seeders, podes entrar com:

- Email: `admin@gestquartel.test`
- Password: `password`

## Funcionalidades implementadas

- Login obrigatório: utilizadores não autenticados são enviados para `/login`.
- Dashboard com totais de pessoas, equipamentos, atribuições ativas e alertas de stock baixo.
- CRUD de pessoas com estado, posto/função, contactos e histórico de equipamento.
- CRUD de equipamentos com stock total, stock disponível e stock mínimo.
- Atribuição de equipamentos a pessoas ativas.
- Devolução de equipamentos com reposição automática do stock.
- Proteção contra atribuições superiores ao stock disponível.
- Proteção contra remoção de pessoas/equipamentos com atribuições ainda ativas.

## Comandos úteis

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
