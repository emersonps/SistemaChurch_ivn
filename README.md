# Sistema de Gestão de Igreja

Este é um sistema completo de gestão para igrejas com site público e área administrativa.

📖 **[Consulte o Manual do Usuário (MANUAL.md)](MANUAL.md) para detalhes de uso.**

## Funcionalidades

- **Site Público**: Página inicial moderna com informações da igreja.
- **Área Administrativa**:
  - **Membros**: Cadastro completo (batismo, congregação, data de nascimento).
  - **Dízimos**: Lançamento de dízimos e ofertas com geração de recibo e envio via WhatsApp.
  - **Eventos**: Agenda de eventos da igreja.
  - **Dashboard**: Visão geral de aniversariantes, eventos e finanças.

## Acesso

- **URL do Site**: http://localhost:8000
- **URL do Admin**: http://localhost:8000/admin/login

## Credenciais de Acesso (Admin)

- **Usuário**: `admin`
- **Senha**: `admin`

## Banco de Dados

O banco de dados é SQLite e está localizado em `database/SistemaChurch.db`.

## Requisitos

- PHP 7.4 ou superior
- Extensões PHP: `pdo_sqlite`, `sqlite3`

## Como Rodar

Se o servidor não estiver rodando, use o comando:

```bash
php -d extension=pdo_sqlite -d extension=sqlite3 -S localhost:8000 -t public
```
