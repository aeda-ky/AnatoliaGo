# 🗺️ AnatoliaGo

> **Discover Türkiye. Build your route. Share your journey.**

AnatoliaGo is a collaborative web application designed to help users
discover destinations across Türkiye, create personalized travel routes,
and explore routes shared by other travelers.

The project combines a PHP-based backend with a MySQL database and a
web interface for destination discovery, route planning, and
community interaction.

---

## ✨ Features

### 🔐 User Authentication
Users can create accounts, log in, manage their sessions, and access
personalized features.

### 🏙️ Discover Cities & Places
Users can select cities and explore popular attractions and places
to visit.

### 🧭 Explore
The Explore section allows users to browse destinations and discover
places based on selected cities.

### 🗺️ Personalized Routes
Authenticated users can create and save their own travel routes by
combining places they want to visit.

### 👥 Community Routes
Users can share their routes with the community and explore routes
created by other travelers.

Community routes can be filtered by city, making it easier to discover
travel plans for a specific destination.

### ⭐ Route Rating
Community-created routes can be rated, allowing users to evaluate and
discover useful travel plans.

### ⚙️ Administration
Administrative functionality provides management operations for
users, cities, locations, routes, and ratings.

### 🗺️ Interactive Map & Route Planning

Users can explore destinations directly on an interactive map and view
where selected places are located geographically.

The map supports:

- City-based destination discovery
- Category filtering
- Location markers
- Place search
- Visual exploration of destinations
- Selecting places for personalized routes
- Creating routes from selected destinations
---

## 🛠️ Tech Stack

### Backend
- PHP
- REST-style API endpoints
- PDO

### Database
- MySQL
- SQL

### Frontend
- HTML
- CSS
- JavaScript

### Maps & Location
- Leaflet
- OpenStreetMap
- Interactive location markers
- Map-based destination discovery
- Route planning

---

## 🏗️ Project Structure

    AnatoliaGo/
    │
    ├── api/
    │   ├── admin/
    │   ├── auth/
    │   ├── cities/
    │   ├── locations/
    │   ├── ratings/
    │   ├── routes/
    │   └── users/
    │
    ├── database/
    │   ├── schema.sql
    │   └── seed.sql
    │
    ├── includes/
    │   ├── helpers/
    │   └── db_connect.php
    │
    ├── public/
    │   ├── explore.php
    │   ├── map.php
    │   ├── profile.php
    │   ├── routes.php
    │   └── route-detail.php
    │
    └── README.md

---

## 👩‍💻 My Contribution — Backend Development

AnatoliaGo was developed collaboratively as a **team project**.

My primary focus was **backend development and application logic**.
My contributions included work on:

- PHP backend and API functionality
- User authentication and session handling
- Database integration using PHP PDO and MySQL
- CRUD operations for application data
- Route creation and management functionality
- Route rating functionality
- User-related backend operations
- Admin-side management functionality
- Integration between backend functionality and application pages

Working on AnatoliaGo gave me practical experience in connecting
application logic with a relational database and implementing backend
functionality for a multi-feature web application.

> **Note:** AnatoliaGo is a collaborative project. This repository contains
> the team's application, while the section above describes my primary
> areas of contribution.

---

## 🗄️ Database

The application uses a relational MySQL database to manage data related
to users, cities, locations, routes, ratings, and other application
features.

Database initialization files are available in the
[`database`](database/) directory.

---

## 🚀 Local Setup

### Requirements

- PHP
- MySQL
- Apache / XAMPP or a similar local server environment

### Database Configuration

The default development configuration expects a local MySQL database:

    Host: 127.0.0.1
    Database: turkey_routes
    User: root

Import the database schema from:

    database/schema.sql

Then configure the database connection in:

    includes/db_connect.php

and run the application through your local PHP/Apache environment.

---

## 🎯 What I Learned

Through this project, I gained hands-on experience with:

- Backend web development with PHP
- Relational database integration
- SQL-based data management
- Authentication and session management
- API endpoint development
- CRUD operations
- Connecting frontend interactions with backend services
- Collaborative software development
