# PortalCore: Encrypted JSON-Based Dynamic Web Portal

PortalCore is a dynamic and modular web portal that uses a unique data storage approach: instead of a traditional database, it stores all its data in a single, securely encrypted JSON file on the server. The system features a user-facing portal that displays applications based on permissions and a comprehensive admin panel for managing the entire system.

## Features

-   **Database-Free:** All system data (users, applications, menus) is stored in `data.json.enc`.
-   **Secure by Design:**
    -   AES-256 encryption for the data file.
    -   `bcrypt` for hashing user passwords.
    -   Session-based authentication.
-   **Dynamic User Portal:** Users see a personalized dashboard of applications they are authorized to access.
-   **Comprehensive Admin Panel:**
    -   **User Management:** Full CRUD (Create, Read, Update, Delete) for users.
    -   **Permission Management:** Assign application access to users via a simple checkbox interface.
    -   **Application Management:** Full CRUD for applications (the cards shown on the user dashboard).
    -   **Menu Management:** Full CRUD for the main navigation menu items.

## Technologies Used

-   **Backend:** Python with Flask
-   **Frontend:** Server-Side Rendered HTML with Jinja2 templates
-   **Encryption:** `cryptography` library (for AES-256)
-   **Password Hashing:** `bcrypt` library

---

## Setup and Installation

### 1. Prerequisites

-   Python 3.6+
-   `pip` for package management

### 2. Clone the Repository

```bash
git clone <repository_url>
cd PortalCore
```

### 3. Set Up a Virtual Environment (Recommended)

**On macOS/Linux:**
```bash
python3 -m venv venv
source venv/bin/activate
```

**On Windows:**
```bash
python -m venv venv
.\venv\Scripts\activate
```

### 4. Install Dependencies

Install all the required Python packages using the `requirements.txt` file.

```bash
pip install -r requirements.txt
```

### 5. Configure Environment Variables

This is the most critical step for securing your application. PortalCore requires two environment variables.

-   `PORTAL_CORE_SECRET_KEY`: A strong, unique secret key used to encrypt and decrypt the `data.json.enc` file.
-   `FLASK_SECRET_KEY`: A secret key used by Flask to sign user sessions.

**On macOS/Linux:**
You can set them in your shell for the current session:
```bash
export PORTAL_CORE_SECRET_KEY='your-very-strong-and-unique-encryption-key'
export FLASK_SECRET_KEY='your-separate-flask-session-key'
```
For a more permanent solution, add these lines to your `.bashrc`, `.zshrc`, or shell profile.

**On Windows:**
```powershell
$env:PORTAL_CORE_SECRET_KEY="your-very-strong-and-unique-encryption-key"
$env:FLASK_SECRET_KEY="your-separate-flask-session-key"
```

> **Warning:** Do not use weak keys or hardcode them in the application. The security of your data file depends entirely on the strength and secrecy of `PORTAL_CORE_SECRET_KEY`.

---

## How to Run the Application

### 1. Generate the Initial Encrypted Data File

The first time you run the application, you need to generate the `data.json.enc` file. This is done by running the `encryption_manager.py` script directly.

```bash
python modules/encryption_manager.py
```
You should see a message: `Initial data created and saved to data.json.enc`. This file contains the default 'admin' user and sample applications.

### 2. Start the Flask Server

Now, you can run the main application.

```bash
python app.py
```

The application will start in debug mode and be accessible at:
**http://127.0.0.1:5001**

### 3. Login

-   Navigate to `http://127.0.0.1:5001`. You will be redirected to the login page.
-   **Default Admin Credentials:**
    -   **Username:** `admin`
    -   **Password:** `admin`

> **Security Note:** Upon your first login as `admin`, you will see a warning message prompting you to change the default password. Please do so immediately via the **Admin Panel -> User Management**.