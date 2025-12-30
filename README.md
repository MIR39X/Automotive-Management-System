<div align="center">

# 🚗 Automotive Management System

**A Full-Stack Dealership & Workshop Management Platform**

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat-square&logo=html5&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/HTML)
[![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat-square&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat-square&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)

> Course Database Management System Semester Project  
> *Developed by Arsalan Mir (23K2013 - 23K2085)*

</div>

---

## Overview

**AMS** is a comprehensive web-based platform that streamlines automotive business operations — from vehicle inventory and customer management to workshop scheduling and retail sales.

---

## Features

| Module | Description |
|:-------|:------------|
| 🚘 **Inventory** | Vehicle lifecycle tracking, parts stock management |
| 🛠️ **Service Center** | Job cards, technician assignment, service catalog |
| 💰 **Sales** | Vehicle checkout, POS for retail, supplier management |
| 👥 **CRM** | Customer profiles, employee directory, role-based access |

---

## Quick Start

**1. Clone & Setup**
```bash
git clone https://github.com/MIR39X/Automotive-Management-System.git
```

**2. Database**
- Create database `ams_db` in phpMyAdmin
- Import schema (vehicles, customers, jobcards, parts, etc.)

**3. Configure**
```php
// includes/header.php
$base = '/ams_project';
```

**4. Launch**
```
http://localhost/ams_project/public/login.php
```

**Default Login:** `admin` / `admin123`

---

## Project Structure

```
ams_project/
├── assets/          # CSS, uploads, logo
├── includes/        # db.php, header.php, footer.php
└── public/          # Application pages
    ├── vehicles/    # Vehicle CRUD
    ├── customers/   # Customer CRM
    ├── jobcards/    # Workshop workflow
    ├── parts/       # Parts inventory
    ├── services/    # Service catalog
    ├── suppliers/   # Supplier management
    └── retailsales/ # POS system
```

---

## Tech Stack

| Layer | Technology |
|:------|:-----------|
| Frontend | HTML5, CSS3, Vanilla JS |
| Backend | PHP 8.1+ with PDO |
| Database | MySQL 5.7+ |
| Security | Prepared statements, session management |

---

<div align="center">

**Made with ❤️ for DBMS Course**

</div>
