🚌 College Bus Tracking System

A simple and efficient web-based application to track college buses in real-time. This system allows users to view bus locations on a map and monitor movement dynamically.


🚀 Features

- 🔐 User Login & Authentication
- 🚌 View Available Buses
- 📍 Live Bus Tracking using Map
- 🗺️ Interactive Map with Marker Movement
- ⚙️ User Profile & Settings
- 🔔 Notification Preferences (UI based)


🛠️ Tech Stack

- Frontend: HTML, CSS, JavaScript
- Backend: PHP
- Database: MySQL
- Map Integration: Leaflet.js
- Icons: Font Awesome


📁 Project Structure

college-bus-tracking/
│
├── index.php
├── home.php
├── map.php
├── track.php
├── settings.php
├── search.php
├── logout.php
│
├── includes/
│   ├── db.php
│   ├── auth.php
│   ├── functions.php
│   └── navbar.php
│
├── api/
│   └── get_locations.php
│
├── assets/
│   ├── css/
│   └── images/
│
└── database.sql



⚙️ Installation

1. Clone the repository:

git clone https://github.com/sudharsanspc/college-bus-tracking.git

2. Move project to your server folder:

- For XAMPP: "htdocs/"

3. Import the database:

- Open phpMyAdmin
- Create a database
- Import "database.sql"

4. Configure database:

- Open "includes/db.php"
- Update DB name, username, password



▶️ Run the Project

- Start Apache & MySQL
- Open browser

http://localhost/college-bus-tracking



🌐 Live Demo

👉 Add your hosted link here
Example:

http://ritbus.epizy.com



⚠️ Notes

- Free hosting may have delays (DNS / SSL issues)
- Real-time tracking depends on API data
- SSL may not be available in free hosting



📌 Future Improvements

- 📱 Mobile App Integration
- 🔔 Real-time Notifications
- 📡 GPS Integration
- 👨‍💼 Admin Dashboard


👨‍💻 Author

Sudharsan

- GitHub: https://github.com/sudharsanspc

