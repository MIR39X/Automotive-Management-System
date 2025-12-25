# AMS - Automotive Management System

> **Course Database Management System Semester Project**  
> *Developed by Arsalan Mir*

## 🚗 Executive Summary
The **Automotive Management System (AMS)** is a comprehensive web-based platform designed to streamline the operations of an automotive business. It unifies vehicle inventory, customer relationship management (CRM), service workshop scheduling, and retail sales into a single, cohesive interface.

Built from the ground up using **Vanilla PHP** and **MySQL**, this project demonstrates a robust implementation of relational database concepts, secure authentication, and a custom MVC-lite architecture without reliance on heavy frameworks.

---

## 🌟 Core Modules

### 🚘 Inventory & Showroom
*   **Vehicle Management**: Full lifecycle tracking of vehicles (Acquisition → Available → Sold). Captures detailed specs (VIN, Model, Year) and high-resolution images.
*   **Parts Inventory**: Manage stock levels for spare parts, track unit prices, and link parts to specific suppliers.

### 🛠️ Service Center
*   **Job Cards**: The heart of the workshop. Track vehicle repairs through customizable statuses (`Open`, `In Progress`, `Completed`, `Closed`). Assign technicians and log date-in/date-out.
*   **Service Catalog**: Maintain a standardized list of service offerings (e.g., Oil Change, Tuning) with base prices for consistent billing.

### 💰 Sales & Commerce
*   **Vehicle Sales**: Streamlined checkout process that handles inventory status updates, customer assignment, and invoice generation.
*   **Retail Sales**: Point-of-Sale (POS) module for selling loose parts or over-the-counter services directly to walk-in customers.
*   **Suppliers**: Manage vendor relationships and procurement sources.

### 👥 CRM & Administration
*   **Customer Profiles**: 360-degree view of customers, including their purchase history, contact details, and lifetime value.
*   **Employee Directory**: HR module to manage staff roles, salaries, and contact information.
*   **Secure Authentication**: Role-based access control protecting all administrative functions.

---

## ⚙️ Technical Architecture

*   **Language**: PHP 8.1+ (Strict Typing, PDO for Database Abstraction).
*   **Database**: MySQL 5.7+ (Relational Schema, Foreign Keys, ACID Compliance).
*   **Frontend**: HTML5, CSS3 (Custom responsive design), Vanilla JavaScript.
*   **Architecture**:
    *   **Security First**: SQL Injection protection via Prepared Statements.
    *   **Component-Based**: Reusable header/footer templates and asset management.
    *   **Session Management**: Secure login sessions with inactivity timeouts.

---

## 🚀 Getting Started

### Prerequisites
*   A localized server environment (XAMPP, WAMP, or Laragon).
*   PHP 8.0 or higher.
*   MySQL/MariaDB.

### Installation
1.  **Clone the Repository**:
    Place the `ams_project` folder into your web server's root directory (e.g., `htdocs` or `www`).
    ```bash
    git clone https://github.com/MIR39X/Automotive-Management-System.git
    ```

2.  **Configure Database**:
    *   Create a new database named `ams_db` in phpMyAdmin or your SQL client.
    *   Import the schema logic (Tables for `vehicle`, `customer`, `purchase`, `jobcard`, `parts`, etc.) or verify `includes/db.php` connects successfully.

3.  **Setup Configuration**:
    Open `includes/header.php` and verify the base path matches your folder name:
    ```php
    $base = '/ams_project'; // Adjust if you renamed the folder
    ```

4.  **Launch**:
    Visit `http://localhost/ams_project/public/login.php`.
    *   **Default Admin Credentials**: `admin` / `admin123`

---

## 📂 Project Structure
```text
ams_project/
├── assets/             # CSS styles, Uploaded Images
├── includes/           # Database config, Header, Footer
├── public/             # Application Pages
│   ├── vehicles/       # Vehicle CRUD
│   ├── customers/      # Customer CRM
│   ├── jobcards/       # Workshop Workflow
│   ├── retailsales/    # Parts POS
│   └── ...             # Other modules (Employees, Parts, etc.)
└── README.md           # Project Documentation
```

---

*This project was submitted as a final semester project for the Database Management Systems course.*
