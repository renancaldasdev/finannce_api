# Finannce API

API REST de finanças pessoais construída com **Laravel 13** e **Sanctum**. Permite que cada usuário gerencie suas próprias contas, categorias e transações, com saldo das contas atualizado automaticamente a cada movimentação.

## Sumário

- [Funcionalidades](#funcionalidades)
- [Stack](#stack)
- [Arquitetura](#arquitetura)
- [Regras de negócio](#regras-de-negócio)
- [Modelo de dados](#modelo-de-dados)
- [Instalação](#instalação)
- [Endpoints](#endpoints)
- [Formato das respostas](#formato-das-respostas)
- [Logs](#logs)
- [Testes e qualidade de código](#testes-e-qualidade-de-código)

## Funcionalidades

- Cadastro, login, logout e atualização de perfil com tokens Sanctum
- CRUD de **contas** (corrente, poupança e investimento)
- CRUD de **categorias** de receita e despesa, com ativação/desativação
- CRUD de **transações** com filtros por período, tipo, conta e categoria, e paginação
- **Transferências entre contas** de forma atômica
- Atualização automática do saldo via Observers
- Isolamento total de dados por usuário
- Logs dedicados de autenticação
- Mensagens de validação e erro em português (pt-BR)

## Stack

| Camada | Tecnologia |
| --- | --- |
| Linguagem | PHP ^8.3 |
| Framework | Laravel ^13.8 |
| Autenticação | Laravel Sanctum ^4.0 |
| Banco de dados | MySQL 8.4 |
| Cache / Redis | Redis (via Sail) |
| Ambiente Docker | Laravel Sail |
| Testes | Pest ^4.7 |
| Estilo de código | Laravel Pint |
| Localização | laravel-lang/lang e laravel-pt-br-localization |

## Arquitetura

O projeto segue o princípio de responsabilidade única, com **uma classe por ação**:

```
app/
├── Http/
│   ├── Controllers/     # Invokable controllers (um por ação)
│   │   ├── Account/
│   │   ├── Auth/
│   │   ├── Category/
│   │   └── Transaction/
│   ├── Requests/        # Form Requests com validação e mensagens
│   └── Resources/       # API Resources (formato das respostas)
├── Models/              # User, Account, Category, Transaction
├── Observers/           # TransactionObserver, UserObserver
└── Services/            # Regras de negócio (uma classe por caso de uso)
    ├── Account/
    ├── Auth/
    ├── Category/
    ├── Log/
    └── Transaction/
```

Fluxo de uma requisição:

```
Route → Controller (invokable) → FormRequest (validação) → Service (regra de negócio) → Model → Resource (resposta)
```

## Regras de negócio

**Valores monetários**
- Os valores (`balance` e `amount`) são armazenados como **inteiros em centavos** (`bigInteger`) para evitar erros de ponto flutuante.
- Os Models convertem automaticamente: a API recebe e devolve valores decimais (ex.: `150.75`), enquanto o banco guarda `15075`.

**Saldo das contas** (`TransactionObserver`)
- Ao criar uma transação com `is_paid = true`, o saldo da conta é incrementado (`income`) ou decrementado (`expense`).
- Ao atualizar `amount`, `type`, `is_paid` ou `account_id`, o efeito anterior é revertido e o novo efeito é aplicado.
- Ao excluir uma transação paga, o efeito sobre o saldo é revertido.
- Transações com `is_paid = false` não alteram o saldo.

**Transferências**
- Criam duas transações dentro de uma `DB::transaction`: uma despesa na conta de origem e uma receita na conta de destino, ambas já pagas.
- As contas de origem e destino devem ser diferentes, e o valor mínimo é `1`.

**Categorias**
- Ao registrar um usuário, o `UserObserver` cria automaticamente as categorias **Transferência Enviada** (`expense`) e **Transferência Recebida** (`income`).
- Existe também o `CategorySeeder`, que cria essas categorias para usuários já existentes.
- A categoria de uma transação deve ser do mesmo `type` da transação.
- Categorias podem ser desativadas sem serem excluídas (`PATCH /categories/{id}/toogle-status`).

**Segurança e isolamento**
- Todas as rotas, exceto `/ping`, `/login` e `/register`, exigem `auth:sanctum`.
- Contas e categorias referenciadas em transações precisam pertencer ao usuário autenticado.
- Tentativas de acessar recursos de outro usuário retornam `403`.
- Contas, categorias e transações usam **soft delete**.
- Senhas exigem no mínimo 8 caracteres com letras maiúsculas e minúsculas, números e símbolos.

## Modelo de dados

```
users ──┬─< accounts ──────┐
        ├─< categories ─┐  │
        └─< transactions >─┴──┘
```

| Tabela | Campos principais |
| --- | --- |
| `users` | `name`, `email`, `password` |
| `accounts` | `user_id`, `name`, `type` (`checking`, `saving`, `investment`), `balance` — único por `(user_id, name, type)` |
| `categories` | `user_id`, `name`, `type` (`income`, `expense`), `description`, `active` — único por `(user_id, name, type)` |
| `transactions` | `user_id`, `account_id`, `category_id`, `amount`, `type`, `date`, `description`, `is_paid` |
| `personal_access_tokens` | Tokens do Sanctum |

## Instalação

### Pré-requisitos

- PHP 8.3+, Composer, Node.js e MySQL 8
- Ou apenas Docker, usando o Laravel Sail

### Com Sail (Docker)

```bash
git clone https://github.com/renancaldasdev/finannce_api.git
cd finannce_api

cp .env.example .env
composer install

./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed --class=CategorySeeder   # opcional, para usuários já existentes
```

> Se `composer install` não estiver disponível localmente, instale as dependências com o container temporário do Sail descrito na [documentação do Laravel](https://laravel.com/docs/sail#installing-composer-dependencies-for-existing-projects).

### Sem Docker

```bash
git clone https://github.com/renancaldasdev/finannce_api.git
cd finannce_api

composer run setup
```

Antes de rodar as migrations, ajuste as credenciais do banco no `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finance_api
DB_USERNAME=root
DB_PASSWORD=
```

Para iniciar o servidor de desenvolvimento (servidor, fila, logs e Vite):

```bash
composer run dev
```

### Idioma

As traduções em pt-BR já estão incluídas no diretório `lang/`. Para usá-las, defina no `.env`:

```env
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=en
```

## Endpoints

Todas as rotas ficam sob o prefixo `/api`. As rotas protegidas exigem o header:

```
Authorization: Bearer {token}
Accept: application/json
```

### Públicas

| Método | Rota | Descrição |
| --- | --- | --- |
| GET | `/api/ping` | Health check da API |
| POST | `/api/register` | Cria um usuário e retorna o token |
| POST | `/api/login` | Autentica e retorna o token |

### Autenticação

| Método | Rota | Descrição |
| --- | --- | --- |
| PUT | `/api/update` | Atualiza nome, e-mail e (opcionalmente) senha |
| POST | `/api/logout` | Encerra a sessão |

### Contas

| Método | Rota | Descrição |
| --- | --- | --- |
| GET | `/api/accounts` | Lista as contas |
| GET | `/api/accounts/{account}` | Detalha uma conta |
| POST | `/api/accounts` | Cria uma conta |
| PUT | `/api/accounts/{account}` | Atualiza uma conta |
| DELETE | `/api/accounts/{account}` | Exclui uma conta (soft delete) |

### Categorias

| Método | Rota | Descrição |
| --- | --- | --- |
| GET | `/api/categories` | Lista as categorias |
| GET | `/api/categories/{category}` | Detalha uma categoria |
| POST | `/api/categories` | Cria uma categoria |
| PUT | `/api/categories/{category}` | Atualiza uma categoria |
| DELETE | `/api/categories/{category}` | Exclui uma categoria (soft delete) |
| PATCH | `/api/categories/{category}/toogle-status` | Ativa ou desativa a categoria |

### Transações

| Método | Rota | Descrição |
| --- | --- | --- |
| GET | `/api/transactions` | Lista paginada, com filtros |
| GET | `/api/transactions/{transaction}` | Detalha uma transação |
| POST | `/api/transactions` | Cria uma transação |
| PUT | `/api/transactions/{transaction}` | Atualiza uma transação |
| DELETE | `/api/transactions/{transaction}` | Exclui uma transação (soft delete) |
| POST | `/api/transactions/transfer` | Transfere valor entre duas contas |

**Filtros de `GET /api/transactions`** (todos opcionais)

| Parâmetro | Tipo | Descrição |
| --- | --- | --- |
| `start_date` | date | Data inicial (inclusive) |
| `end_date` | date | Data final (inclusive), deve ser maior ou igual a `start_date` |
| `type` | string | `income` ou `expense` |
| `account_id` | integer | Filtra por conta |
| `category_id` | integer | Filtra por categoria |
| `per_page` | integer | Itens por página, de 1 a 100 (padrão: 20) |

### Exemplos

**Registro**

```bash
curl -X POST http://localhost/api/register \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Maria Silva",
    "email": "maria@example.com",
    "password": "Senha@123"
  }'
```

**Criar conta**

```bash
curl -X POST http://localhost/api/accounts \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Nubank",
    "type": "checking",
    "balance": 1500.00
  }'
```

**Criar transação**

```bash
curl -X POST http://localhost/api/transactions \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "account_id": 1,
    "category_id": 3,
    "amount": 89.90,
    "type": "expense",
    "date": "2026-08-10",
    "description": "Supermercado",
    "is_paid": true
  }'
```

**Transferência entre contas**

```bash
curl -X POST http://localhost/api/transactions/transfer \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "origin_account_id": 1,
    "destination_account_id": 2,
    "origin_category_id": 1,
    "destination_category_id": 2,
    "amount": 500,
    "date": "2026-08-10",
    "description": "Reserva de emergência"
  }'
```

## Formato das respostas

**Sucesso**

```json
{
  "success": true,
  "message": "Transação cadastrada com sucesso!",
  "data": {
    "id": 1,
    "amount": 89.9,
    "type": "expense",
    "date": "2026-08-10",
    "description": "Supermercado",
    "is_paid": true,
    "account_id": 1,
    "category_id": 3
  }
}
```

**Erros**

| Status | Situação | Corpo |
| --- | --- | --- |
| 401 | Token ausente, inválido ou expirado | `{ "status": "fail", "message": "..." }` |
| 403 | Recurso pertence a outro usuário | `{ "status": "fail", "message": "..." }` |
| 404 | Recurso não encontrado | `{ "status": "fail", "message": "..." }` |
| 422 | Falha de validação | `{ "status": "fail", "errors": { "campo": ["..."] } }` |

## Logs

- Eventos de autenticação (login, registro e atualização de perfil) são gravados em `storage/logs/auth.log`, com `user_id`, e-mail, IP e user agent.
- Acessos não autorizados e falhas de validação são registrados nos logs da aplicação. Os campos `password` e `password_confirmation` nunca são gravados.

## Testes e qualidade de código

```bash
# Testes (Pest)
composer test

# Estilo de código (Pint)
./vendor/bin/pint
```

## Licença

Projeto sob a licença [MIT](https://opensource.org/licenses/MIT).
