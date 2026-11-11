# Online Application &amp; Registration Web Application

A full-stack, responsive web application for online student and candidate registrations built with **HTML5**, **CSS3**, **JavaScript**, **jQuery**, and **PHP**.

Upon submission, the application processes, sanitizes, and verifies the applicant data on the backend, saves it securely, and dynamically displays a formatted, printable official admission/registration sheet in the browser with print-to-PDF support.

---

## 🌟 Key Features

1. **Frontend (HTML5, CSS3, JavaScript, jQuery)**:
   - **Modern Aesthetic**: Glassmorphism cards, responsive flex/grid layouts, polished gradients, and Font Awesome iconography.
   - **Real-Time Client-side Validation**: Instant field validation on blur and input for names, email addresses, international phone numbers, birth dates, and required options.
   - **Live Photograph Preview**: Instant client-side photo thumbnail preview using HTML5 `FileReader` before uploading.
   - **Dynamic Character Counter**: Live count updates on the Statement of Purpose field (up to 500 characters).
   - **Asynchronous AJAX Submission**: Form submission handled seamlessly via `$.ajax()` without page reloads, featuring animated spinner states.
   - **Official Printable Document Card**: On successful submission, presents an official registration certificate/card with applicant photo, reference ID, timestamps, digital verification hash, and signature block.
   - **Native Print / PDF Styling**: Dedicated `@media print` CSS rules that remove web headers and buttons to produce a clean printable paper document.

2. **Backend (PHP 8.x)**:
   - **Security**: CSRF token generation and validation to safeguard submissions from cross-site request forgery.
   - **Server-Side Sanitization & Validation**: Comprehensive sanitation (`htmlspecialchars`, `filter_var`, regex checks).
   - **Secure File Upload**: Validates image MIME types (`image/jpeg`, `image/png`, `image/webp`), size limits, generates randomized safe filenames, and stores them in `uploads/` with script execution blocked via `.htaccess`.
   - **Data Persistence**: Generates unique application IDs (e.g. `REG-2026-XXXXXXXX`) and persists candidate records in `data/applications.json` using atomic file locks.
   - **Dual Handling**: Returns formatted JSON with pre-rendered HTML for AJAX clients, with a graceful server-rendered fallback if JavaScript is disabled.
   - **Candidate Directory (`records.php`)**: Built-in administrative dashboard with live search to view all submissions and open any registration document.

---

## 📂 Project Structure

```text
online-registration-app/
├── index.php             # Main registration form page
├── process.php           # Backend processing, validation, file upload & formatting
├── display.php           # Standalone printable view for any registration by ID
├── records.php           # Directory of all registered candidates with search
├── css/
│   └── style.css         # Modern responsive styles & print-specific rules
├── js/
│   └── app.js            # jQuery logic (validation, image preview, AJAX, print)
├── uploads/              # Storage directory for applicant photos (.htaccess secured)
├── data/                 # JSON flat-file storage for candidate records
├── Dockerfile            # Container definition for cloud deployments
├── render.yaml           # 1-click cloud configuration for Render.com
├── fly.toml              # Cloud configuration for Fly.io
├── vercel.json           # Serverless configuration for Vercel
├── start-server.bat      # 1-click Windows local server starter
└── README.md             # Complete documentation
```

---

## 💻 Local Setup & Execution

### Method 1: Instant 1-Click Launcher (Windows)
Double-click `start-server.bat` in the project folder. It will launch the PHP development server automatically and open on:
```
http://localhost:8000
```

### Method 2: Command Line (PHP CLI)
Run the following command from the project root:
```bash
php -S localhost:8000
```
Then visit `http://localhost:8000` in your web browser.

### Method 3: Using XAMPP / WampServer / Apache
1. Copy or move the `online-registration-app` folder into your web root directory (e.g. `C:\xampp\htdocs\online-registration-app`).
2. Start Apache from the XAMPP Control Panel.
3. Access: `http://localhost/online-registration-app/`

---

## ☁️ How to Host on a Cloud Platform

You can deploy this application for free on any of the following cloud platforms:

---

### Option 1: Render.com (Recommended - Free Web Service)
Render natively supports containerized web services with automatic SSL:

1. **Push code to GitHub**:
   ```bash
   git init
   git add .
   git commit -m "Initial commit of registration web app"
   git remote add origin https://github.com/<your-username>/<your-repo-name>.git
   git branch -M main
   git push -u origin main
   ```
2. **Deploy on Render**:
   - Go to [render.com](https://render.com) and log in.
   - Click **New +** > **Web Service**.
   - Connect your GitHub repository.
   - Render will detect the `Dockerfile` and `render.yaml` automatically.
   - Under **Instance Type**, select **Free**.
   - Click **Deploy Web Service**.
   - Your application will be live in 1–2 minutes with a free HTTPS domain (e.g., `https://online-registration-app.onrender.com`).

---

### Option 2: Railway.app
1. Create a free account at [railway.app](https://railway.app).
2. Click **New Project** > **Deploy from GitHub repo**.
3. Select this repository.
4. Railway will automatically build the `Dockerfile` and expose the web port.
5. In your project settings, click **Generate Domain** to get your public URL.

---

### Option 3: InfinityFree / 000webhost (Traditional Free PHP Web Hosting)
InfinityFree provides 100% free Apache + PHP hosting without requiring Docker or Git:

1. Sign up at [infinityfree.com](https://www.infinityfree.com).
2. Create a free hosting account and open the **Online File Manager** or connect via FTP (FileZilla).
3. Navigate into the `htdocs` directory.
4. Upload all files from this project (`index.php`, `process.php`, `display.php`, `records.php`, `css/`, `js/`, etc.).
5. Make sure `uploads/` and `data/` directories have write permissions (chmod 755 or 777).
6. Your application is live immediately on your chosen free domain!

---

### Option 4: Fly.io
1. Install the Fly CLI:
   - On Windows (PowerShell): `iwr https://fly.io/install.ps1 -useb | iex`
2. Authenticate: `fly auth login`
3. Launch and deploy:
   ```bash
   fly launch --now
   ```
   Fly.io will use the included `fly.toml` and `Dockerfile` to deploy globally.

---

### Option 5: Vercel (Serverless PHP)
1. Install Vercel CLI: `npm i -g vercel`
2. Run in the project directory:
   ```bash
   vercel --prod
   ```
   Vercel will use `vercel.json` with the `vercel-php` runtime to serve your PHP app serverlessly.

---

## 📄 License
MIT License. Open for academic, personal, and commercial projects.
