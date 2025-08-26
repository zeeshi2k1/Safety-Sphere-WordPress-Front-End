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

📝 Sensitive files like wp-config.php are excluded via .gitignore.

⚙️ Setup Instructions

Clone or download this repo.

git clone https://github.com/your-username/safetysphere.git


Set up a WordPress environment (e.g., XAMPP, MAMP, or hosting server).

Copy the files into your WordPress installation folder.

Create a new database and configure it in your own wp-config.php.

Install required plugins:

Elementor Pro

Dynamic Content for Elementor

Ultimate Member

Import demo data or configure manually.

📷 Screenshots

(Add landing page, login/signup, and detection page screenshots here)

📽️ Demo Video

Watch Full Demo

🏆 Challenges & Solutions

Plugin customization → Solved limited styling options with custom CSS & JS.

Backend-frontend integration → Used WebSockets to stream real-time detections.

Responsive design → Ensured smooth experience on both desktop & mobile.

✨ Reflection

This project improved my skills in WordPress development, plugin customization, and frontend integration. I also learned how to connect an AI-powered backend with WordPress for real-time detection systems.

📬 Contact

👤 Zeeshan Ahmed

Portfolio: Behance

Email: your-email@example.com

LinkedIn: your-linkedin
