# SafetySphere – Real-Time PPE Detection Web Application

SafetySphere is a real-time PPE (Personal Protective Equipment) detection web application that enhances workplace safety. It detects if individuals are wearing PPE correctly and raises alerts when safety gear is missing.

**⚡ Note:** This repository contains the entire WordPress installation used for the frontend. The backend Python scripts and trained YOLOv8 models are not included.

# 🚀 Features

Modern **landing page** (desktop + mobile responsive)

Secure **login/signup** system (Ultimate Member plugin)

**Restricted** detection page (accessible only after login)

**Custom UI/UX built** with **Elementor Pro + custom HTML/CSS/JS**

Integrated with **backend (via WebSockets)** for **real-time PPE alerts**

# 🛠️ Tech Stack

## Frontend (this repo):

### WordPress

**Elementor Pro + Dynamic Content for Elementor**

**Ultimate Member Plugin** (user authentication)

**Custom HTML, CSS, and JavaScript**

## Backend (not included here):

**Python, YOLOv8, PyTorch, OpenCV**

**WebSockets** (for real-time communication)

# 📂 Repository Contents

`wp-admin/` → WordPress core admin files

`wp-includes/` → WordPress core includes

`wp-content/` → Custom work here:

`themes/` → Custom theme files

`plugins/` → Installed and customized plugins

`uploads/` → Images and assets used

**📝 Sensitive files like wp-config.php are excluded via .gitignore.**

# ⚙️ Setup Instructions

**Clone or download this repo.**

`git clone https://github.com/zeeshi2k1/Safety-Sphere-WordPress-Front-End.git`


Set up a WordPress environment (e.g.,LocalWP, XAMPP, MAMP, or hosting server).

Copy the files into your WordPress installation folder.

Create a new database and configure it in your own wp-config.php.

## Install required plugins:

Elementor Pro

Dynamic Content for Elementor

Ultimate Member

Import demo data or configure manually.

# Project on Behance with Demo video

 **Link:** https://www.behance.net/gallery/233356265/SafetySphere-WordPress-with-AIML

# 📷 Screenshots

## Landing Page
<img alt="DesktopLanding page" src="https://github.com/user-attachments/assets/99ede4c2-5889-4053-a6e4-13c92dbc3b73" />

## Login & Sign Up Page
**Login**
<img width="1920" height="922" alt="safe 6" src="https://github.com/user-attachments/assets/42515ac1-0a58-4d37-bd5a-b843f1f85f2e" />
**Sign Up**
<img width="1920" height="921" alt="safe 7" src="https://github.com/user-attachments/assets/45c52b5d-fbb4-4d61-b274-ce1164fb1c64" />

## PPE Detection Page
<img width="1901" height="920" alt="safe 8" src="https://github.com/user-attachments/assets/bf1269f4-87e5-46ea-a901-3058d4875e90" />

# Responsive UI
<img width="1080" height="1080" alt="Phone Mockup1" src="https://github.com/user-attachments/assets/abc12546-5227-41e2-87f3-619a9be293bc" />
<img width="1080" height="1080" alt="Phone Mockup2" src="https://github.com/user-attachments/assets/a5ce9ae0-58ca-4787-87bd-8a125def58c3" />
<img width="1080" height="1080" alt="Phone Mockup3" src="https://github.com/user-attachments/assets/0e63a69c-8698-4265-b274-d2652648cf09" />

# 🏆 Challenges & Solutions

**Plugin customization** → Solved limited styling options with custom CSS & JS.

**Backend-frontend integration**→ Used WebSockets to stream real-time detections.

**Responsive design** → Ensured smooth experience on both desktop & mobile.

# ✨ Reflection

This project improved my skills in WordPress development, plugin customization, and frontend integration. I also learned how to connect an AI-powered backend with WordPress for real-time detection systems.

# 📬 Contact

## 👤 Zeeshan Ahmed

**Portfolio:** https://www.behance.net/Zeeshii2k1

**Email:** workmail.zeeshan@gmail.com

**LinkedIn:** https://www.linkedin.com/in/zeeshan-ahmed-321399223


