<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Laravel Version"></a>
  <a href="https://img.shields.io/badge/PHP-8.2-blue"><img src="https://img.shields.io/badge/PHP-8.2-blue" alt="PHP Version"></a>
  <a href="https://www.mysql.com/"><img src="https://img.shields.io/badge/MySQL-8.0-orange" alt="MySQL Version"></a>
  <a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/License-MIT-green" alt="License"></a>
  <a href="http://127.0.0.1:8000/api/documentation"><img src="https://img.shields.io/badge/Swagger-API-yellow" alt="Swagger UI"></a>
</p>

# Graph API Laravel Project

API para gestión de **nodos** construida con **Laravel 12**, con documentación **Swagger** generada automáticamente usando **L5-Swagger**.

---

## 🛠 Requisitos

- PHP >= 8.2  
- Composer  
- MySQL / PostgreSQL / SQLite  
- Extensiones PHP: `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`  

---

## ⚡ Instalación

```bash
git clone https://github.com/tu-usuario/graph-api.git
cd graph-api
cp .env.example .env
composer install
php artisan key:generate
