<div align="center">

![Logo Band](https://raw.githubusercontent.com/GustoVasconcelos/sgcm/refs/heads/main/public/logotipo-band.webp)

# SGCM — Sistema de Gerenciamento do Controle Mestre

**Um sistema web para gerenciamento de escalas, afinacao de jornais, timers de estúdio, ferias dos operadores, programas locais e independentes.**

[![Tests](https://github.com/GustoVasconcelos/sgcm/actions/workflows/tests.yml/badge.svg)](https://github.com/GustoVasconcelos/sgcm/actions/workflows/tests.yml)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)
![License](https://img.shields.io/badge/license-MIT-green)

</div>

---

## 📌 Sobre o Projeto

O **SGCM** é uma aplicação web desenvolvida em Laravel para auxiliar a equipe do Controle Mestre da Band Paulista no gerenciamento operacional do dia a dia. Ele centraliza o controle de escalas de trabalho, a afinacao de jornais, o monitoramento de timers de estúdio, gerenciamento de ferias dos operadores e dos programas locais e independentes que sao exibidos ao finais de semana, e o registro de logs de ações dos usuários.

---

## ✨ Funcionalidades

| Módulo | Descrição |
|---|---|
| **Dashboard** | Visão geral dos turnos do usuário logado e datas de folga/retorno |
| **Afinacao de Jornais** | Gerenciamento de afinacao de jornais |
| **Escalas** | Criação, edição e geração automática de escalas de turnos (6h/8h) |
| **Relatórios** | Geração de PDF e envio de escalas por e-mail |
| **Timer de Estúdio** | Cronômetro e regressiva em tempo real para operadores e coordenadores |
| **Programação** | Cadastro de programas e grades da emissora |
| **Férias** | Controle de períodos de férias e afastamentos dos operadores |
| **Admin** | Dashboard administrativo com métricas, gerenciamento de usuários, roles e permissões |
| **Logs** | Rastreamento completo de ações dos usuários (com filtros por módulo, usuário e data) |

---

## 🛠️ Stack Tecnológica

- **Backend:** Laravel 12, PHP 8.2
- **Frontend:** Blade Templates, CSS (Bootstrap 5.3), JavaScript (Vanilla)
- **Banco de Dados:** MySQL / MariaDB
- **Autenticação & Permissões:** Laravel Auth + Spatie Laravel Permission
- **Build:** Vite + Node.js
- **Containerização:** Docker + Docker Compose
- **CI/CD:** GitHub Actions

---

## 🚀 Como Executar Localmente

### Com Docker (Recomendado)

```bash
# 1. Clone o repositório
git clone https://github.com/GustoVasconcelos/sgcm.git
cd sgcm

# 2. Configure o ambiente
cp .env.example .env

# 3. Suba os containers
docker compose up -d

# 4. Acesse em http://localhost:8080
```

> As migrations e otimizações do Laravel rodam automaticamente na inicialização do container.

## 🔐 Criando um usuário admin

Para criar um usuário admin, rode o comando:

```bash
docker compose exec app php artisan db:seed --class=RolesAndPermissionsSeeder
```

---

## 🧪 Testes

O projeto possui uma suíte de testes automatizados (Unit + Feature), executados também no CI/CD via GitHub Actions.

```bash
php artisan test
```

---

## 🔑 Permissões e Acessos

O sistema utiliza roles para controle de acesso:

| Role | Acesso |
|---|---|
| `admin` | Painel administrativo, usuários, roles, logs e configurações |
| `operador` | Dashboard, afinacao, escalas, timer de estúdio, ferias, programas e perfil |

---

## 🐳 Infraestrutura Docker

| Serviço | Descrição |
|---|---|
| `app` | PHP 8.2 + Nginx (via `serversideup/php`) |
| `db` | MariaDB 10.6 |

---

## 📄 Licença

Este projeto está sob a licença [MIT](LICENSE).
