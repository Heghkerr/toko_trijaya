<p align="center">
  <img src="https://cdn-icons-png.flaticon.com/512/3081/3081559.png" width="120" alt="POS Logo">
</p>

<p align="center">
<b>🛍️ Toko Trijaya - Inventory & POS System</b><br>
Modern web-based inventory and point of sale system with PWA & WhatsApp integration
</p>

---

## About Toko Trijaya

Toko Trijaya is a comprehensive inventory and point-of-sale (POS) system designed to help small to medium businesses manage their operations efficiently.

This system provides real-time stock management, transaction handling, financial tracking, and automation features — all accessible through a modern web interface with Progressive Web App (PWA) support.

---

## Features

- 🛒 **Point of Sale (POS)**  
  Fast and intuitive transaction system with real-time calculation.

- 📦 **Inventory Management**  
  - Multi-unit product support  
  - Product variations (type, color, unit)  
  - Automatic stock updates  

- 🔄 **Purchase & Return Management**  
  - Record purchases  
  - Handle purchase returns  
  - Update stock automatically  

- 💸 **Refund System**  
  Manage customer refunds with proper tracking.

- 📊 **Reporting & Analytics**  
  - Daily & monthly reports  
  - X Report & Z Report  
  - Sales and profit insights  

- 💰 **Cashflow Tracking**  
  Monitor income and expenses in real-time.

- 📱 **Progressive Web App (PWA)**  
  - Installable on mobile devices  
  - Works offline  
  - Fast and responsive  

- 🤖 **WhatsApp Integration**  
  - Automated notifications  
  - Chatbot integration using Fonnte API  

---

## Why This Project Exists

Many small businesses still rely on manual systems or basic tools, which can lead to:

- Stock inconsistencies  
- Human errors in transactions  
- Lack of financial visibility  
- Inefficient reporting  

Toko Trijaya solves these problems by providing a fully integrated and automated system.

---

## Tech Stack

- **Backend:** Laravel 10  
- **Frontend:** Blade / JavaScript  
- **Database:** MySQL  
- **PWA:** Service Worker  
- **Integration:** WhatsApp API (Fonnte)  

---

## Installation

```bash
git clone https://github.com/Heghkerr/toko_trijaya.git
cd toko_trijaya

composer install
cp .env.example .env
php artisan key:generate

php artisan migrate --seed
php artisan serve
