<div align="center">

<<<<<<< HEAD
<!-- Banner -->
<img src="assets/banner.png" alt="AMS Banner" width="100%">
=======
> **Course Database Management System Semester Project**  
> *Developed by Arsalan Mir*
>>>>>>> 0c166b3058fa94cd3fc6fe344f8d5152fc593128

<!-- Logo & Title -->
<br>
<img src="assets/logo.png" alt="AMS Logo" width="150">

# 🚗 Automotive Management System

### *A Modern, Full-Stack Dealership & Workshop Platform*

[![PHP Version](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)
[![PRs Welcome](https://img.shields.io/badge/PRs-Welcome-brightgreen?style=for-the-badge)](https://github.com/MIR39X/Automotive-Management-System/pulls)

<br>

[📖 Documentation](#-getting-started) • [🎯 Features](#-core-modules) • [🚀 Quick Start](#-installation) • [📂 Structure](#-project-structure)

---

</div>

## � Overview

> **Automotive Management System (AMS)** is a comprehensive web-based platform that streamlines automotive business operations. From vehicle inventory and customer management to workshop scheduling and retail sales — all unified in one sleek interface.

<br>

<div align="center">
  <table>
    <tr>
      <td align="center"><img src="https://img.icons8.com/fluency/48/car.png" width="40"/><br><b>Vehicle<br>Inventory</b></td>
      <td align="center"><img src="https://img.icons8.com/fluency/48/customer-support.png" width="40"/><br><b>Customer<br>CRM</b></td>
      <td align="center"><img src="https://img.icons8.com/fluency/48/maintenance.png" width="40"/><br><b>Service<br>Workshop</b></td>
      <td align="center"><img src="https://img.icons8.com/fluency/48/shopping-cart.png" width="40"/><br><b>Retail<br>Sales</b></td>
      <td align="center"><img src="https://img.icons8.com/fluency/48/business-report.png" width="40"/><br><b>Analytics<br>Dashboard</b></td>
    </tr>
  </table>
</div>

---

## ✨ Core Modules

<table>
<tr>
<td width="50%">

### 🚘 Inventory & Showroom

- **Vehicle Lifecycle Tracking**  
  `Acquisition → Available → Sold`
- **Detailed Specifications**  
  VIN, Brand, Model, Year, High-res Images
- **Parts Inventory**  
  Stock levels, unit pricing, supplier links

</td>
<td width="50%">

### 🛠️ Service Center

- **Digital Job Cards**  
  `Open → In Progress → Completed → Closed`
- **Technician Assignment**  
  Track date-in/date-out workflows
- **Service Catalog**  
  Standardized services with base pricing

</td>
</tr>
<tr>
<td width="50%">

### 💰 Sales & Commerce

- **Vehicle Sales**  
  Streamlined checkout with auto-invoicing
- **POS System**  
  Over-the-counter parts & services
- **Supplier Management**  
  Vendor relationships & procurement

</td>
<td width="50%">

### 👥 CRM & Administration

- **360° Customer Profiles**  
  Purchase history, contact info, lifetime value
- **Employee Directory**  
  Roles, salaries, HR management
- **Role-Based Access Control**  
  Secure authentication system

</td>
</tr>
</table>

---

## 🏗️ Technical Architecture

<div align="center">

```
┌─────────────────────────────────────────────────────────────────┐
│                     🖥️  PRESENTATION LAYER                      │
│              HTML5 • CSS3 (Responsive) • Vanilla JS             │
├─────────────────────────────────────────────────────────────────┤
│                    ⚙️  APPLICATION LAYER                         │
│       PHP 8.1+ (Strict Typing) • PDO Database Abstraction       │
├─────────────────────────────────────────────────────────────────┤
│                      🗄️  DATA LAYER                              │
│          MySQL 5.7+ • Foreign Keys • ACID Compliance            │
└─────────────────────────────────────────────────────────────────┘
```

</div>

<br>

| Layer | Technology | Features |
|:-----:|:-----------|:---------|
| 🎨 **Frontend** | HTML5, CSS3, JavaScript | Custom responsive design, Modern UI |
| 🔧 **Backend** | PHP 8.1+ | Strict typing, Prepared statements |
| 🗃️ **Database** | MySQL 5.7+ | Relational schema, ACID compliance |
| 🔐 **Security** | Session Management | SQL injection protection, Role-based access |

---

## 🚀 Getting Started

### 📋 Prerequisites

Before you begin, ensure you have the following installed:

| Requirement | Version | Download |
|:------------|:--------|:---------|
| 🌐 Web Server | XAMPP, WAMP, or Laragon | [XAMPP](https://www.apachefriends.org/) |
| 🐘 PHP | 8.0 or higher | Included with XAMPP |
| 🐬 MySQL | 5.7 or higher | Included with XAMPP |

---

### ⚡ Installation

<details>
<summary><b>📥 Step 1: Clone the Repository</b></summary>
<br>

Place the project folder into your web server's root directory (e.g., `htdocs` or `www`):

```bash
# Clone via HTTPS
git clone https://github.com/MIR39X/Automotive-Management-System.git

# Navigate to project
cd Automotive-Management-System
```

</details>

<details>
<summary><b>🗄️ Step 2: Configure Database</b></summary>
<br>

1. Start **Apache** and **MySQL** in XAMPP Control Panel
2. Open [phpMyAdmin](http://localhost/phpmyadmin)
3. Create a new database:
   ```sql
   CREATE DATABASE ams_db;
   ```
4. Import the schema (tables: `vehicle`, `customer`, `purchase`, `jobcard`, `parts`, etc.)

</details>

<details>
<summary><b>⚙️ Step 3: Setup Configuration</b></summary>
<br>

Edit `includes/header.php` and verify the base path:

```php
$base = '/ams_project';  // Adjust if you renamed the folder
```

</details>

<details>
<summary><b>🚀 Step 4: Launch Application</b></summary>
<br>

Open your browser and navigate to:

```
http://localhost/ams_project/public/login.php
```

**Default Credentials:**
| Username | Password |
|:---------|:---------|
| `admin` | `admin123` |

</details>

---

## 📂 Project Structure

```
ams_project/
│
├── 📁 assets/                  # Static resources
│   ├── 📁 css/                 # Stylesheets
│   │   ├── style.css           # Global styles
│   │   └── index.css           # Homepage styles
│   ├── 📁 uploads/             # User-uploaded images
│   ├── banner.png              # README banner
│   └── logo.png                # Application logo
│
├── 📁 includes/                # Shared PHP components
│   ├── db.php                  # Database configuration
│   ├── header.php              # Header template
│   └── footer.php              # Footer template
│
├── 📁 public/                  # Application pages
│   ├── 📁 vehicles/            # 🚗 Vehicle CRUD operations
│   ├── 📁 customers/           # 👥 Customer CRM module
│   ├── 📁 employees/           # 👨‍💼 Employee management
│   ├── 📁 jobcards/            # 🔧 Workshop job cards
│   ├── 📁 parts/               # ⚙️ Parts inventory
│   ├── 📁 services/            # 🛠️ Service catalog
│   ├── 📁 suppliers/           # 📦 Supplier management
│   ├── 📁 retailsales/         # 💳 POS system
│   ├── index.php               # Main dashboard
│   ├── login.php               # Authentication
│   └── logout.php              # Session termination
│
└── README.md                   # 📖 Documentation
```

---

## 🎨 Screenshots

<div align="center">

| Dashboard | Vehicle Inventory |
|:---------:|:-----------------:|
| *Live stats and inventory overview* | *Full vehicle lifecycle management* |

| Service Center | Customer CRM |
|:--------------:|:------------:|
| *Job card tracking system* | *360° customer profiles* |

</div>

> 📸 *Screenshots coming soon*

---

## 🤝 Contributing

Contributions are what make the open source community amazing! Any contributions you make are **greatly appreciated**.

1. **Fork** the repository
2. **Create** your feature branch (`git checkout -b feature/AmazingFeature`)
3. **Commit** your changes (`git commit -m 'Add AmazingFeature'`)
4. **Push** to the branch (`git push origin feature/AmazingFeature`)
5. **Open** a Pull Request

---

## 👨‍💻 Authors

<div align="center">

| Developer | Roll Number |
|:---------:|:-----------:|
| **Team Lead** | 23K2013 |
| **Developer** | 23K2085 |

</div>

---

## 📄 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- 📚 Semester project for **Database Management Systems** course
- 🏫 Developed with passion and dedication
- 🎓 Special thanks to our instructors and mentors

---

<div align="center">

### ⭐ Star this repo if you find it helpful!

<br>

[![GitHub stars](https://img.shields.io/github/stars/MIR39X/Automotive-Management-System?style=social)](https://github.com/MIR39X/Automotive-Management-System/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/MIR39X/Automotive-Management-System?style=social)](https://github.com/MIR39X/Automotive-Management-System/network/members)
[![GitHub watchers](https://img.shields.io/github/watchers/MIR39X/Automotive-Management-System?style=social)](https://github.com/MIR39X/Automotive-Management-System/watchers)

<br>

**Made with ❤️ for the automotive industry**

</div>
